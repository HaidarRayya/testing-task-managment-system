<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\TaskWorkStatus;
use App\Enums\UserRole;
use App\Http\Resources\ReportResource;
use App\Http\Resources\TaskResource;
use App\Jobs\CheckTaskDependency;
use App\Jobs\CreateTaskDependencies;
use App\Jobs\CreateTaskStatusUpdate;
use App\Jobs\SendErrorMessage;
use App\Models\Report;
use App\Models\Task;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * get all  tasks
     * @param string $status 
     * @param string $priority 
     * @param int $employee_id
     * @param string $type
     * @param date $due_date
     * @param string $sort
     * @return   TaskResource $tasks
     */

    public function allTasks($status, $priority, $employee_id, $type, $due_date, $sort)
    {
        try {
            if (AuthService::user_role(Auth::user()->id) == UserRole::ADMIN->value) {
                $tasks = Task::query()->with(['employee' => function ($q) {
                    $q->select('name', 'role_id');
                }])->byPriority($priority)
                    ->byStatus($status)
                    ->byType($type)
                    ->byEmployee($employee_id)
                    ->byDueDate($due_date)
                    ->sortDueDate($sort)
                    ->get();
            } else {
                $tasks =  Task::myTask(Auth::user()->id)
                    ->byPriority($priority)
                    ->byStatus($status)
                    ->byDueDate($due_date)
                    ->sortDueDate($sort)
                    ->get();
            }
            $tasks = TaskResource::collection($tasks);
            return $tasks;
        } catch (Exception $e) {
            Log::error("error in get all tasks" . $e->getMessage());
            SendErrorMessage::dispatch("error in  get all tasks" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in get all tasks" . $e->getMessage());
            SendErrorMessage::dispatch("error in  get all tasks" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * create  a  new task
     * @param array $data 
     * @return   TaskResource $task
     */
    public function createTask(array $data)
    {
        try {
            if ($data['depends_on'] == null) {
                $task = Task::create($data);
            } else {
                $task = Task::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'priority' => $data['priority'],
                    'type' => $data['type'],
                    'due_date' => $data['due_date'],
                    'assigned_to' => $data['assigned_to']  ?? null,
                ]);
                $task->status = TaskStatus::BLOCKED->value;
                $task->save();

                CreateTaskDependencies::dispatch($task, $data['depends_on']);
            }
            CreateTaskStatusUpdate::dispatch($task);

            $task = TaskResource::make($task);
            Cache::forget('tasks');

            return $task;
        } catch (Exception $e) {
            Log::error("error in create task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  create task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * show  a  task
     * @param int $task_id
     * @return  TaskResource $task
     */
    public function oneTask($task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  get task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
        try {
            $task = Cache::remember('task_' . $task_id, 600, function () use ($task) {
                return $task->load('employee.role');
            });
            $task = TaskResource::make($task);
            return $task;
        } catch (Exception $e) {
            Log::error("error in get a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  get task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in get a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  get task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * update  a  task
     * @param array $data 
     * @param int $task_id 
     * @return  TaskResource $task
     */
    public function updateTask($data, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in update a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  update task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $task->update($data);
            if (Cache::has('user_' . $task_id)) {
                $task = Cache::remember('task_' . $task_id, 600, function () use ($task) {
                    return $task;
                });
            }
            Cache::forget('tasks');

            $task = TaskResource::make($task);
            return $task;
        } catch (Exception $e) {
            Log::error("error in update a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in  update task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in update a task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }

    /**
     * delete  a task
     * @param int $task_id
     */
    public function deleteTask($task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  delete a task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $task->delete();
            Cache::forget('task_' . $task_id);
            Cache::forget('tasks');
        } catch (Exception $e) {
            Log::error("error in  delete a task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * get all deleted tasks
     * @param string $status 
     * @param string $priority 
     * @param int $employee_id
     * @param string $type
     * @param date $due_date
     * @param string $sort     
     * @return TaskResource $tasks
     */
    public function allDeletedTask($status, $priority, $employee_id, $type, $due_date, $sort)
    {
        try {
            $tasks =  Task::onlyTrashed()
                ->byPriority($priority)
                ->byStatus($status)
                ->byType($type)
                ->byEmployee($employee_id)
                ->byDueDate($due_date)
                ->sortDueDate($sort)
                ->get();
            $tasks = TaskResource::collection($tasks);
            return $tasks;
        } catch (Exception $e) {
            Log::error("error in  get all deleted tasks"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  all deleted tasks" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  get all deleted tasks"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  all deleted tasks" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * restore a task
     * @param int $task_id      
     * @return TaskResource $task
     */
    public function restoreTask($task_id)
    {
        try {
            $task = Task::withTrashed()->findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in restore a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in restore task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $task->restore();
            return TaskResource::make($task->load('employee.role'));
        } catch (Exception $e) {
            Log::error("error in restore a task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in restore task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in restore a task" . $e->getMessage());
            SendErrorMessage::dispatch("error in restore task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }

    /**
     * assign employee to a task
     * @param int $task_id     
     * @return TaskResource $task
     */
    public function assignEmployee($taskData, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in assign employee"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }

        if ($task->assigned_to != null) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لقد قمت باضافة موظف سابقا على هذه المهمة",
                ],
                422
            ));
        }
        if (($task->status == TaskStatus::OPEN->value || $task->status == TaskStatus::BLOCKED->value ) && (AuthService::user_role($taskData['employee_id']) == UserRole::TESTER->value)) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => " لا يمكنك تعيين مختبر على هذه المهمة لم يتم البدء عليها بعد",
                ],
                422
            ));
        }
        try {
            $task->assigned_to = $taskData['employee_id'];
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);

            $task->load('employee.role');
            return TaskResource::make($task);
        } catch (Exception $e) {
            Log::error("error in assign employee"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * restore a task
     * @param int $task_id      
     * @return TaskResource $task
     */
    public function reassignEmployee($taskData, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in assign employee"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $task->assigned_to = $taskData['employee_id'];
            $task->work_status = TaskWorkStatus::IDLE->value;
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);
            $task->load('employee.role');
            return TaskResource::make($task);
        } catch (Exception $e) {
            Log::error("error in reassign employee"  . $e->getMessage());
            SendErrorMessage::dispatch("error in reassign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * end a task
     * @param int $task_id      
     */
    public function endTask($task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in end task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($task->work_status != TaskWorkStatus::FINISHED->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك انهاء المهمة لا يزال العمل يتم العمل بها",
                ],
                422
            ));
        }
        try {
            $task->status = TaskStatus::COMPLETED->value;
            $task->save();

            CheckTaskDependency::dispatch($task);

            CreateTaskStatusUpdate::dispatch($task);

            return $task;
        } catch (Exception $e) {
            Log::error("error in end task" . $e->getMessage());
            SendErrorMessage::dispatch("error in end task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in end task" . $e->getMessage());
            SendErrorMessage::dispatch("error in end task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
    }
    /**
     * start work task
     * @param Task $task     
     */
    public function startWorkTask($task)
    {
        if ($task->work_status != TaskWorkStatus::IDLE->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك يدءالعمل على هذه المهمة لقد تمت معالجتها سابقا",
                ],
                422
            ));
        }
        try {
            if ($task->status ==  TaskStatus::OPEN->value) {
                $task->status =  TaskStatus::IN_PROGRESS->value;
            }
            $task->work_status = TaskWorkStatus::ACTIVE->value;
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);
        } catch (Exception $e) {
            Log::error("error in start work task" . $e->getMessage());
            SendErrorMessage::dispatch("error in start work task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * end work task
     * @param Task $task      
     */
    public function endWorkTask($task)
    {

        if ($task->work_status != TaskWorkStatus::ACTIVE->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك انهاء العمل على هذه المهمة لقد تمت معالجتها سابقا",
                ],
                422
            ));
        }
        try {
            $task->work_status = TaskWorkStatus::FINISHED->value;
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);
        } catch (Exception $e) {
            Log::error("error in end work task" . $e->getMessage());
            SendErrorMessage::dispatch("error in end work task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * start test task
     * @param Task $task    
     */
    public function startTestTask($task)
    {
        if ($task->work_status != TaskWorkStatus::IDLE->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك يدء اختبار هذه المهمة لقد تمت معالجتها سابقا",
                ],
                422
            ));
        }
        try {
            $task->work_status = TaskWorkStatus::ACTIVE->value;
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);

            return $task;
        } catch (Exception $e) {
            Log::error("error in start test task" . $e->getMessage());
            SendErrorMessage::dispatch("error in start test task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * end test task
     * @param Task $task     
     */
    public function endTestTask($task)
    {
        if ($task->work_status != TaskWorkStatus::ACTIVE->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك انهاء اختبار هذه المهمة لقد تمت معالجتها سابقا",
                ],
                422
            ));
        }
        try {
            $task->work_status = TaskWorkStatus::FINISHED->value;
            $task->save();
            CreateTaskStatusUpdate::dispatch($task);
        } catch (Exception $e) {
            Log::error("error in end test task" . $e->getMessage());
            SendErrorMessage::dispatch("error in end test task" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * get daily reports tasks
     * @return ReportResource $reports      
     */
    public function dailyReportsTasks()
    {
        $reports = Report::whereDate('created_at', Carbon::today())
            ->with('task')->get();
        $reports = ReportResource::collection($reports);
        return $reports;
    }
}