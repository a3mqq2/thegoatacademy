<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Permissions
        $admin_permissions = [
            'Manage Users',
            'Students List',
            'Courses List',
            'Reports',
            'Manage Settings',
            'Manage Quality Settings',
            'Audit Logs',
        ];


        $exammanPermissions = [
            'Exam Manager'
        ];

       $permissions = array_merge($admin_permissions, $exammanPermissions);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'Instructor']);
        $monitorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $examOfficerRole = Role::firstOrCreate(['name' => 'Examiner']);

        // Assign Permissions to Admin Role
        $adminRole->syncPermissions($permissions);
        $examOfficerRole->syncPermissions($exammanPermissions);
        // Create an Admin User
        $user = User::firstOrCreate([
            'email' => 'admin@demo.com',
        ], [
            'name' => 'Administrator',
            'password' => bcrypt('123123123'),
            'status' => 'active',
        ]);

        // Assign Admin Role to the User
        $user->syncRoles([$adminRole, $instructorRole, $monitorRole, $examOfficerRole]);
        $user->givePermissionTo($permissions);






        $this->call([
            QualitySettingsSeeder::class,
            SkillsSeeder::class,
            GroupTypeSeeder::class,
            // CourseTypeSeeder::class,
            LevelsTableSeeder::class,
        ]);
    }
}
