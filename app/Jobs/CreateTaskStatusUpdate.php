<?php

namespace App\Jobs;

use App\Models\TaskStatusUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTaskStatusUpdate implements ShouldQueue
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
        TaskStatusUpdate::create([
            'task_id' => $this->task->id,
            'status' => $this->task->status,
            'assigned_to' => $this->task->assigned_to,
            'work_status' => $this->task->work_status
        ]);
    }
}