<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectWorkerServiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_add_workers_to_project(): void
    {
        $project = Project::factory()->create();
        $users =User::factory()->count(3)->create();

        $this->actingAs($users->first());
        Role::create(['name' => 'member']);
        $data = [];
        $workersIds = [];
        $workersIds= $users->pluck('id')->toArray();
        $data['worker_ids'] =$workersIds;
        $service = new ProjectService();
        $service->addWorkersToProject($data,$project);
        $this->assertEqualsCanonicalizing($workersIds, $project->workers()->pluck('user_id')->toArray());
    }
}
