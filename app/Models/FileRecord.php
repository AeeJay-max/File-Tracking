<?php

namespace App\Models;

use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FileRecord extends Model
{
    protected $table = 'file_records';

    protected $fillable = [
        'uuid',
        'department_id',
        'current_department_id',
        'file_name',
        'file_number',
        'remarks',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'created_by',
        'current_user_id',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
        // Invalidate dashboard caches after writes
        static::created(fn () => DashboardService::clearSuperAdminCache());
        static::deleted(fn () => DashboardService::clearSuperAdminCache());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    // Alias for views using ->currentUser
    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * The department that currently holds this file.
     * Set on creation, updated on every cross-department transfer.
     * When current_user_id is NULL, this department is responsible for assignment.
     */
    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    /**
     * Scope by origin department + file number identity.
     * Use this when looking up the file that was CREATED in a given department.
     */
    public function scopeByOriginDepartmentAndNumber($query, int $departmentId, string $fileNumber)
    {
        return $query
            ->where('department_id', $departmentId)
            ->where('file_number', strtoupper(trim($fileNumber)));
    }

    /**
     * Scope by current holder department + file number identity.
     * Use this when looking up which file a department currently holds by number.
     */
    public function scopeByCurrentDepartmentAndNumber($query, int $departmentId, string $fileNumber)
    {
        return $query
            ->where('current_department_id', $departmentId)
            ->where('file_number', strtoupper(trim($fileNumber)));
    }

    /**
     * Check whether a given file number already exists in a department.
     * Excludes the current record when checking on updates (pass $excludeId).
     */
    public static function existsInDepartment(int $departmentId, string $fileNumber, ?int $excludeId = null): bool
    {
        $query = static::where('department_id', $departmentId)
            ->where('file_number', strtoupper(trim($fileNumber)));

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function movements()
    {
        return $this->hasMany(FileMovement::class, 'file_id');
    }
}
