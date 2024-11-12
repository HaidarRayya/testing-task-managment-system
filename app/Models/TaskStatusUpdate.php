<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'status',
        'assigned_to',
        'work_status'
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}