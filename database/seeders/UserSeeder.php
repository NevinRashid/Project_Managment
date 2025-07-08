<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate([
            'name'      => 'nevin',
            'email'     => 'nevin@gmail.com',
            'password'  => '123456789',  
        ]);
        $admin->assignRole('admin');  

        $team_owner = User::firstOrCreate([
            'name'      => 'rashid',
            'email'     => 'rashid@gmail.com',
            'password'  => '123456789',  
        ]);
        $team_owner->assignRole('team_owner');  

        $project_manager = User::firstOrCreate([
            'name'      => 'mohamad',
            'email'     => 'mohamad@gmail.com',
            'password'  => '123456789',  
        ]);
        $project_manager->assignRole('project_manager');  

        $member = User::firstOrCreate([
            'name'      => 'heba',
            'email'     => 'heba@gmail.com',
            'password'  => '123456789',  
        ]);
        $member->assignRole('member');  

    }
}
