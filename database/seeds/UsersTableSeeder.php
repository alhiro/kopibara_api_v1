<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin Kopi Bara',
            'email' => 'admin@kopibara.id',
            'password' => bcrypt('123456789')
        ]);
    }
}
