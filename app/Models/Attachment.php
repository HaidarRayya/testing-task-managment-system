<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'path',
        'mime_type',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->select('name', 'id', 'role_id');
    }
    public function attachmentable()
    {
        return $this->morphTo();
    }
}
