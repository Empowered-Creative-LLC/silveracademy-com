<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Database\Seeders\PortalSandboxSeeder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SandboxController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(config('portal.sandbox_enabled') && ! app()->environment('production'), 404);

        $user = $request->user();
        abort_unless($user?->isAdmin() || $user?->isSuperAdmin(), 403);

        $signupStudents = Student::query()
            ->with(['grade:id,name', 'accessCodes'])
            ->whereIn('name', ['Jordan Sandbox'])
            ->get()
            ->map(function (Student $student) {
                $code = $student->activeAccessCode()?->getDecryptedPlainCode();

                return [
                    'name' => $student->name,
                    'grade' => $student->grade?->name,
                    'code' => $code,
                    'signup_url' => url('/parent/signup'),
                ];
            })
            ->filter(fn (array $row) => filled($row['code']))
            ->values();

        return Inertia::render('Portal/Sandbox', [
            'accounts' => PortalSandboxSeeder::accounts(),
            'checklist' => PortalSandboxSeeder::checklist(),
            'signupCodes' => $signupStudents,
        ]);
    }
}
