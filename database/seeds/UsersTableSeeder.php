<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/9/15
 * Time: 15:05
 */

use \App\User;
use Bican\Roles\Models\Role;

class UsersTableSeeder extends \Illuminate\Database\Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        DB::statement('truncate role_user');
        DB::statement('truncate permission_user');

        User::truncate();

        $user = User::create([
            'name'=>"Dan Schultz",
            'email'=>"dschultz@octanela.com",
            'password'=>'12345678',
            'phone'=>"3106004938"
        ]);

        $user->attachRole(2);

        $user = User::create([
            'name'=>"Andrew Davis",
            'email'=>"adavis@octanela.com",
            'password'=>'12345678',
            'phone'=>"3104332997"
        ]);

        $user->attachRole(2);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}