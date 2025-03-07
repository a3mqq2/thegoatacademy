<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $adminRole = Role::create(['name' => 'Admin']);
        $InstructorRole = Role::create(['name' => 'Instructor']);
        $MonitorRole = Role::create(['name' => 'Supervisor']);
        $examOfficerRole = Role::create(['name' => 'Exam Officer']);

        

        $user = new User();
        $user->name = "administrator";
        $user->email = "admin@demo.com";
        $user->password = bcrypt("123123123");
        $user->status = "active";
        $user->save();

        $user->syncRoles($adminRole,$InstructorRole,$MonitorRole,$examOfficerRole);
    }
}
