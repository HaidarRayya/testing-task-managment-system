<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Enums\TaskWorkStatus;
use App\Models\Report;
use App\Models\Task;
use App\Models\TaskStatusUpdate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateTasksReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-tasks-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::all();
        foreach ($tasks  as $task) {
            if ($task->status == TaskStatus::BLOCKED->value) {
                Report::create([
                    'description' => "لم يتم البدء في هذه المهمة بعد",
                    'task_id' => $task->id
                ]);
            } else if ($task->status == TaskStatus::COMPLETED->value) {
                Report::create([
                    'description' => "تم الانتهاء من العمل على الممهمة $task->name",
                    'task_id' => $task->id
                ]);
            } else {
                $taskStatus = TaskStatusUpdate::where('task_id', '=', $task->id)->with('employee.role')->get();
                $text = "";
                foreach ($taskStatus as $ts) {
                    if ($ts->status == TaskStatus::OPEN->value && $ts->work_status == TaskWorkStatus::IDLE->value  && $ts->assigned_to == null) {
                        $text .= ("المهمة $task->name متاحة و لم يتم بدء العمل بها" . "\n");
                    } else if ($ts->status == TaskStatus::OPEN->value  && $ts->work_status == TaskWorkStatus::IDLE->value && $ts->assigned_to != null) {
                        $employee = $ts->employee;
                        $role = $employee->role->name;
                        $text .= (" قمتٍ بتعيين الموظف $employee->name بدور $role في المهمة $task->name" . "\n");
                    } else if ($ts->status ==  TaskStatus::IN_PROGRESS->value) {
                        if ($ts->work_status == TaskWorkStatus::ACTIVE->value) {
                            $employee = $ts->employee;
                            $date = Carbon::create($ts->created_at)->format('Y-m-d');
                            $text .= ("قام الموظف $employee->name ببدء العمل على المهمة  $task->name  بتاريخ $date  " . "\n");
                        } else if ($ts->work_status == TaskWorkStatus::FINISHED->value) {
                            $employee = $ts->employee;
                            $date = Carbon::create($ts->created_at)->format('Y-m-d');
                            $text .= ("قام الموظف $employee->name بانهاء العمل على المهمة  $task->name  بتاريخ $date  " . "\n");
                        }
                    } else if ($ts->status ==  TaskStatus::COMPLETED->value) {
                        $date = Carbon::create($ts->created_at)->format('Y-m-d');
                        $text .=  ("تم الانتهاء من العمل على الممهمة $task->name بتاريخ $date" . "\n");
                    }
                }
                Report::create([
                    'description' => $text,
                    'task_id' => $task->id
                ]);
            }
        }
    }
}
