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
        'folder_id',
        'department_id',
        'current_department_id',
        'recommended_department_id',
        'file_name',
        'file_number',
        'remarks',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'created_by',
        'current_user_id',
        'status',
        'is_public',
        'has_permsec_reviewed',
        'completed_at',
        'return_deadline',
        'is_urgent',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'has_permsec_reviewed' => 'boolean',
        'completed_at' => 'datetime',
        'return_deadline' => 'datetime',
        'is_urgent' => 'boolean',
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

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
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

    public function recommendedDepartment()
    {
        return $this->belongsTo(Department::class, 'recommended_department_id');
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

    /**
     * Check if the file has been submitted to or reviewed by the Permanent Secretary.
     */
    public function hasBeenToPermSec(): bool
    {
        if ($this->has_permsec_reviewed) {
            return true;
        }

        return $this->movements()->whereHas('toUser', function ($q) {
            $q->whereHas('designation', function ($d) {
                $d->where('name', 'Permanent Secretary');
            })->orWhere('email', 'permsec@filetrack.local');
        })->exists();
    }

    /**
     * Scope a query to only include publicly viewable files.
     */
    public function scopePubliclyViewable($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Calculate hours spent with current holder or department.
     */
    public function hoursWithCurrentHolder(): int
    {
        $lastMovement = $this->movements()->latest('created_at')->first();
        $assignedAt = $lastMovement?->created_at ?? $this->updated_at ?? $this->created_at;

        return (int) $assignedAt->diffInHours(now());
    }

    /**
     * Check if a file is overdue (> 8 hours in non-records dept OR return_deadline passed).
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $currentDeptCode = strtoupper((string) ($this->currentDepartment?->code ?? ''));
        $currentDeptName = Str::lower((string) ($this->currentDepartment?->name ?? ''));
        $isRecordsDept = ($currentDeptCode === 'REC' || Str::contains($currentDeptName, 'record'));

        if ($isRecordsDept) {
            return false;
        }

        if ($this->return_deadline && now()->greaterThan($this->return_deadline)) {
            return true;
        }

        return $this->hoursWithCurrentHolder() >= 8;
    }

    /**
     * Scope query to files that are overdue (> 8 hours or past return_deadline in non-records dept).
     */
    public function scopeOverdue($query)
    {
        $recordsDeptIds = Department::where('code', 'REC')
            ->orWhere('name', 'like', '%record%')
            ->pluck('id');

        $eightHoursAgo = now()->subHours(8);

        return $query->where('status', '!=', 'completed')
            ->whereNotIn('current_department_id', $recordsDeptIds)
            ->where(function ($q) use ($eightHoursAgo) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('return_deadline')
                        ->where('return_deadline', '<', now());
                })->orWhere(function ($sub) use ($eightHoursAgo) {
                    $sub->where('updated_at', '<=', $eightHoursAgo);
                });
            });
    }
}
