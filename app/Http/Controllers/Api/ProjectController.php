<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\RemoveProjectWorkersRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\StoreProjectWorkersRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * This property is used to handle various operations related to projects,
     * such as creating, updating, ...
     *
     * @var ProjectService
     */
    protected $projectService;

    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin|team_owner|project_manager', only:['index','store', 'update', 'destroy','addWorkers','removeWorkers']),
            new Middleware('permission:change project manager', only:['changeManager']),
            new Middleware('permission:view completed project', only:['getCompletedProjects']),
        ];
    }

    /**
     * Constructor for the ProjectController class.
     *
     * Initializes the $projectService property via dependency injection.
     *
     * @param ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * This method return all projects from database.
     */
    public function index()
    {
        return $this->success($this->projectService->getAllProjects());
    }

    /**
     * Add a new project in the database using the projectService via the createProject method
     * passes the validated request data to createProject.
     *
     * @param StoreProjectRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        return $this->success(
            $this->projectService->createProject($request->validated())
                    , 'Project has been created successfully'
                    , 201);
    }

    /**
     * Get project from database.
     * using the projectService via the showProject method
     *
     * @param Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $this->authorize('view',$project);
        return $this->success($this->projectService->showProject($project));
    }

    /**
     * Update a project in the database using the projectService via the updateProject method.
     * passes the validated request data to updateProject.
     *
     * @param UpdateProjectRequest $request
     *
     * @param Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update',$project);
            return $this->success($this->projectService->updateProject($request->validated(), $project)
                                ,'updated successfuly');
    }

    /**
     * Remove the specified project from database.
     *
     * @param Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete',$project);
        $this->projectService->deleteProject($project);
        return $this->success(null,'Deleted successfuly');
    }

    
    /**
     * Change the Project manager for the project
     * 
     * @param Project $project
     * @param string $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function changeManager(Project $project , string $id)
    {
        $this->authorize('change',$project);
        return $this->success($this->projectService->changeProjectManager($project , $id)
                                ,'Changed successfuly');
    }

    /**
     * Adding new workers to the project
     * 
     * @param Project $project
     * @param array $workerIds
     * 
     * @return \Illuminate\Http\Response
     */
    public function addWorkers(StoreProjectWorkersRequest $request, Project $project)
    {
        $this->authorize('add',$project);
        return $this->success($this->projectService->addWorkersToProject($request->validated(),$project)
                                , 'added successfuly');
    }

    /**
     * Remove workers from the project
     * 
     * @param Project $project
     * @param array $workerIds
     * 
     * @return \Illuminate\Http\Response
     */
    public function removeWorkers(RemoveProjectWorkersRequest $request, Project $project)
    {
        $this->authorize('remove',$project);
        return $this->success($this->projectService->removeWorkersFromProject($request->validated(),$project)
                                ,'Removed successfuly');
    }

    /**
     * Get all completed projects
     *    
     * @return \Illuminate\Http\Response
     */
    public function getCompletedProjects()
    {
        return $this->success($this->projectService->getAllCompleted());
    }

    /**
     * Get counts of all completed tasks in project.
     *   
     * @return \Illuminate\Http\Response
     */
    public function getCompletedTasksCounts()
    {
        return $this->success($this->projectService->getNumCompletedTasks());
    }
    
}
