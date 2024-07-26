<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        $set = [
            [
                'title' => 'Admin',
                'slug' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Pub Owner',
                'slug' => 'pub_owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Sponsor',
                'slug' => 'sponsor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Organizer',
                'slug' => 'organizer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Role::insert($set);
    
    }
}