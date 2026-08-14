<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PublicFile extends Model
{
    protected $table = 'public_files';

    protected $fillable = [
        'uuid',
        'applicant_name',
        'email',
        'contact_number',
        'subject',
        'remarks',
        'attachment_path',
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

    /**
     * Check if the file physically exists on either storage disk.
     */
    public function getAttachmentExistsAttribute(): bool
    {
        if (! $this->attachment_path) {
            return false;
        }

        return Storage::disk('private')->exists($this->attachment_path)
            || Storage::disk('public')->exists($this->attachment_path);
    }

    /**
     * Return a 15-minute temporary signed download URL (UUID-based — no numeric ID exposed).
     */
    public function getSignedDownloadUrl(): string
    {
        if (! $this->uuid) {
            return '#';
        }

        return URL::temporarySignedRoute(
            'admin.public-files.download',
            now()->addMinutes(15),
            ['uuid' => $this->uuid]
        );
    }
}
