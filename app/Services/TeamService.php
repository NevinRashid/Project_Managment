<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Traits\HandleServiceErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TeamService
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
     * Get all teams according to the user
     * If it was a admin, return all teams.
     * If it was a team owner, only return own teams.
     *
     * @return array $teamdata
     */
    public function getAllTeams()
    {
        try{
            $user=Auth::user();

            if($user->hasRole('admin'))  {
                $teams = Cache::remember('all_teams', 3600, function(){
                    return Team::with('owner')->withCount(['members','projects'])->get();
                });
                return $teams;
            }

            if(Auth::check() && $user->hasRole('team_owner'))
            {
                $teams= Cache::remember('owner_teams_'.$user->id, 3600, function(){
                    return Team::OwnTeams()->with(['owner'])->withCount(['members','projects'])->get();
                });
                    return $teams;
            }

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new team to the database.
     *
     * @param array $teamdata
     *
     * @return Team $team
     */
    public function createTeam(array $data)
    {
        try{
            $user = Auth::user();

            return DB::transaction(function () use ($data, $user) {
            // create team 
            $team = Team::create($data);
            $team->members()->attach($data['member_ids']);

            if($user->hasRole('admin') && !$user->hasRole('team_owner')){
                $user->assignRole('team_owner');
            }
            Cache::forget("owner_teams_".$user->id);
            Cache::forget("all_teams");
            return $team;
            });

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get a single team with its relationships.
     *
     * @param  Team $team
     *
     * @return Team $team
     */
    public function showTeam(Team $team)
    {
        try{
            $user = Auth::user();
            if($user->hasRole('admin') 
                ||($user->hasRole('team_owner') && $user->id === $team->owner_id) 
                )
            {
                return $team->load([
                    'owner',
                    'members',
                    'projects',
                ])->loadCount(['members' ,'projects']);
            }
        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Transfer the Ownership for the team
     * 
     * @param Team $team
     * @param string $newOwnerId
     * 
     * @return \Illuminate\Http\Response
     */
    public function transferOwnership(Team $team, int $newOwnerId)
    {
        $currentUser = Auth::user();
        $previousOwnerId = $team->owner_id;

        if(!($currentUser->hasRole('admin') || $previousOwnerId === $currentUser->id) || ! $newOwnerId){
            return $this->error('You do not have the permissions to transfer team ownership.',403);
        }

        return DB::transaction(function () use ($team, $newOwnerId, $previousOwnerId, $currentUser) {
            // Verify that the new team owner is a member of the team
            $newOwner = User::findOrFail($newOwnerId);
            if(!$team->members->contains($newOwner->id)){
                return $this->error('The new owner is not on the team',403);
            }

            $team->owner_id = $newOwner->id;
            $team->save();
            // Assign the role to the new owner
            if(!$newOwner->hasRole('team_owner')){
                $newOwner->assignRole('team_owner');
            }
            Cache::forget("owner_teams_".$previousOwnerId);
            Cache::forget("owner_teams_".$currentUser->id);
            Cache::forget("all_teams");

            // After updating check if the previous owner has no other teams
            //  If he does not own other teams, we will remove the role team_owner
            $otherTeamsCount = Team::where('owner_id', $previousOwnerId)
                                    ->where('id', '!=', $team->id)
                                    ->count();
            if ($otherTeamsCount === 0) {
                $previousOwner = User::find($previousOwnerId);
                if ($previousOwner && $previousOwner->hasRole('team_owner')) {
                    $previousOwner->removeRole('team_owner');
                }
            }
            return $team;
        });
    }


    /**
     * Update the specified team in the database.
     *
     * @param array $teamdata
     * @param Team $team
     *
     * @return Team $team
     */

    public function updateTeam(array $data, Team $team){
        try{
            $user=Auth::user();
            $newOwnerId = $data['owner_id'] ?? null;

            if(!($user->hasRole('admin') || $team->owner_id === $user->id) ){
                return $this->error('You do not have the permissions to update team',403);
            }
            // Check if trying to transfer ownership
            if ($newOwnerId && $newOwnerId != $team->owner_id) {
                $newTeam=$this->transferOwnership($team, $newOwnerId);
            }

            $filtered = collect($data)->only(['name'])->filter()->toArray();
            $team->update($filtered);
            if(!empty($data['member_ids'])){
                $team->members()->syncWithoutDetaching($data['member_ids']);
            }
            Cache::forget("owner_teams_".$team->owner_id);
            Cache::forget("owner_teams_".$user->id);
            Cache::forget("all_teams");

            return $team;

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified team from the database.
     *
     * @param Team $team
     *
     * @return Team $team
     */

    public function deleteTeam(Team $team){
        try{
            $user = Auth::user();
            if (!($user->hasRole('admin') || $team->owner_id === $user->id)){
                return $this->error('You do not have the permissions to delete a team that you do not own',403);
            }
            $team->members()->detach();
            $team->projects()->delete();
            return $team->delete();
            
        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Adding new members to the team, 
     *
     * @param array $memberIdsdata
     * @param Team $team
     *
     * @return Team $team
     */
    public function addMembersToTeam( array $data, Team $team){
        try{
            $user=Auth::user();
            if(!($user->hasRole('admin') || $team->owner_id === $user->id) ){
                return $this->error('You do not have the permissions to add members to this team',403);
            }
            $team->members()->syncWithoutDetaching($data['member_ids']);
            Cache::forget("owner_teams_".$user->id);
            Cache::forget("all_teams");
            return $team->load('members')
                        ->loadCount('members');

        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    
    /**
     * Removing members from the team, while preventing the removal
     *  of the team owner and of course after verifying that all
     *  members are present in the team 
     * 
     * @param array $memberIdsdata
     * @param Team $team
     *
     * @return Team $team
     */
    public function removeMembersFromTeam( array $data, Team $team){
        try{
            $user=Auth::user();
            if(!($user->hasRole('admin') || $team->owner_id === $user->id) ){
                return $this->error('You do not have the permissions to remove members from this team',403);
            }
            // Prevent owner deletion
            if(in_array($team->owner_id , $data['member_ids']))
            {
                return $this->error('You can not remove the team owner',403);
            }

            $team->members()->detach($data['member_ids']);
            Cache::forget("owner_teams_".$user->id);
            Cache::forget("all_teams");
            return $team->load('members')
                        ->loadCount('members');
            
        }catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }
}
