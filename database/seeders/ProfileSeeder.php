<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $name = ucfirst(explode('@', $user->email)[0]);
            Profile::create([
                'users_id' => $user->id,
                'name' => $name,
                'bio' => 'This is the bio for ' . $name,
            ]);
        }
    }
}
