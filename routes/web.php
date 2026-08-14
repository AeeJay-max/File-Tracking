<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDesignationController;
use App\Http\Controllers\Admin\AdminFileAssignmentController;
use App\Http\Controllers\Admin\AdminFileController;
use App\Http\Controllers\Admin\AdminTransferController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\FileTimelineController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\FileRecordController;
use App\Http\Controllers\FileTransferController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicFileSearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingPageController::class, 'index'])->name('welcome');
Route::get('/about', fn () => redirect('/#about'))->name('about');
Route::get('/features', fn () => redirect('/#features'))->name('features');
Route::get('/privacy-policy', fn () => view('pages.privacy'))->name('privacy');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');
Route::get('/help', fn () => view('pages.help'))->name('help');

// Public File Search — accessible without login
Route::get('/public/file-search', [PublicFileSearchController::class, 'index'])->name('public.file.search');
Route::get('/public/file-search/result', [PublicFileSearchController::class, 'search'])->name('public.file.search.result');

/*
|--------------------------------------------------------------------------
| ALL AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'no.cache', 'force.pwd.change'])->group(function () {

    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'super_admin' => redirect()->route('super_admin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    })->name('dashboard');

    // Files (UUID-based route model binding)
    Route::get('/files', [FileRecordController::class, 'index'])->name('files.index');
    Route::get('/files/create', [FileRecordController::class, 'create'])->name('files.create');
    Route::post('/files', [FileRecordController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [FileRecordController::class, 'show'])->name('files.show');
    Route::get('/files/{file}/edit', [FileRecordController::class, 'edit'])->name('files.edit');
    Route::put('/files/{file}', [FileRecordController::class, 'update'])->name('files.update');
    Route::get('/files/{file}/download', [FileRecordController::class, 'download'])->name('files.download');

    // Transfer (immediate — no approval)
    Route::get('/files/{file}/transfer', [FileTransferController::class, 'create'])->name('files.transfer.create');
    Route::post('/files/transfer', [FileTransferController::class, 'store'])->name('files.transfer.store');

    // AJAX: department search for transfer form autocomplete
    Route::get('/ajax/departments/search', [FileTransferController::class, 'searchDepartments'])->name('ajax.departments.search');

    // AJAX: inline department creation from File Creation page (any authenticated user)
    Route::post('/ajax/departments/create', [DepartmentController::class, 'storeAjax'])->name('ajax.departments.create');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-visible', [NotificationController::class, 'markVisibleAsRead'])->name('notifications.readVisible');

    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonation.start');
    Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
});

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES — auth + verified required, but ForcePasswordChange is
| intentionally NOT applied here. An unverified user with
| must_change_password = true must be able to reach /profile to change
| their password after verifying their email. Adding 'verified' here is
| correct: password change should only be allowed once email is confirmed.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'no.cache'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo.upload');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| USER DASHBOARD — role:user only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'no.cache', 'force.pwd.change', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
});

/*
|--------------------------------------------------------------------------
| SUPER ADMIN — departments, admin management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin', 'no.cache', 'force.pwd.change'])->group(function () {
    Route::resource('departments', DepartmentController::class);
    Route::resource('users', UserController::class);

    Route::get('/super-admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('super_admin.dashboard');
});

/*
|--------------------------------------------------------------------------
| SUPER ADMIN + ADMIN SHARED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,admin', 'no.cache', 'force.pwd.change'])->group(function () {
    Route::resource('designations', DesignationController::class);
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — admin + super_admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin,admin', 'no.cache', 'force.pwd.change'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Admin manages users scoped to their dept
        Route::resource('users', AdminUserController::class);
        Route::resource('designations', AdminDesignationController::class);

        // Files — view + timeline only
        // NOTE: /files/pending MUST be declared before /files/{uuid} to avoid route shadowing
        Route::get('/files/pending', [AdminFileAssignmentController::class, 'index'])->name('files.pending');
        Route::post('/files/pending/{uuid}/assign', [AdminFileAssignmentController::class, 'assign'])->name('files.pending.assign');

        Route::get('/files', [AdminFileController::class, 'index'])->name('files');
        Route::get('/files/{uuid}/timeline', [FileTimelineController::class, 'show'])->name('files.timeline');
        Route::get('/files/{uuid}', [FileTimelineController::class, 'fileDetails'])->name('files.show');

        // Transfer history — read-only monitoring
        Route::get('/transfers', [AdminTransferController::class, 'index'])->name('transfers');

        // Backup — super_admin only
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
            Route::post('/backup', [BackupController::class, 'create'])->name('backup.create');
            Route::get('/backup/{filename}/download', [BackupController::class, 'download'])->name('backup.download');
            Route::delete('/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');
        });
    });

require __DIR__.'/auth.php';
