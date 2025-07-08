<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\RemoveTeamMembersRequest;
use App\Http\Requests\Team\StoreTeamMembersRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * This property is used to handle various operations related to teams,
     * such as creating, updating, changeOwnerTeam.
     *
     * @var TeamService
     */
    protected $teamService;

    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware ('role:admin|team_owner', 
                            only:['index','store', 'update', 'destroy','addMembers','removeMembers','changeOwner'
                            ]),
        ];
    }
    /**
     * Constructor for the TeamController class.
     *
     * Initializes the $teamService property via dependency injection.
     *
     * @param TeamService $teamService
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * This method return all teams from database.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success($this->teamService->getAllTeams());
    }

    /**
     * Add a new Team in the database using the teamService via the createTeam method
     * passes the validated request data to createTeam.
     *
     * @param StoreTeamRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeamRequest $request)
    {
        return $this->success(
            $this->teamService->createTeam($request->validated())
                    , 'Team has been created successfully'
                    , 201);
    }

    /**
     * Get team from database.
     * using the teamService via the showTeam method
     *
     * @param Team $team
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        return $this->success($this->teamService->showTeam($team));
    }

    /**
     * Update a team in the database using the teamService via the updateTeam method.
     * passes the validated request data to updateTeam.
     *
     * @param UpdateTeamRequest $request
     *
     * @param Team $team
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        return $this->success($this->teamService->updateTeam($request->validated(), $team)
                            ,'updated successfuly');
    }

    /**
     * Remove the specified team from database.
     *
     * @param Team $team
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team)
    {
        $this->teamService->deleteTeam($team);
        return $this->success(null,'Deleted successfuly');

    }

    /**
     * Change the Ownership for the team
     * 
     * @param Team $team
     * @param string $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function changeOwner(Team $team , string $id)
    {
        return $this->success($this->teamService->transferOwnership($team , $id)
                                ,'transfered Ownership successfuly');
    }
    
    /**
     * Adding new members to the team
     * 
     * @param Team $team
     * @param array $memberIds
     * 
     * @return \Illuminate\Http\Response
     */
    public function addMembers(StoreTeamMembersRequest $request, Team $team)
    {
        return $this->success($this->teamService->addMembersToTeam($request->validated(),$team)
                                , 'added successfuly');
    }

    /**
     * Remove members from the team
     * 
     * @param Team $team
     * @param array $memberIds
     * 
     * @return \Illuminate\Http\Response
     */
    public function removeMembers(RemoveTeamMembersRequest $request, Team $team)
    {
        return $this->success($this->teamService->removeMembersFromTeam($request->validated(),$team)
                                ,'Removed successfuly');
    }
}
