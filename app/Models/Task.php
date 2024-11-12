<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskWorkStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'priority',
        'type',
        'due_date',
        'assigned_to',
    ];
    protected $guarded = [
        'work_status',
        'status',
    ];
    protected $casts = [
        'due_date'   =>  "datetime:Y-m-d",
    ];

    protected $attributes = [
        'status' => TaskStatus::OPEN->value,
        'work_status' => TaskWorkStatus::IDLE->value,
    ];

    protected $perPage = 10;

    public function employee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->select('id', 'description', 'user_id');
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable')->select('id', 'name', 'path', 'user_id');
    }

    public function taskStatusUpdate()
    {
        return $this->hasMany(TaskStatusUpdate::class);
    }
    public function taskDependencies()
    {
        return $this->hasMany(TaskDependencies::class, 'task_dependency_id');
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    /**
     *   get all tasks of worked by employee_id
     * @param  Builder $query  
     * @return Builder query  
     */
    public function scopeMyTask(Builder $query, $employee_id)
    {
        return $query->where('assigned_to', '=', $employee_id);
    }
    /**
     *   search  a tasks by status
     * @param  Builder $query  
     * @param  string $status  
     * @return Builder $query  
     */

    public function scopeByStatus(Builder $query, $status)
    {
        if ($status)
            return $query->where('status', '=', $status);
        else
            return $query;
    }
    /**
     *   search  a tasks by priority
     * @param  Builder $query  
     * @param  string $priority  
     * @return Builder $query  
     */
    public function scopeByPriority(Builder $query, $priority)
    {
        if ($priority)
            return $query->where('priority', '=', $priority);
        else
            return $query;
    }
    /**
     *   search  a tasks by type
     * @param  Builder $query  
     * @param  string $type  
     * @return Builder $query  
     */
    public function scopeByType(Builder $query, $type)
    {
        if ($type)
            return $query->where('type', '=', $type);
        else
            return $query;
    }
    /**
     *   search  a tasks by due_date
     * @param  Builder $query  
     * @param  string $due_date  
     * @return Builder $query  
     */
    public function scopeByDueDate(Builder $query, $due_date)
    {
        if ($due_date)
            return $query->where('due_date', '=', $due_date);
        else
            return $query;
    }
    /**
     *   sort  a tasks 
     * @param  Builder $query  
     * @param  string $sort  
     * @return Builder $query  
     */

    public function scopeSortDueDate(Builder $query, $sort)
    {
        if ($sort)
            return $query->orderBy('due_date', $sort);
        else
            return $query->orderByDesc('due_date');
    }
    /**
     *   search  a tasks worked by  the  specific employee
     * @param  Builder $query  
     * @return Builder query  
     */
    public function scopeByEmployee(Builder $query, $employee_id)
    {
        if ($employee_id)
            return $query->where('assigned_to', '=', $employee_id);
        else
            return $query;
    }
}
