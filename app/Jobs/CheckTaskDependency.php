<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependencies;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTaskDependency implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    /**
     * Create a new job instance.
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $taskDependencies = $this->task->load('TaskDependencies')->TaskDependencies;
        foreach ($taskDependencies as $t) {
            $dependencies = TaskDependencies::where('task_id', '=', $t->task_id)->select('task_dependency_id')->get();
            $done = true;
            foreach ($dependencies as $d) {
                $task_d = Task::where('id', '=', $d->task_dependency_id)->select('status')->first();
                if (!($task_d->status == TaskStatus::COMPLETED->value)) {
                    $done = false;
                    break;
                }
            }
            if ($done) {
                $task = Task::find($t->task_id);
                $task->status = TaskStatus::OPEN->value;
                $task->save();
                
                CreateTaskStatusUpdate::dispatch($task);
            }
        }
    }
}