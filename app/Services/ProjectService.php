<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Traits\HandleServiceErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class ProjectService
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
     * Get all projects from database
     *
     * @return array $arraydata
     */
    public function getAllProjects()
    {
        try{
            $user = Auth::user();

            // Check the user role if admin will return all projects
            if($user->hasRole('admin'))  {
                $projects = Cache::remember('all_projects', 3600, function(){
                    return  Project::with(['creator', 'team','tasks','workers'
                                            ,'comments', 'attachments'])
                                    ->withCount(['workers','tasks'])
                                    ->get();
                });
                    return $projects;
            }

            // Check the user role if the team owner will only return projects that he is the owner of.
            if($user->hasRole('team_owner'))  {
                $projects = Cache::remember('team_owner_projects_'.$user->id, 3600, function(){
                    $teamIds = Team::OwnTeams()->pluck('id');
                    if($teamIds->isEmpty())
                        return $this->error("Unfortunately, you don't have any team",500);

                    return Project::where('team_id', $teamIds)->with(['creator', 'team','tasks','workers'
                                                                    ,'comments', 'attachments'])
                                                            ->withCount(['workers','tasks'])
                                                            ->get();
                    });
                    return $projects;
            }

            //Check the role if it is a Project Manager will return only the projects that it manages.
            if(Auth::check() && $user->hasRole('project_manager')){
                $projects = Cache::remember('project_manager_projects_'.$user->id, 3600, function() use($user){
                    return $user->ownedProjects->load(['creator', 'team','tasks','workers'
                                                    ,'comments', 'attachments'])
                                                ->loadCount(['workers','tasks']);
                    });
                    return $projects;
            }

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get a single project from database with its relationships.
     *
     * @param  Project $project
     *
     * @return Project $project
     */
    public function showProject(Project $project)
    {
        try{
            {
                return $project->load([
                    'creator',
                    'team',
                    'tasks',
                    'workers',
                    'comments',
                    'attachments'
                ])->loadCount(['tasks' ,'workers','comments','attachments']);
            }

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new project to the database.
     *
     * @param array $arraydata
     *
     * @return Project $project
     */
    public function createProject(array $data )
    {
        try{
            $user = Auth::user();

            return DB::transaction(function () use ($data, $user) {
                // create project
                $data['created_by_user_id']=$user->id;
                $project = Project::create($data);

                /*Assigning the role of member to all workers,
                only the creator takes the role of project_manager in pivot table.
                */
                $creatorId = $user->id;
                $workerIds = $data['worker_ids'];
                $pivotData = [];
                foreach ($workerIds as $workerId) {
                    $pivotData[$workerId] = [
                    'role' => $workerId == $creatorId ? 'project_manager' : 'member',
                    ];
                    $member = User::find($workerId);
                    $member->assignRole('member');
                }
                $project->workers()->attach($pivotData);

                // Ensure that if the user is an admin or team owner
                // and does not previously have the project manager role, then the role is added to him.
                if(($user->hasRole('admin')|| $user->hasRole('team_owner'))&& !$user->hasRole('project_manager')){
                    $user->assignRole('project_manager');
                }

                //Ensure that attachments are available
                if ($data['attachments']) {
                    foreach($data['attachments'] as $file){
                        $file_name = Str::random(5).$file->getClientOriginalName();
                        $file_size = $file->getSize();
                        $mime_type = $file->getMimeType();
                        $path = $file->storeAs('files_'.$project->name,$file_name, 'public');
                        $project->attachments()->create([
                        'path'      => $path,
                        'disk'      => 'public',
                        'file_name' => $file_name,
                        'file_size' => $file_size,
                        'mime_type' => $mime_type
                        ]);
                    }
                }

                Cache::forget("team_owner_projects_".$user->id);
                Cache::forget("project_manager_projects_".$user->id);
                Cache::forget("all_projects");
                return $project->load(['creator', 'team','tasks','workers'
                                        ,'comments', 'attachments'])
                                ->loadCount(['workers','tasks']);
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Change the project manager for the project
     *
     * @param Project $project
     * @param string $newProjectManagerId
     *
     * @return \Illuminate\Http\Response
     */
    public function changeProjectManager(Project $project, int $newManagerId)
    {
        try{

        $user = Auth::user();
        $preManager = $project->project_manager;
        //return $preManager;
        $queryWorkerInProject = $project->workers()->where('user_id',$newManagerId);

        // Verify that the new project manager is a worker on this project
        if(!$queryWorkerInProject->exists()){
            return $this->error('The new project manager is not a worker on this project',403);
        }

        // Verify that the new project manager is is already the project manager.
        if($newManagerId === $preManager->id){
            return $this->error('The user '.$newManagerId.' is already the project manager currently',422);
        }

        return DB::transaction(function () use ($project, $newManagerId, $preManager, $user, $queryWorkerInProject) {

            //Get the new manager from the worker relationship.
            $newManager = $queryWorkerInProject->first();

            //1. Update old manager's role in the pivot table .
            $project->workers()->updateExistingPivot($preManager->id,['role' => 'member']);
            //$preManager->removeRole('project_manager');

            //2. assign the role "project_manager" to the new manager
            if(!$newManager->hasRole('project_manager')){
                $newManager->assignRole('project_manager');
            }

            //3.Update new manager's role in the pivot table
            $project->workers()->updateExistingPivot($newManager->id,['role' => 'project_manager']);

            Cache::forget("team_owner_projects_".$newManagerId);
            Cache::forget("team_owner_projects_".$preManager->id);
            Cache::forget("project_manager_projects_".$newManagerId);
            Cache::forget("project_manager_projects_".$preManager->id);
            Cache::forget("project_manager_projects_".$user->id);
            Cache::forget("all_projects");

            return $project;
        });
        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Update the specified project in the database.
     *
     * @param array $arraydata
     * @param Project $project
     *
     * @return Project $project
     */

    public function updateProject(array $data, Project $project){
        try{

            $user = Auth::user();
            return DB::transaction(function () use ($project, $data, $user) {
            $project->update(array_filter($data));

            //Assigning the role of member to all workers,
            if(!empty($data['worker_ids'])){
                $workerIds = $data['worker_ids'];
                $pivotData = [];
                foreach ($workerIds as $workerId) {
                    $pivotData[$workerId] = [
                    'role' => 'member',
                    ];
                $member = User::find($workerId);
                $member->assignRole('member');
                }
                $project->workers()->syncWithoutDetaching($data['worker_ids']);
            }

            Cache::forget("team_owner_projects_".$user->id);
            Cache::forget("project_manager_projects_".$user->id);
            Cache::forget("all_projects");

            return $project->loadCount(['workers','tasks']);
        });
        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified Project from the database.
     *
     * @param Project $project
     *
     * @return Project $project
     */

    public function deleteProject(Project $project){
        try{
            return DB::transaction(function () use ($project) {
                $project->workers()->detach();
                $project->tasks()->delete();
                $project->comments()->delete();
                $project->attachments()->delete();
                return $project->delete();
            });

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Adding new workers to the project.
     *
     * @param array $workerIds
     * @param Project $project
     *
     * @return Project $project
     */
    public function addWorkersToProject( array $data, Project $project){
        try{
            $user = Auth::user();
            return DB::transaction(function () use ($project, $data, $user) {

            $workerIds = $data['worker_ids'];
            $pivotData = [];
            //Assigning the role of member to all workers,
            foreach ($workerIds as $workerId) {
                $pivotData[$workerId] = [
                    'role' =>'member'
                    ];
                $member = User::find($workerId);
                $member->assignRole('member');
            }
                $project->workers()->syncWithoutDetaching($data['worker_ids']);

            Cache::forget("team_owner_projects_".$user->id);
            Cache::forget("project_manager_projects_".$user->id);
            Cache::forget("all_projects");

            return $project->load('workers')->loadCount(['workers']);
        });

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }


    /**
     * Removing workers from the project,
     * Prevent the dismissal of the project manager
     * and ensure that all workers are team members.
     *
     * @param array $workerIdsdata
     * @param Project $project
     *
     * @return Project $project
     */
    public function removeWorkersFromProject( array $data, Project $project){
        try{
            $user=Auth::user();
            // Prevent the dismissal of the project manager
            if(in_array($project->created_by_user_id , $data['worker_ids']))
            {
                return $this->error('You can not remove the project manager',403);
            }

            //Remove the role of member from all worker_ids,
            foreach($data['worker_ids'] as $workerId){
                $member = User::find($workerId);
                $member->removeRole('member');
            }
            $project->workers()->detach($data['worker_ids']);
            Cache::forget("team_owner_projects_".$user->id);
            Cache::forget("project_manager_projects_".$user->id);
            Cache::forget("all_projects");
            return $project->load('workers')
                        ->loadCount('workers');

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get all completed projects from database.
     *
     * @return array $arraydata
     */
    public function getAllCompleted(){
        try{
            $completedProjects = Cache::remember('all_completed_projects', 3600, function(){
                Project::CompletedProjects()->get();
            });
            return $completedProjects;

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get number of completed tasks from database.
     *
     * @return array $arraydata
     */
    public function getNumCompletedTasks(){
        try{
            $numOfTasks = Cache::remember('number_completed_tasks', 3600, function(){
                Project::withCount('completedTasks')->get();
            });
            return $numOfTasks;

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }
}
