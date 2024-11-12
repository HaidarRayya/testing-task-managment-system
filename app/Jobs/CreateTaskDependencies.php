<?php

namespace App\Jobs;

use App\Models\TaskDependencies;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTaskDependencies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected $task_dependencies;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $task_dependencies)
    {
        $this->task = $task;
        $this->task_dependencies = $task_dependencies;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->task_dependencies  as $t) {
            TaskDependencies::create([
                'task_id' => $this->task->id,
                'task_dependency_id' => $t
            ]);
        }
    }
}