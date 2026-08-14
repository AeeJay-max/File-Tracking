<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileMovement extends Model
{
    protected $fillable = [
        'file_id',
        'from_user',
        'to_user',
        'from_department',
        'to_department',
        'action',
        'remarks',
    ];

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user');
    }

    public function fromDept()
    {
        return $this->belongsTo(Department::class, 'from_department');
    }

    public function toDept()
    {
        return $this->belongsTo(Department::class, 'to_department');
    }
}
