<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\Folder;
use App\Policies\FileRecordPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register file authorization policy
        Gate::policy(FileRecord::class, FileRecordPolicy::class);

        $this->configureRateLimiting();
        Paginator::useBootstrapFive();

        // Share departments & folders for create-file-modal overlay for authenticated users
        View::composer('partials.create-file-modal', function ($view) {
            if (auth()->check()) {
                $view->with('globalDepartments', Department::where('is_active', true)->orderBy('name')->get());
                $view->with('globalFolders', Folder::orderBy('folder_number')->get());
            }
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
