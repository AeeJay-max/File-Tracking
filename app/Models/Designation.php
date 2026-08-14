<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Designation extends Model
{
    protected $fillable = ['uuid', 'department_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
