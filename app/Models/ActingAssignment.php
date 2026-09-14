<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActingAssignment extends Model
{
    protected $fillable = [
        'uuid',
        'file_id',
        'assigned_by',
        'assigned_to',
        'authority_type',
        'instructions',
        'required_action',
        'next_step',
        'return_destination',
        'status',
        'assigned_at',
        'acted_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'acted_at'     => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->assigned_at)) {
                $model->assigned_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForFile($query, int $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
