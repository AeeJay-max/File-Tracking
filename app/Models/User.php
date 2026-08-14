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
     * Impersonation authorization hierarchy:
     *   Super Admin  → can impersonate any admin or user (not another super_admin)
     *   Admin        → can impersonate users in their own department only
     *   User         → cannot impersonate anyone
     */
    public function canImpersonate(User $target): bool
    {
        // Cannot impersonate yourself
        if ($this->id === $target->id) {
            return false;
        }

        if ($this->role === 'super_admin') {
            // Super admin can impersonate admins and users, never another super_admin
            return in_array($target->role, ['admin', 'user'], true);
        }

        if ($this->role === 'admin') {
            // Admin can only impersonate users in the same department
            return $target->role === 'user'
                && (int) $target->department_id === (int) $this->department_id;
        }

        return false;
    }
}
