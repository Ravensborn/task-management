<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        Permission::create(['name' => 'users crud']);

        $userManagerRole = Role::create(['name' => 'user manager']);
        $userManagerRole->givePermissionTo(['users crud']);



         $yad = \App\Models\User::factory()->create([
             'name' => 'Yad Hoshyar',
             'email' => 'yad@example.com',
         ]);

        $yad->givePermissionTo('users crud');




    }
}
