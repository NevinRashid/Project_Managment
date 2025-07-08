<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add Permissions
        $permissions = [
            'create team',
            'update team',
            'view teams',
            'delete team',
            'add team member',
            'remove team member',

            'create project',
            'update project',
            'view projects',
            'delete project',
            'view completed project',
            'change project status',
            'change project manager',
            'add project workers',
            'remove project workers',

            'create task',
            'update task',
            'view tasks',
            'delete task',
            'assign task',
            'change task status',

            'create comment',
            'update comment',
            'view comments',
            'delete comment',

            'upload attachment',
            'update attachment',
            'view attachments',
            'delete attachment',

            'view notifications',
            
            'assign role',
            'update role',
            'delete role'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]); 
        }

        // add roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $team_owner = Role::firstOrCreate(['name' => 'team_owner']);
        $project_manager = Role::firstOrCreate(['name' => 'project_manager']);
        $member = Role::firstOrCreate(['name' => 'member']);

        // giving permissions to the roles
        $admin->givePermissionTo($permissions);
        $team_owner->givePermissionTo([
            'create team',
            'update team',
            'view teams',
            'delete team',
            'add team member',
            'remove team member',

            'create project',
            'update project',
            'view projects',
            'delete project',
            'view completed project',
            'change project status',
            'change project manager',
            'add project workers',
            'remove project workers',

            'create comment',
            'update comment',
            'view comments',
            'delete comment',

            'upload attachment',
            'update attachment',
            'view attachments',
            'delete attachment',

            'view notifications',
            'view tasks',
        ]);

        $project_manager->givePermissionTo([
            'create project',
            'update project',
            'view projects',
            'delete project',
            'change project status',
            'add project workers',
            'remove project workers',

            'create task',
            'update task',
            'view tasks',
            'delete task',
            'assign task',
            'change task status',

            'create comment',
            'update comment',
            'view comments',
            'delete comment',

            'upload attachment',
            'update attachment',
            'view attachments',
            'delete attachment',

            'view notifications',
        ]);

        $member->givePermissionTo([
            'create task',
            'update task',
            'view tasks',
            'delete task',
            'assign task',
            'change task status',

            'create comment',
            'update comment',
            'view comments',
            'delete comment',

            'upload attachment',
            'update attachment',
            'view attachments',
            'delete attachment',

            'view notifications',
        ]);
    }
}
