<?php

use App\Http\Controllers\Marketing\HomeController;
use App\Http\Controllers\Marketing\ContactController;
use App\Http\Controllers\Marketing\NewsController;
use App\Http\Controllers\Marketing\EventController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\PostController;
use App\Http\Controllers\Portal\CalendarController;
use App\Http\Controllers\Portal\LunchMenuController;
use App\Http\Controllers\Portal\SettingsController;
use App\Http\Controllers\Portal\TeacherNewsController;
use App\Http\Controllers\Portal\HelpController;
use App\Http\Controllers\Portal\Admin\ApprovalController;
use App\Http\Controllers\Portal\Admin\GradeController;
use App\Http\Controllers\Portal\Admin\StudentImportController;
use App\Http\Controllers\Portal\Admin\StaffController;
use App\Http\Controllers\Portal\Admin\StaffDirectoryController;
use App\Http\Controllers\Portal\Admin\StaffImportController;
use App\Http\Controllers\Portal\Admin\ParentController;
use App\Http\Controllers\Portal\Admin\LunchMenuImportController;
use App\Http\Controllers\Portal\Admin\StudentCodeController;
use App\Http\Controllers\Portal\Admin\StudentCodeEmailController;
use App\Http\Controllers\Portal\AddChildController;
use App\Http\Controllers\Portal\SandboxController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing Routes
|--------------------------------------------------------------------------
|
| These routes are for the public marketing website.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/staff', [HomeController::class, 'staff'])->name('staff');
Route::get('/why-silver-academy', [HomeController::class, 'whySilverAcademy'])->name('why-silver-academy');
Route::get('/our-community', [HomeController::class, 'ourCommunity'])->name('our-community');
Route::get('/admissions', [HomeController::class, 'admissions'])->name('admissions');
Route::get('/programs', [HomeController::class, 'services'])->name('programs');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:3,10')
    ->name('contact.store');

Route::prefix('programs')->name('programs.')->group(function () {
    Route::get('/ganeinu', [HomeController::class, 'ganeinu'])->name('ganeinu');
    Route::get('/kindergarten', [HomeController::class, 'kindergarten'])->name('kindergarten');
    Route::get('/lower-school', [HomeController::class, 'lowerSchool'])->name('lower-school');
    Route::get('/upper-school', [HomeController::class, 'upperSchool'])->name('upper-school');
    Route::get('/after-school', [HomeController::class, 'afterSchool'])->name('after-school');
    Route::get('/parent-circle', [HomeController::class, 'parentCircle'])->name('parent-circle');
});

Route::prefix('get-involved')->name('get-involved.')->group(function () {
    Route::get('/', [HomeController::class, 'getInvolved'])->name('index');
    Route::get('/donate', [HomeController::class, 'donate'])->name('donate');
    Route::get('/eitc-corporate', [HomeController::class, 'eitcCorporate'])->name('eitc-corporate');
    Route::get('/eitc-individual', [HomeController::class, 'eitcIndividual'])->name('eitc-individual');
    Route::get('/life-and-legacy', [HomeController::class, 'lifeAndLegacy'])->name('life-and-legacy');
    Route::get('/fundraisers', [HomeController::class, 'fundraisers'])->name('fundraisers');
});

// News & Events public routes
Route::get('/news-events', [NewsController::class, 'index'])->name('news-events');
Route::get('/news/{post:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/events/{post:slug}', [EventController::class, 'show'])->name('events.show');

/*
|--------------------------------------------------------------------------
| Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the authenticated parent portal.
|
*/

