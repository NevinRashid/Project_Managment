<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class TaskController extends Controller
{
    use AuthorizesRequests;
    /**
     * This property is used to handle various operations related to tasks,
     * such as creating, updating, ...
     *
     * @var TaskService
     */
    protected $taskService;

    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view tasks', only:['index']),
            new Middleware('role:project_manager|member', only:['store', 'update', 'destroy']),
        ];
    }

    /**
     * Constructor for the TaskController class.
     *
     * Initializes the $taskService property via dependency injection.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * This method return all tasks from database.
     */
    public function index()
    {
        return $this->success($this->taskService->getAllTasks());
    }

    /**
     * Add a new task in the database using the taskService via the createTask method
     * passes the validated request data to createTask.
     *
     * @param StoreTaskRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        return $this->success(
            $this->taskService->createTask($request->validated())
                    , 'Task has been created successfully'
                    , 201);
    }

    /**
     * Get Task from database.
     * using the taskService via the showTask method
     *
     * @param Task $task
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $this->authorize('view',$task);
        return $this->success($this->taskService->showTask($task));
    }

    /**
     * Update a task in the database using the taskService via the updateTask method.
     * passes the validated request data to updateTask.
     *
     * @param UpdateTaskRequest $request
     *
     * @param Task $task
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update',$task);
        return $this->success($this->taskService->updateTask($request->validated(), $task)
                            ,'updated successfuly');
    }

    /**
     * Remove the specified task from database.
     *
     * @param Task $task
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete',$task);
        $this->taskService->deleteTask($task);
        return $this->success(null,'Deleted successfuly');
    }

    /**
     *Assign a task to a user.
     *
     * @param Task $task
     *
     * @return \Illuminate\Http\Response
     */
    public function assign(UpdateTaskRequest $request,Task $task)
    {
        $this->authorize('assign',$task);
        return $this->success($this->taskService->assignTaskToUser($request->validated(), $task)
                            ,'assigned successfuly');
    }
}
