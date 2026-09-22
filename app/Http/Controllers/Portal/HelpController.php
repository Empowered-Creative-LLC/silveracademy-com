<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    /**
     * Display the help page with role-appropriate content.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $actualRole = $user->role;
        
        // Super admins can preview help for other roles
        $previewRole = request()->query('preview');
        $allowedPreviews = ['admin', 'teacher', 'parent'];
        
        if ($user->isSuperAdmin() && $previewRole && in_array($previewRole, $allowedPreviews)) {
            $role = $previewRole;
        } else {
            $role = $actualRole;
        }

        // Determine which help sections to show based on role
        $sections = $this->getHelpSections($role);

        return Inertia::render('Portal/Help', [
            'user' => $user,
            'sections' => $sections,
            'role' => $role,
            'actualRole' => $actualRole,
        ]);
    }

    /**
     * Get help sections based on user role.
     */
    private function getHelpSections(string $role): array
    {
        $commonSections = [
            [
                'id' => 'getting-started',
                'title' => 'Getting Started',
                'icon' => 'rocket',
                'items' => [
                    [
                        'title' => 'Logging In',
                        'content' => 'Use your email address and password to log in to the Family Portal. If you\'ve forgotten your password, click "Forgot Password" on the login page to reset it.',
                    ],
                    [
                        'title' => 'Updating Your Profile',
                        'content' => 'Click on your avatar in the top right corner and select "Settings" to update your name, email, password, or profile picture.',
                    ],
                ],
            ],
            [
                'id' => 'calendar',
                'title' => 'Using the Calendar',
                'icon' => 'calendar',
                'items' => [
                    [
                        'title' => 'Viewing Events',
                        'content' => 'The calendar shows all school events. Click on any event to see more details. Use the toggle at the top to switch between Events, Lunch Menu, or Both views.',
                    ],
                    [
                        'title' => 'Lunch Menus',
                        'content' => 'Switch to "Lunch Menu" view to see the daily lunch menus. Click on any day to see the full menu details.',
                    ],
                ],
            ],
        ];

        switch ($role) {
            case 'super_admin':
            case 'admin':
                return array_merge($commonSections, $this->getAdminSections());
            case 'teacher':
                return array_merge($commonSections, $this->getTeacherSections());
            case 'parent':
            default:
                return array_merge($commonSections, $this->getParentSections());
        }
    }

    /**
     * Get admin-specific help sections.
     */
    private function getAdminSections(): array
    {
        return [
            [
                'id' => 'staff-management',
                'title' => 'Staff Management',
                'icon' => 'users',
                'items' => [
                    [
                        'title' => 'Adding Staff Members',
                        'content' => 'Go to Staff Management and click "Add Staff Member". Enter their name, email, and select their role (Staff Member or Administrator). Assign them to grade levels if applicable. A welcome email with login credentials will be sent automatically.',
                    ],
                    [
                        'title' => 'Bulk Importing Staff',
                        'content' => 'Click "Import" on the Staff Management page to upload multiple staff members at once via CSV or Excel file. Download the template first to ensure your file has the correct format. After import, use "Send All Welcome Emails" to generate passwords and send login credentials.',
                    ],
                    [
                        'title' => 'Assigning Grades to Teachers',
                        'content' => 'When creating or editing a staff member, use the checkboxes to assign them to one or more grade levels. Teachers can only post news to grades they are assigned to.',
                    ],
                ],
            ],
            [
                'id' => 'parent-management',
                'title' => 'Parent Management',
                'icon' => 'family',
                'items' => [
                    [
                        'title' => 'Approving Registrations',
                        'content' => 'When parents register, they appear in the Approvals queue. Review their information, link them to their children, then approve. An email with login credentials will be sent automatically.',
                    ],
                    [
                        'title' => 'Linking Children to Parents',
                        'content' => 'During approval or from the Parents page, you can link students to parent accounts. Parents will only see news and information relevant to their children\'s grade levels.',
                    ],
                ],
            ],
            [
                'id' => 'grades-students',
                'title' => 'Grades & Students',
                'icon' => 'academic',
                'items' => [
                    [
                        'title' => 'Managing Grade Levels',
                        'content' => 'The Grades page shows all grade levels with student counts and assigned teachers. Click on a grade to view and manage its students and teacher assignments.',
                    ],
                    [
                        'title' => 'Importing Students',
                        'content' => 'Use the Import feature to bulk upload students. The template requires student name and grade; classroom and teacher email are optional. Students can be moved between grades as needed.',
                    ],
                    [
                        'title' => 'Parent Codes',
                        'content' => 'Each student can have a Parent Code so families can sign up or link that student to their account. In Grades, open a grade and use "Generate" or "Regenerate" in the Parent Code column. The code is shown only once—copy it and share it securely with the family. By default, up to 5 parent accounts can link per student.',
                    ],
                ],
            ],
            [
                'id' => 'news-events',
                'title' => 'News & Events',
                'icon' => 'megaphone',
                'items' => [
                    [
                        'title' => 'Creating News Posts',
                        'content' => 'Go to News & Events and click "Create Post". Choose "News" as the type. Set the audience to "Everyone" for school-wide announcements, or target specific groups like teachers only.',
                    ],
                    [
                        'title' => 'Creating Events',
                        'content' => 'Choose "Event" as the type when creating a post. Set the event date, and optionally make it recurring (daily, weekly, bi-weekly, or monthly). Mark "School Closure" for days when school is closed.',
                    ],
                    [
                        'title' => 'Public vs Portal-Only',
                        'content' => 'Check "Show on public website" to make news/events visible to everyone. Uncheck it to keep content visible only to logged-in portal users.',
                    ],
                ],
            ],
            [
                'id' => 'lunch-menus',
                'title' => 'Lunch Menus',
                'icon' => 'clipboard',
                'items' => [
                    [
                        'title' => 'Adding Lunch Menus',
                        'content' => 'From the Calendar, switch to "Lunch Menu" view and click "Add menu" or click directly on a weekday. Enter the menu details for that day.',
                    ],
                    [
                        'title' => 'Bulk Importing Menus',
                        'content' => 'Click "Import Menus" to upload a week or month of menus at once. Download the template to see the required format (date and menu content columns).',
                    ],
                ],
            ],
            [
                'id' => 'preview-mode',
                'title' => 'Preview Mode',
                'icon' => 'eye',
                'items' => [
                    [
                        'title' => 'Testing Different Views',
                        'content' => 'As a Super Admin, you can preview how the portal looks for different user types. Click the "Preview as" button and toggle between Admin, Staff, and Parent views.',
                    ],
                    [
                        'title' => 'What Changes in Preview',
                        'content' => 'Preview mode changes the navigation menu and dashboard content to match what that role would see. Use this to verify that news, events, and permissions are working correctly.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get teacher-specific help sections.
     */
    private function getTeacherSections(): array
    {
        return [
            [
                'id' => 'posting-news',
                'title' => 'Posting Grade-Specific News',
                'icon' => 'pencil',
                'items' => [
                    [
                        'title' => 'How to Post News to Parents',
                        'content' => 'From your dashboard, click the "Post Grade News" button in the Quick Actions section. Then follow these steps:',
                        'steps' => [
                            'Use the dropdown to select which grade level your news is for (only grades assigned to you will appear).',
                            'Enter a clear, descriptive title like "Upcoming Field Trip" or "Homework Reminder".',
                            'Write your full message in the content area, including dates, times, and any action items.',
                            'Click "Post News" to publish immediately to parents with children in that grade.',
                        ],
                    ],
                    [
                        'title' => 'Managing Your Posts',
                        'content' => 'Your dashboard lists these under Messages to Families, including notes an admin sent to your grade. Open My Grade News to see the full list and delete your own posts.',
                    ],
                    [
                        'title' => 'What Parents See',
                        'content' => 'Your grade-specific news appears in the "News & Announcements" section at the top of parents\' dashboards. It shows a grade badge, your name as the author, and the date posted.',
                    ],
                    [
                        'title' => 'No Grades Assigned?',
                        'content' => 'If you don\'t see the "Post Grade News" button or have no grade options, contact an administrator to have grade levels assigned to your account.',
                    ],
                ],
            ],
            [
                'id' => 'staff-announcements',
                'title' => 'Staff Announcements',
                'icon' => 'megaphone',
                'items' => [
                    [
                        'title' => 'Viewing Announcements',
                        'content' => 'Your dashboard shows announcements from administration. These may be for all staff, for teachers of specific grades, or addressed specifically to you.',
                    ],
                    [
                        'title' => 'Announcement Types',
                        'content' => 'Green badges indicate "All Staff" announcements. Amber badges show grade-specific announcements. Purple badges are messages sent directly to you.',
                    ],
                ],
            ],
            [
                'id' => 'school-info',
                'title' => 'School Information',
                'icon' => 'info',
                'items' => [
                    [
                        'title' => 'News & Events',
                        'content' => 'Click "News & Events" in the navigation to view the public school news and events page. This opens in a new tab.',
                    ],
                    [
                        'title' => 'Lunch Menus',
                        'content' => 'Click "Lunch Menu" on your dashboard to view the calendar with lunch menu information. You can see what\'s being served each day.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get parent-specific help sections.
     */
    private function getParentSections(): array
    {
        return [
            [
                'id' => 'news-announcements',
                'title' => 'News & Announcements',
                'icon' => 'newspaper',
                'items' => [
                    [
                        'title' => 'Understanding the News Section',
                        'content' => 'Your dashboard shows news relevant to your family. "School-Wide" news (blue badge) is for all families. News with a grade badge (green) is specific to your child\'s grade level.',
                    ],
                    [
                        'title' => 'Grade-Specific News',
                        'content' => 'Teachers post news specifically for parents of students in their grade. You\'ll only see news for the grades your children are enrolled in.',
                    ],
                    [
                        'title' => 'Reading Full Articles',
                        'content' => 'Click "Read more" on school-wide news to see the full article on the school website.',
                    ],
                ],
            ],
            [
                'id' => 'lunch-info',
                'title' => 'Lunch Information',
                'icon' => 'utensils',
                'items' => [
                    [
                        'title' => 'This Week\'s Menu',
                        'content' => 'Your dashboard shows the lunch menu for the current week. Each day shows what\'s being served.',
                    ],
                    [
                        'title' => 'Viewing Full Menu',
                        'content' => 'Click "View All" to see the complete calendar with all lunch menus. You can browse past and future weeks.',
                    ],
                ],
            ],
            [
                'id' => 'events-calendar',
                'title' => 'Events & Calendar',
                'icon' => 'calendar',
                'items' => [
                    [
                        'title' => 'Upcoming Events',
                        'content' => 'Your dashboard shows upcoming school events. Click "View Calendar" to see all events in a calendar view.',
                    ],
                    [
                        'title' => 'Event Details',
                        'content' => 'Click on any event to see full details including time, description, and any registration links.',
                    ],
                    [
                        'title' => 'School Closures',
                        'content' => 'Days when school is closed are marked in red on the calendar. These include holidays, teacher workdays, and weather closures.',
                    ],
                ],
            ],
            [
                'id' => 'account-settings',
                'title' => 'Your Account',
                'icon' => 'user',
                'items' => [
                    [
                        'title' => 'Signing up with a Parent Code',
                        'content' => 'If the school gave you a Parent Code, go to the login page and click "First time? Sign up with a Parent Code." Enter your email and the code. You will receive an email with a temporary password and a link to log in. We recommend changing your password after your first login (Settings).',
                    ],
                    [
                        'title' => 'Adding another child',
                        'content' => 'If you have more than one child at Silver Academy, go to Settings and find the "Linked Students" section. Enter the Parent Code for your other child in the "Link another student" field and click "Add child." That child\'s grade-level news will then appear on your dashboard.',
                    ],
                    [
                        'title' => 'Where to get your code',
                        'content' => 'Parent Codes are provided by the school office or your child\'s teacher. Each student has a unique code; keep it private and share only with guardians who should have portal access.',
                    ],
                    [
                        'title' => 'Updating Your Information',
                        'content' => 'Click on your avatar and select "Settings" to update your name, email address, or profile picture.',
                    ],
                    [
                        'title' => 'Changing Your Password',
                        'content' => 'In Settings, scroll to the "Update Password" section. Enter your current password, then your new password twice to confirm.',
                    ],
                    [
                        'title' => 'Multiple Children',
                        'content' => 'If you have multiple children at Silver Academy, you\'ll see news and information for all of their grade levels combined on your dashboard.',
                    ],
                ],
            ],
            [
                'id' => 'contact',
                'title' => 'Getting Help',
                'icon' => 'question',
                'items' => [
                    [
                        'title' => 'Contacting the School',
                        'content' => 'For general questions, contact the school office. For grade-specific questions, reach out to your child\'s teacher directly.',
                    ],
                    [
                        'title' => 'Technical Issues',
                        'content' => 'If you\'re experiencing technical problems with the portal, please contact the school office and describe the issue.',
                    ],
                ],
            ],
        ];
    }
}