Route::middleware(['auth', 'approved'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/session/ping', fn () => response()->noContent())->name('session.ping');
    Route::get('/sandbox', [SandboxController::class, 'index'])->name('sandbox');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');
    Route::delete('/settings/avatar', [SettingsController::class, 'removeAvatar'])->name('settings.avatar.remove');

    // Help
    Route::get('/help', [HelpController::class, 'index'])->name('help');

    // Parent: add another child via code
    Route::post('/parent/add-child', [AddChildController::class, 'store'])->name('parent.add-child')->middleware('throttle:10,1');

    // Calendar (accessible to all authenticated users)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // Lunch Menus (view accessible to all, CRUD requires admin)
    Route::get('/lunch', [LunchMenuController::class, 'index'])->name('lunch.index');
    Route::get('/lunch/api/month', [LunchMenuController::class, 'getMenusForMonth'])->name('lunch.api.month');
    Route::middleware('admin')->group(function () {
        Route::get('/lunch/import', [LunchMenuImportController::class, 'showImport'])->name('lunch.import');
        Route::post('/lunch/import', [LunchMenuImportController::class, 'import'])->name('lunch.import.process');
        Route::get('/lunch/template', [LunchMenuImportController::class, 'downloadTemplate'])->name('lunch.template');
        Route::get('/lunch/export', [LunchMenuImportController::class, 'export'])->name('lunch.export');
        Route::get('/lunch/create', [LunchMenuController::class, 'create'])->name('lunch.create');
        Route::post('/lunch', [LunchMenuController::class, 'store'])->name('lunch.store');
        Route::get('/lunch/{lunch}/edit', [LunchMenuController::class, 'edit'])->name('lunch.edit');
        Route::put('/lunch/{lunch}', [LunchMenuController::class, 'update'])->name('lunch.update');
        Route::delete('/lunch/{lunch}', [LunchMenuController::class, 'destroy'])->name('lunch.destroy');
    });

    // Admin-only routes for managing posts
    Route::middleware('admin')->group(function () {
        Route::resource('posts', PostController::class)->except(['show']);
        Route::post('/posts/upload-image', [PostController::class, 'uploadImage'])->name('posts.upload-image');
        Route::post('/posts/{post}/toggle-publish', [PostController::class, 'togglePublish'])
            ->name('posts.toggle-publish');
    });

    // Teacher routes for posting grade-specific news
    Route::get('/teacher-news', [TeacherNewsController::class, 'index'])->name('teacher-news.index');
    Route::get('/teacher-news/create', [TeacherNewsController::class, 'create'])->name('teacher-news.create');
    Route::post('/teacher-news', [TeacherNewsController::class, 'store'])->name('teacher-news.store');
    Route::delete('/teacher-news/{post}', [TeacherNewsController::class, 'destroy'])->name('teacher-news.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Grade & Student Management)
|--------------------------------------------------------------------------
|
| These routes are for admin management of grades, students, and teachers.
|
*/

Route::middleware(['auth', 'admin'])->prefix('portal/admin')->name('admin.')->group(function () {
    // Approval Management (PARENTS ONLY - staff are imported)
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{user}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{user}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{user}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('/approvals/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approvals.bulk-approve');

    // Staff Management
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/import', [StaffImportController::class, 'showImport'])->name('staff.import');
    Route::post('/staff/import', [StaffImportController::class, 'import'])->name('staff.import.process');
    Route::get('/staff/template', [StaffImportController::class, 'downloadTemplate'])->name('staff.template');
    Route::get('/staff/export-credentials', [StaffImportController::class, 'exportCredentials'])->name('staff.export-credentials');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::match(['put', 'patch', 'post'], '/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{staff}/toggle-role', [StaffController::class, 'toggleRole'])->name('staff.toggle-role');
    Route::post('/staff/{staff}/send-welcome', [StaffController::class, 'sendWelcomeEmail'])->name('staff.send-welcome');
    Route::post('/staff/send-bulk-welcome', [StaffController::class, 'sendBulkWelcomeEmails'])->name('staff.send-bulk-welcome');
    Route::post('/staff/send-all-pending-welcome', [StaffController::class, 'sendAllPendingWelcomeEmails'])->name('staff.send-all-pending-welcome');

    // Staff Directory (public website staff page)
    Route::get('/staff-directory', [StaffDirectoryController::class, 'index'])->name('staff-directory.index');
    Route::get('/staff-directory/create', [StaffDirectoryController::class, 'create'])->name('staff-directory.create');
    Route::post('/staff-directory', [StaffDirectoryController::class, 'store'])->name('staff-directory.store');
    Route::get('/staff-directory/{staffDirectory}/edit', [StaffDirectoryController::class, 'edit'])->name('staff-directory.edit');
    Route::put('/staff-directory/{staffDirectory}', [StaffDirectoryController::class, 'update'])->name('staff-directory.update');
    Route::delete('/staff-directory/{staffDirectory}', [StaffDirectoryController::class, 'destroy'])->name('staff-directory.destroy');

    // Parent Management
    Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
    Route::get('/parents/{parent}/edit', [ParentController::class, 'edit'])->name('parents.edit');
    Route::put('/parents/{parent}', [ParentController::class, 'update'])->name('parents.update');
    Route::delete('/parents/{parent}', [ParentController::class, 'destroy'])->name('parents.destroy');
    Route::post('/parents/{parent}/resend-verification', [ParentController::class, 'resendVerification'])->name('parents.resend-verification');
    Route::post('/parents/{parent}/verify-email', [ParentController::class, 'verifyEmail'])->name('parents.verify-email');

    // Student Import/Export
    Route::get('/students/import', [StudentImportController::class, 'showImport'])->name('students.import');
    Route::post('/students/import', [StudentImportController::class, 'import'])->name('students.import.process');
    Route::get('/students/template', [StudentImportController::class, 'downloadTemplate'])->name('students.template');
    Route::get('/students/export', [StudentImportController::class, 'export'])->name('students.export');

    // Student Parent Code management
    Route::get('/students/{student}/code', [StudentCodeController::class, 'show'])->name('students.code.show');
    Route::get('/students/{student}/code/regenerate/confirm', [StudentCodeController::class, 'confirmRegenerate'])->name('students.code.regenerate.confirm');
    Route::post('/students/{student}/code/regenerate', [StudentCodeController::class, 'regenerate'])->name('students.code.regenerate');
    Route::post('/students/{student}/send-code-to-parent', [StudentCodeEmailController::class, 'sendToParent'])->name('students.send-code-to-parent');
    Route::patch('/students/{student}/code', [StudentCodeController::class, 'update'])->name('students.code.update');

    // Grade Management (replaces Classroom Management)
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
    Route::post('/grades/{grade}/send-codes-to-parents', [StudentCodeEmailController::class, 'bulkSendForGrade'])->name('grades.send-codes-to-parents');
    Route::put('/grades/{grade}/teachers', [GradeController::class, 'updateTeachers'])->name('grades.update-teachers');
    Route::post('/grades/{grade}/students', [GradeController::class, 'storeStudent'])->name('grades.students.store');
    Route::put('/grades/{grade}/students/{student}', [GradeController::class, 'updateStudent'])->name('grades.students.update');
    Route::delete('/grades/{grade}/students/{student}', [GradeController::class, 'destroyStudent'])->name('grades.students.destroy');
    Route::post('/grades/move-students', [GradeController::class, 'moveStudents'])->name('grades.move-students');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes will be added by Laravel Breeze/Fortify when auth is set up.
|
*/

require __DIR__.'/auth.php';
