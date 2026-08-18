<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\LunchMenu;
use App\Models\Post;
use App\Models\Student;
use App\Models\User;
use App\Services\ParentCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PortalSandboxSeeder extends Seeder
{
    public const PASSWORD = 'Sandbox123!';

    /**
     * Known sandbox logins for local/staging testing only.
     *
     * @return list<array{role: string, name: string, email: string, password: string, notes: string}>
     */
    public static function accounts(): array
    {
        return [
            [
                'role' => 'Parent',
                'name' => 'Sandbox Parent',
                'email' => 'parent@sandbox.silver.test',
                'password' => self::PASSWORD,
                'notes' => 'Linked to Emma (Kindergarten) and Noah (1st Grade). Use this to test the parent dashboard, grade news, and calendar.',
            ],
            [
                'role' => 'Staff',
                'name' => 'Sandbox Staff',
                'email' => 'staff@sandbox.silver.test',
                'password' => self::PASSWORD,
                'notes' => 'Kindergarten teacher. Use this to post grade news and confirm it appears for the parent.',
            ],
            [
                'role' => 'Staff + Parent',
                'name' => 'Sandbox Staff Parent',
                'email' => 'staffparent@sandbox.silver.test',
                'password' => self::PASSWORD,
                'notes' => 'Staff member who also has a child (Emma). Use View as: Parent View, then Sign out to switch accounts.',
            ],
            [
                'role' => 'Admin',
                'name' => 'Sandbox Admin',
                'email' => 'admin@sandbox.silver.test',
                'password' => self::PASSWORD,
                'notes' => 'School admin. Create/publish events and confirm they show on the August 2026 calendar.',
            ],
            [
                'role' => 'Super Admin',
                'name' => 'Sandbox Super Admin',
                'email' => 'superadmin@sandbox.silver.test',
                'password' => self::PASSWORD,
                'notes' => 'Use this one login. The Preview as dropdown in the header switches Admin, Staff, and Parent views — including linked students Emma and Noah.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function checklist(): array
    {
        return [
            'Log in as superadmin@sandbox.silver.test and use Preview as in the header to switch Admin, Staff, and Parent views.',
            'In Parent View, confirm grade news and linked students (Emma, Noah) appear.',
            'In Staff View, open Post Grade News and post to Kindergarten.',
            'In Admin View, open Calendar, go to August 2026, and confirm “Start of School” appears on August 25.',
            'Create a new event as admin and confirm it shows on the matching calendar day.',
            'Use Sign out only if you specifically need to test a separate parent or staff password.',
            'To test parent signup: Sign out, open /parent/signup, use the Jordan Sandbox code from this page, then log in with the password shown on screen (always Sandbox123! in this lab).',
        ];
    }

    public function run(): void
    {
        $this->call(GradeSeeder::class);

        $kindergarten = Grade::where('name', 'Kindergarten')->firstOrFail();
        $firstGrade = Grade::where('name', '1st Grade')->firstOrFail();

        $parent = $this->upsertUser('Sandbox Parent', 'parent@sandbox.silver.test', User::ROLE_PARENT);
        $staff = $this->upsertUser('Sandbox Staff', 'staff@sandbox.silver.test', User::ROLE_TEACHER);
        $staffParent = $this->upsertUser('Sandbox Staff Parent', 'staffparent@sandbox.silver.test', User::ROLE_TEACHER);
        $admin = $this->upsertUser('Sandbox Admin', 'admin@sandbox.silver.test', User::ROLE_ADMIN);
        $superAdmin = $this->upsertUser('Sandbox Super Admin', 'superadmin@sandbox.silver.test', User::ROLE_SUPER_ADMIN);

        $staff->grades()->syncWithoutDetaching([$kindergarten->id]);
        $staffParent->grades()->syncWithoutDetaching([$kindergarten->id]);
        $superAdmin->grades()->syncWithoutDetaching([$kindergarten->id]);

        $emma = Student::updateOrCreate(
            ['name' => 'Emma Sandbox'],
            ['grade_id' => $kindergarten->id, 'status' => Student::STATUS_ACTIVE]
        );
        $noah = Student::updateOrCreate(
            ['name' => 'Noah Sandbox'],
            ['grade_id' => $firstGrade->id, 'status' => Student::STATUS_ACTIVE]
        );
        $secondGrade = Grade::where('name', '2nd Grade')->firstOrFail();
        $jordan = Student::updateOrCreate(
            ['name' => 'Jordan Sandbox'],
            ['grade_id' => $secondGrade->id, 'status' => Student::STATUS_ACTIVE]
        );

        $parent->children()->syncWithoutDetaching([$emma->id, $noah->id]);
        $staffParent->children()->syncWithoutDetaching([$emma->id]);
        $superAdmin->children()->syncWithoutDetaching([$emma->id, $noah->id]);

        $codeResult = ParentCodeService::createCodeForStudent($jordan, 5, true);
        $plainCode = $codeResult['plain_code'];

        Post::updateOrCreate(
            ['slug' => 'sandbox-start-of-school'],
            [
                'user_id' => $admin->id,
                'type' => 'event',
                'audience' => 'all',
                'title' => 'Start of School',
                'content' => 'First day of school. Drop-off 7:45–8:00 AM.',
                'event_start_date' => Carbon::parse('2026-08-25 08:00:00', 'America/New_York'),
                'is_public' => false,
                'published_at' => now(),
            ]
        );

        Post::updateOrCreate(
            ['slug' => 'sandbox-kindergarten-welcome'],
            [
                'user_id' => $staff->id,
                'type' => 'news',
                'audience' => 'grade',
                'target_grade_id' => $kindergarten->id,
                'title' => 'Start of School for Kindergarten',
                'content' => "Our Early Childhood Program and Kindergarten students will begin with a half day on Tuesday, August 25.\n\nDrop-off: 7:45–8:00 AM\nPickup: 12:45 PM\nLunch will be served before dismissal.",
                'is_public' => false,
                'published_at' => now(),
            ]
        );

        $monday = Carbon::now()->startOfWeek();
        LunchMenu::updateOrCreate(
            ['menu_date' => $monday->toDateString()],
            [
                'user_id' => $admin->id,
                'content' => 'Sandbox lunch: pasta, salad, fruit.',
            ]
        );

        $this->command?->info('Portal sandbox seeded.');
        $this->command?->info('Password for all sandbox accounts: '.self::PASSWORD);
        $this->command?->info("Jordan Sandbox parent code (for new parent signup): {$plainCode}");
        foreach (self::accounts() as $account) {
            $this->command?->line("  {$account['role']}: {$account['email']}");
        }
    }

    private function upsertUser(string $name, string $email, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => self::PASSWORD,
                'role' => $role,
                'is_approved' => true,
                'approved_at' => now(),
                'email_verified_at' => now(),
            ]
        );
    }
}
