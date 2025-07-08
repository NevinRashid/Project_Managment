<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProjectOverdueJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $overdueProjects = Project::OverdueProjects()->get();
        if ($overdueProjects->isEmpty()) {
        }
        foreach($overdueProjects as $overdueProject){
            $overdueProject->update([
                'status' => 'overdue'
            ]);
        }
    }
}
