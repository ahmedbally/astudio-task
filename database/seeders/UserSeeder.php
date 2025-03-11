<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() !== 0) {
            return;
        }

        User::factory()->create([
            'email' =>  'user@astudio.com',
            'password' => bcrypt('password'),
        ]);
    }
}
