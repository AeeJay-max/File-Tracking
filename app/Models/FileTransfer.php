<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileTransfer extends Model
{
    protected $fillable = [
        'file_id',
        'sender_id',
        'receiver_id',
        'remarks',
        'transferred_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
