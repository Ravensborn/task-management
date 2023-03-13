<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Board;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Tags\Tag;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Permission::create(['name' => 'users crud']);
        Permission::create(['name' => 'boards crud']);
        Permission::create(['name' => 'tasks crud']);
        Permission::create(['name' => 'labels crud']);

        $productOwnerRole = Role::create(['name' => 'product owner']);
        $developerRole = Role::create(['name' => 'developer']);
        $testerRole = Role::create(['name' => 'tester']);


        $productOwnerRole->givePermissionTo(['users crud']);
        $productOwnerRole->givePermissionTo(['boards crud']);
        $productOwnerRole->givePermissionTo(['tasks crud']);
        $productOwnerRole->givePermissionTo(['labels crud']);


        $yad = \App\Models\User::factory()->create([
            'name' => 'Yad Hoshyar',
            'email' => 'yad@example.com',
        ]);

//        $yad->assignRole('product owner');
        $yad->assignRole('developer');
//        $yad->assignRole('tester');

        Board::factory()->create([
            'title' => 'Default board'
        ]);

        $statusList = ['to-do', 'in-progress', 'dev-review', 'testing', 'done', 'close'];

       foreach ($statusList as $status) {
           Status::factory()->create([
               'title' => $status
           ]);
       }

        Task::factory(1)->create();

       Tag::findOrCreate('important', 'label');
       Tag::findOrCreate('work related', 'label');
       Tag::findOrCreate('personal', 'label');
       Tag::findOrCreate('family', 'label');


    }
}
