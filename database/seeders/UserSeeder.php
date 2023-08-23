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
                'email' => 'john@example.com',
                'password' => bcrypt('password123'),
                'verified' => false,
            ],
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('password456'),
                'verified' => true,
            ],
            [
                'email' => 'alice@example.com',
                'password' => bcrypt('alicepass'),
                'verified' => false,
            ],
            [
                'email' => 'bob@example.com',
                'password' => bcrypt('bobpass'),
                'verified' => true,
            ],
            [
                'email' => 'sarah@example.com',
                'password' => bcrypt('sarahpass'),
                'verified' => false,
            ],
            [
                'email' => 'michael@example.com',
                'password' => bcrypt('michaelpass'),
                'verified' => false,
            ],
            [
                'email' => 'emily@example.com',
                'password' => bcrypt('emilypass'),
                'verified' => true,
            ],
            [
                'email' => 'david@example.com',
                'password' => bcrypt('davidpass'),
                'verified' => false,
            ],
            [
                'email' => 'olivia@example.com',
                'password' => bcrypt('oliviapass'),
                'verified' => true,
            ],
            [
                'email' => 'william@example.com',
                'password' => bcrypt('williampass'),
                'verified' => false,
            ],
            [
                'email' => 'sophia@example.com',
                'password' => bcrypt('sophiapass'),
                'verified' => true,
            ],
            [
                'email' => 'daniel@example.com',
                'password' => bcrypt('danielpass'),
                'verified' => true,
            ],
            [
                'email' => 'ava@example.com',
                'password' => bcrypt('avapass'),
                'verified' => true,
            ],
            [
                'email' => 'matthew@example.com',
                'password' => bcrypt('matthewpass'),
                'verified' => false,
            ],
            [
                'email' => 'isabella@example.com',
                'password' => bcrypt('isabellapass'),
                'verified' => true,
            ],
        ];

        foreach ($users as $user) {
            $user = (object) $user;
            User::create([
                'email' => $user->email,
                'password' => $user->password,
                'remember_token' => $user->verified ? null : Str::random(),
                'email_verified_at' => $user->verified ? NOW() : null,
            ]);
        }
    }
}
