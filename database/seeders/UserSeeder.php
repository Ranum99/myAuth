<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'username' => 'john',
                'email' => 'john@example.com',
                'password' => bcrypt('password123'),
                'verified' => false,
            ],
            [
                'username' => 'jane',
                'email' => 'jane@example.com',
                'password' => bcrypt('password456'),
                'verified' => true,
            ],
            [
                'username' => 'alice',
                'email' => 'alice@example.com',
                'password' => bcrypt('alicepass'),
                'verified' => false,
            ],
            [
                'username' => 'bob',
                'email' => 'bob@example.com',
                'password' => bcrypt('bobpass'),
                'verified' => true,
            ],
            [
                'username' => 'sarah',
                'email' => 'sarah@example.com',
                'password' => bcrypt('sarahpass'),
                'verified' => false,
            ],
            [
                'username' => 'michael',
                'email' => 'michael@example.com',
                'password' => bcrypt('michaelpass'),
                'verified' => false,
            ],
            [
                'username' => 'emily',
                'email' => 'emily@example.com',
                'password' => bcrypt('emilypass'),
                'verified' => true,
            ],
            [
                'username' => 'david',
                'email' => 'david@example.com',
                'password' => bcrypt('davidpass'),
                'verified' => false,
            ],
            [
                'username' => 'olivia',
                'email' => 'olivia@example.com',
                'password' => bcrypt('oliviapass'),
                'verified' => true,
            ],
            [
                'username' => 'william',
                'email' => 'william@example.com',
                'password' => bcrypt('williampass'),
                'verified' => false,
            ],
            [
                'username' => 'sophia',
                'email' => 'sophia@example.com',
                'password' => bcrypt('sophiapass'),
                'verified' => true,
            ],
            [
                'username' => 'daniel',
                'email' => 'daniel@example.com',
                'password' => bcrypt('danielpass'),
                'verified' => true,
            ],
            [
                'username' => 'ava',
                'email' => 'ava@example.com',
                'password' => bcrypt('avapass'),
                'verified' => true,
            ],
            [
                'username' => 'matthew',
                'email' => 'matthew@example.com',
                'password' => bcrypt('matthewpass'),
                'verified' => false,
            ],
            [
                'username' => 'isabella',
                'email' => 'isabella@example.com',
                'password' => bcrypt('isabellapass'),
                'verified' => true,
            ],
        ];

        foreach ($users as $user) {
            $user = (object) $user;
            User::create([
                'username' => $user->username   ,
                'email' => $user->email,
                'password' => $user->password,
                'remember_token' => $user->verified ? null : Str::random(),
                'email_verified_at' => $user->verified ? NOW() : null,
            ]);
        }
    }
}
