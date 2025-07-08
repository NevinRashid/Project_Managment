<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Models\Task;
use App\Traits\HandleServiceErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class TaskService
{
    use HandleServiceErrors;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all tasks from database
     *
     * @return array $arraydata
     */
    public function getAllTasks()
    {
        try{
            $user = Auth::user();

            // Check the user role if admin will return all tasks
            if($user->hasRole('admin') )  {
                $tasks = Cache::remember('all_tasks', 3600, function(){
                    return  Task::with(['project', 'assignee'])
                                    ->withCount(['comments','attachments'])
                                    ->paginate(10);
                });
                return $tasks;
            }

            // Check the user role if the team owner will return tasks for projects owned by his team.
            if($user->hasRole('team_owner'))  {
                $tasks = Cache::remember('team_owner_tasks_'.$user->id, 3600, function(){
                    return Task::whereHas('project.team', fn($q) => $q->where('owner_id',Auth::user()->id))->latest()->paginate(10);
                    });
                    return $tasks;
            }

            // Check the user role if the project manager will return tasks for the project of which he is a manager.
            if($user->hasRole('project_manager'))  {
                $tasks = Cache::remember('project_manager_tasks_'.$user->id, 3600, function(){
                    return Task::whereHas('project.workers', fn($q) => $q->where('user_id',Auth::user()->id)
                                                                        ->where('project_user.role','project_manager'))
                                ->latest()->paginate(10);
                    });
                    return $tasks;
            }

            // Check the user role if it is a mamber that returns all the tasks assigned to it.
            if($user->hasRole('member'))  {
                $tasks = Cache::remember('member_tasks_'.$user->id, 3600, function(){
                    return Task::with(['comments','attachments'])->where('assigned_user_id',Auth::user()->id)->paginate(10);
                    });
                    return $tasks;
            }

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get a single task with its relationships.
     *
     * @param  Task $task
     *
     * @return Task $task
     */
    public function showTask(Task $task)
    {
        try{
            return $task->load([
                    'project',
                    'assignee',
                    'comments',
                    'attachments'
                ])->loadCount(['comments','attachments']);

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new task to the database.
     *
     * @param array $arraydata
     *
     * @return Task $task
     */
    public function createTask(array $data)
    {
        try{
            $user = Auth::user();
            return DB::transaction(function () use ($data, $user) {

                $task = Task::create($data);

                //Create an internal notification when a new task is assigned to a user.
                event(new TaskAssigned($task));
                Cache::forget("project_manager_tasks_".$user->id);
                Cache::forget("member_tasks_".$user->id);
                Cache::forget("all_tasks");
                return $task->load(['project','assignee',
                            'comments', 'attachments'])
                            ->loadCount(['comments','attachments']);
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Update the specified task in the database.
     *
     * @param array $arraydata
     * @param Task $task
     *
     * @return Task $task
     */

    public function updateTask(array $data, Task $task){
        try{
            $user = Auth::user();
            $task->update(array_filter($data));
            Cache::forget("project_manager_tasks_".$user->id);
            Cache::forget("member_tasks_".$user->id);
            Cache::forget("all_tasks");
            return $task;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified task from the database.
     *
     * @param Task $task
     *
     * @return Task $task
     */

    public function deleteTask(Task $task){
        try{

            $task->project()->delete();
            $task->assignee()->delete();
            $task->comments()->delete();
            $task->attachments()->delete();
            return $task->delete();

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Assign a task to a specified user.
     *
     * @param array $arraydata
     * @param Task $task
     *
     * @return Task $task
     */
    public function assignTaskToUser(array $data , Task $task){
        try{
            if(isset($data['assigned_user_id'])){
                $task->update([
                    'assigned_user_id' => $data['assigned_user_id']
                ]);
                event(new TaskAssigned($task));
                return $task;
            }
        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

}
