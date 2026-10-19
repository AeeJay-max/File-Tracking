<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FileAttachment extends Model
{
    protected $fillable = [
        'uuid',
        'file_record_id',
        'user_id',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'version',
        'version_label',
        'is_latest',
        'notes',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
        'version' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function fileRecord()
    {
        return $this->belongsTo(FileRecord::class, 'file_record_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isOriginal(): bool
    {
        return (int) $this->version === 1;
    }

    public function isUpdated(): bool
    {
        return (int) $this->version > 1;
    }
}
