<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDependencies extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_id',
        'task_dependency_id'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}