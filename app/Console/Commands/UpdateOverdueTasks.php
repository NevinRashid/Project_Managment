<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class UpdateOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-overdue-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of overdue tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get Overdue tasks
        $overdueTasks = Task::OverdueTasks()->get();
        if ($overdueTasks->isEmpty()) {
            $this->info("No overdue tasks to update.");
            return 0;
        }
        foreach($overdueTasks as $overdueTask){
            $overdueTask->update([
                'status' => 'overdue'
            ]);
        }
        $updatedCount= $overdueTasks->count();
        $this->info("Successfully updated status for {$updatedCount} overdue tasks.");
        return 0;
    }
}
