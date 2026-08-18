<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAccessCode;
use App\Models\User;
use App\Notifications\ParentCodeWelcome;
use App\Support\PortalPassword;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentCodeService
{
    /**
     * Code length (alphanumeric uppercase + digits).
     */
    public const CODE_LENGTH = 10;

    /**
     * Default max links per student.
     */
    public const DEFAULT_MAX_LINKS = 5;

    /**
     * Normalize code for comparison: trim, uppercase.
     */
    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Validate code and return student + access code if valid, else null.
     * Does not check link count; caller should use $accessCode->isValid() for that.
     *
     * @return array{student: Student, access_code: StudentAccessCode}|null
     */
    public static function validateCode(string $code): ?array
    {
        $normalized = self::normalizeCode($code);
        if ($normalized === '') {
            return null;
        }

        $codes = StudentAccessCode::active()
            ->with(['student.grade'])
            ->get();

        foreach ($codes as $accessCode) {
            if (Hash::check($normalized, $accessCode->code_hash)) {
                return [
                    'student' => $accessCode->student,
                    'access_code' => $accessCode,
                ];
            }
        }

        return null;
    }

    /**
     * Generate a new random code (plaintext).
     */
    public static function generatePlainCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // exclude ambiguous 0,O,1,I
        $code = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $chars[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Create a new access code for a student (e.g. on regenerate).
     * Revokes any existing active code for this student first if $revokeExisting is true.
     *
     * @return array{plain_code: string, access_code: StudentAccessCode}
     */
    public static function createCodeForStudent(Student $student, int $maxLinks = self::DEFAULT_MAX_LINKS, bool $revokeExisting = true): array
    {
        if ($revokeExisting) {
            StudentAccessCode::where('student_id', $student->id)
                ->where('status', StudentAccessCode::STATUS_ACTIVE)
                ->update([
                    'status' => StudentAccessCode::STATUS_REVOKED,
                    'revoked_at' => now(),
                ]);
        }

        $plainCode = self::generatePlainCode();
        $normalized = self::normalizeCode($plainCode);
        $last4 = strlen($normalized) >= 4 ? substr($normalized, -4) : $normalized;

        $accessCode = StudentAccessCode::create([
            'student_id' => $student->id,
            'code_hash' => Hash::make($normalized),
            'code_last4' => $last4,
            'plain_code_encrypted' => Crypt::encryptString($plainCode),
            'max_links' => $maxLinks,
            'status' => StudentAccessCode::STATUS_ACTIVE,
        ]);

        return [
            'plain_code' => $plainCode,
            'access_code' => $accessCode,
        ];
    }

    /**
     * Build student hint for API (minimal PII: first name, last initial, grade).
     *
     * @return array{first_name: string, last_initial: string, grade: array{name: string}}
     */
    public static function studentHint(Student $student): array
    {
        $name = trim($student->name);
        $parts = preg_split('/\s+/', $name, 2);
        $firstName = $parts[0] ?? 'Student';
        $lastInitial = '';
        if (isset($parts[1]) && $parts[1] !== '') {
            $lastInitial = strtoupper(substr($parts[1], 0, 1));
        }

        return [
            'first_name' => $firstName,
            'last_initial' => $lastInitial,
            'grade' => [
                'name' => $student->grade?->name ?? 'Unknown',
            ],
        ];
    }

    public static function inSandbox(): bool
    {
        return ! app()->environment('production')
            && (config('portal.sandbox_enabled') || app()->environment(['local', 'sandbox']));
    }

    /**
     * Create or link a parent account using a parent code.
     *
     * @return array{ok: bool, message: string, password: ?string, email: ?string, is_new: bool}
     */
    public static function signup(string $email, string $code): array
    {
        $email = strtolower(trim($email));
        $result = self::validateCode($code);
        if ($result === null) {
            return [
                'ok' => false,
                'message' => 'Invalid code or this student has reached the maximum number of linked accounts. Contact the school office.',
                'password' => null,
                'email' => $email,
                'is_new' => false,
            ];
        }

        $student = $result['student'];
        $accessCode = $result['access_code'];

        if (! $accessCode->isValid()) {
            return [
                'ok' => false,
                'message' => 'Invalid code or this student has reached the maximum number of linked accounts. Contact the school office.',
                'password' => null,
                'email' => $email,
                'is_new' => false,
            ];
        }

        $user = User::where('email', $email)->first();
        $isNewUser = $user === null;
        $sendWelcomeEmail = false;
        $plainPassword = null;

        DB::transaction(function () use ($email, $student, &$user, &$sendWelcomeEmail, &$plainPassword) {
            if ($user === null) {
                $plainPassword = self::inSandbox()
                    ? (string) config('portal.sandbox_password', 'Sandbox123!')
                    : PortalPassword::generate();
                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => $plainPassword,
                    'role' => User::ROLE_PARENT,
                    'is_approved' => true,
                    'approved_at' => now(),
                    'approved_by' => null,
                ]);
                $sendWelcomeEmail = true;
            } else {
                if (! $user->isStaff() && $user->role !== User::ROLE_PARENT) {
                    $user->update(['role' => User::ROLE_PARENT]);
                }
                if (! $user->isApproved()) {
                    $plainPassword = self::inSandbox()
                        ? (string) config('portal.sandbox_password', 'Sandbox123!')
                        : PortalPassword::generate();
                    $user->update([
                        'password' => $plainPassword,
                        'is_approved' => true,
                        'approved_at' => now(),
                        'approved_by' => null,
                    ]);
                    $sendWelcomeEmail = true;
                } elseif (self::inSandbox()) {
                    $plainPassword = (string) config('portal.sandbox_password', 'Sandbox123!');
                    $user->update(['password' => $plainPassword]);
                }
            }

            if (! $user->children()->where('students.id', $student->id)->exists()) {
                $user->children()->attach($student->id);
            }
        });

        if ($sendWelcomeEmail && $plainPassword !== null && ! self::inSandbox()) {
            $user->notify(new ParentCodeWelcome($plainPassword));
        }

        if (self::inSandbox() && $plainPassword === null) {
            $plainPassword = (string) config('portal.sandbox_password', 'Sandbox123!');
            $user->update(['password' => $plainPassword]);
        }

        return [
            'ok' => true,
            'message' => self::inSandbox()
                ? 'Account created. Copy the sandbox password below, then log in.'
                : ($isNewUser || $sendWelcomeEmail
                    ? 'Check your email for your password and login link.'
                    : 'Child added successfully.'),
            'password' => self::inSandbox() ? $plainPassword : null,
            'email' => $user->email,
            'is_new' => $isNewUser,
        ];
    }
}
