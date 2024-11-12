<?php

namespace App\Http\Controllers;


use App\Enums\UserPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\FillterTaskRequest;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Jobs\SendErrorMessage;
use App\Models\Task;
use App\Services\AuthService;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\Response;

class TaskController extends Controller
{
    protected $taskService;
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * get all tasks
     *
     * @param FillterTaskRequest $request 
     *
     * @return response  of the status of operation : tasks  
     */
    public function index(FillterTaskRequest $request)
    {
        AuthService::canDo(UserPermission::GET_TASK->value);

        $status = $request->input('status');
        $priority = $request->input('priority');
        $sort = $request->input('sort');
        $due_date = $request->input('due_date');
        $type = $request->input('type');
        $employee_id = $request->input('assigned_to');
        $tasks = $this->taskService->allTasks($status, $priority, $employee_id, $type, $due_date, $sort);
        return response()->json([
            'status' => 'success',
            'data' => [
                'tasks' =>  $tasks
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * create a new task
     *
     * @param StoreTaskRequest $request 
     *
     * @return response  of the status of operation : task and message
     */
    public function store(StoreTaskRequest $request)
    {
        AuthService::canDo(UserPermission::CREATE_TASK->value);

        $taskData = $request->validated();
        $task = $this->taskService->createTask($taskData);

        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */

    /**
     * show a specified task
     *
     * @param int $task_id 
     *
     * @return response  of the status of operation : task 
     */
    public function show(int $task_id)
    {
        AuthService::canDo(UserPermission::GET_TASK->value);

        $task = $this->taskService->oneTask($task_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * update a specified task
     * @param UpdateTaskRequest $request
     * @param int $task_id 
     *
     * @return response  of the status of operation : task and message 
     */
    public function update(UpdateTaskRequest $request, int $task_id)
    {
        AuthService::canDo(UserPermission::UPDATE_TASK->value);

        $taskData = $request->validated();

        $task = $this->taskService->updateTask($taskData, $task_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */

    /**
     * delete a specified task
     * @param int $task_id 
     *
     * @return response  of the status of operation 
     */
    public function destroy(int $task_id)
    {
        AuthService::canDo(UserPermission::DELETE_TASK->value);

        $this->taskService->deleteTask($task_id);
        return response()->json([
            'status' => 'success',
        ], 204);
    }
    /**
     * get all deleted task
     * @param FillterTaskRequest $request
     * @return response  of the status of operation : task 
     */
    public function allDeletedTasks(FillterTaskRequest $request)
    {
        AuthService::canDo(UserPermission::GET_DELELTED_TASK->value);
        $status = $request->input('status');
        $priority = $request->input('priority');
        $sort = $request->input('sort');
        $due_date = $request->input('due_date');
        $type = $request->input('type');
        $employee_id = $request->input('assigned_to');

        $tasks = $this->taskService->allDeletedTask($status, $priority, $employee_id, $type, $due_date, $sort);
        return response()->json([
            'status' => 'success',
            'data' => [
                'tasks' =>  $tasks
            ],
        ], 200);
    }

    /**
     * restore a specified task
     * @param int $task_id      
     * @return response  of the status of operation : task 
     */
    public function restoreTask($task_id)
    {
        AuthService::canDo(UserPermission::RESTORE_TASK->value);

        $task = $this->taskService->restoreTask($task_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], 200);
    }

    /**
     * assign an employee a specified task
     * @param int $task
     * @param AssignTaskRequest $request
     * @return response  of the status of operation : task and message 
     */
    public function assignEmployee(AssignTaskRequest $request, int $task_id)
    {
        AuthService::canDo(UserPermission::ASSIGN_TASK->value);

        $taskData = $request->validated();
        $task = $this->taskService->assignEmployee($taskData, $task_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], 200);
    }


    /**
     * change an employee of specified task
     * @param int $task
     * @param AssignTaskRequest $request
     * @return response  of the status of operation : task and message 
     */
    public function reassignEmployee(int $task_id, AssignTaskRequest $request)
    {
        AuthService::canDo(UserPermission::REASSIGN_TASK->value);

        $taskData = $request->validated();

        $task = $this->taskService->reassignEmployee($taskData, $task_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'task' =>  $task
            ],
        ], status: 200);
    }

    /**
     * end task
     * @param int $task
     * @return response  of the status of operation 
     */
    public function endTask(int $task_id)
    {
        AuthService::canDo(UserPermission::END_TASK->value);

        $this->taskService->endTask($task_id);
        return response()->json([
            'status' => 'success',
        ], status: 200);
    }
    /**
     * start work a task
     * @param int $task_id
     * @return response  of the status of operation 
     */
    public function startWorkTask(int $task_id)
    {
        AuthService::canDo(UserPermission::START_WORK_TASK->value);
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in start work task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }

        if (Auth::user()->id != $task->assigned_to) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };

        $this->taskService->startWorkTask($task);
        return response()->json([
            'status' => 'success',
        ], status: 200);
    }
    /**
     * end work a task
     * @param int $task_id
     * @return response  of the status of operation 
     */

    public function endWorkTask(int $task_id)
    {
        AuthService::canDo(UserPermission::END_WORK_TASK->value);

        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in start work task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if (Auth::user()->id != $task->assigned_to) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };
        //   Gate::allows('end-work-task', [Auth::user(), $task]);

        $this->taskService->endWorkTask($task);
        return response()->json([
            'status' => 'success',
        ], status: 200);
    }

    /**
     * start test a task
     * @param int $task
     * @return response  of the status of operation 
     */
    public function startTestTask(int $task_id)
    {
        AuthService::canDo(UserPermission::START_TEST_TASK->value);
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in start work task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if (Auth::user()->id != $task->assigned_to) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };
        //Gate::allows('start-test-task', [Auth::user(), $task]);

        $this->taskService->startTestTask($task);
        return response()->json([
            'status' => 'success',
        ], status: 200);
    }
    /**
     * end test a task
     * @param int $task
     * @return response  of the status of operation 
     */
    public function endTestTask(int $task_id)
    {
        AuthService::canDo(UserPermission::END_TEST_TASK->value);
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in start work task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if (Auth::user()->id != $task->assigned_to) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };
        // Gate::authorize('end-test-task', [Auth::user(), $task]);

        $this->taskService->endTestTask($task);
        return response()->json([
            'status' => 'success',
        ], status: 200);
    }
    /**
     * get daily reports tasks
     * @return response  of the status of operation :reporst
     */
    public function dailyReportsTasks()
    {
        AuthService::canDo(UserPermission::CREATE_REPORTS->value);

        $reports =  $this->taskService->dailyReportsTasks();
        return response()->json([
            'status' => 'success',
            'data' => [
                'reports' => $reports
            ]
        ], status: 200);
    }
}
