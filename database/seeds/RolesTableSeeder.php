<?php

use Illuminate\Database\Seeder;
use Bican\Roles\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Role::truncate();

        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => '', // optional
            'level' => 1, // optional, set to 1 by default
        ]);

        Role::create([
            'name' => 'Worker',
            'slug' => 'worker',
            'level' => 5,
        ]);

        Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'level' => 10,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
