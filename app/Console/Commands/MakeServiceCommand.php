<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = app_path("Services/{$name}.php");

        if (File::exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }
        $stubPath = base_path('stubs/service.stub');
        if(!File::exists($stubPath)){
            $this->error("Stub file not found at {$stubPath}");
            return;
        }

        $stub=File::get($stubPath);
        $stub=str_replace(['{{ namespace }}','{{ class }}'],
                            ['App\Services',$name],$stub);
        File::ensureDirectoryExists(app_path('Services'));
        File::put($path, $stub);

        $this->info("Service Class [". $path ."]created successfully.");
    }
}
