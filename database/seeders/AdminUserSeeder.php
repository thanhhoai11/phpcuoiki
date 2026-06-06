<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'admin',
                'fullname' => 'Administrator',
                'email' => 'admin@gmail.com',
                'phone' => '0901234567',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'verified' => 1,
                'role' => 'admin',
                'created_at' => now(),
            ],
            [
                'username' => 'letan',
                'fullname' => 'Lễ Tân',
                'email' => 'letan@gmail.com',
                'phone' => '0901234568',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'verified' => 1,
                'role' => 'receptionist',
                'created_at' => now(),
            ]
        ]);
    }
}
