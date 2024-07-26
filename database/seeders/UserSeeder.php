<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::truncate();
        User::where('email','java@mail.com')->forceDelete();
        $testUser = [
            'name' => 'java',
            'email' => 'java@mail.com',
            'gender' => 'male',
            'email_verified_at' => now(),
            'password' => 12345678,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        User::create($testUser);
        User::factory(1000)->create();
    }
}
