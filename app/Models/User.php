<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'designation_id',
        'employee_code',
        'phone',
        'is_active',
        'contact_number',
        'photo',
        'can_create_file',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'can_create_file' => 'boolean',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user's profile photo URL or null if none.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::disk('public')->url($this->photo);
        }

        return null;
    }

    /**
     * Get initials for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withDefault(['name' => 'No Department']);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class)->withDefault(['name' => '—']);
    }

    /**
     * Mark all unread notifications related to a specific file as read for this user.
     */
    public function markFileNotificationsRead(FileRecord|int|string $file): void
    {
        $fileId = $file instanceof FileRecord ? $file->id : $file;
        $fileUuid = $file instanceof FileRecord ? $file->uuid : null;

        $this->unreadNotifications->filter(function ($notification) use ($fileId, $fileUuid) {
            $data = $notification->data;
            if (! is_array($data)) {
                return false;
            }

            if (isset($data['file_id']) && (int) $data['file_id'] === (int) $fileId) {
                return true;
            }

            if ($fileUuid && isset($data['file_uuid']) && $data['file_uuid'] === $fileUuid) {
                return true;
            }

            return false;
        })->each->markAsRead();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isChiefDirector(): bool
    {
        return $this->role === 'chief_director';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrdinaryUser(): bool
    {
        return $this->role === 'user';
    }

    public function isPermanentSecretary(): bool
    {
        return $this->designation?->name === 'Permanent Secretary' || $this->email === 'permsec@filetrack.local';
    }

    /**
     * Get the user's display title for the UI.
     * E.g. "Director — Finance" for admin role, "Chief Director" for chief_director.
     */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->role === 'admin') {
            $deptName = $this->department?->name;
            return $deptName ? "Director — {$deptName}" : "Director";
        }

        if ($this->role === 'chief_director') {
            return "Chief Director";
        }

        if ($this->role === 'super_admin') {
            return "Super Admin";
        }

        return $this->designation?->name ?: 'Officer';
    }
}

