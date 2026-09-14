<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public const CACHE_TTL_SECONDS = 3600; // 1 hour

    /**
     * Get active departments list cached.
     */
    public static function getActiveDepartments()
    {
        return Cache::remember('departments_active_list', self::CACHE_TTL_SECONDS, function () {
            return Department::where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Clear active departments cache.
     */
    public static function clearDepartmentsCache(): void
    {
        Cache::forget('departments_active_list');
    }

    /**
     * Get active designations list cached.
     */
    public static function getActiveDesignations()
    {
        return Cache::remember('designations_active_list', self::CACHE_TTL_SECONDS, function () {
            return Designation::where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Clear active designations cache.
     */
    public static function clearDesignationsCache(): void
    {
        Cache::forget('designations_active_list');
    }
}
