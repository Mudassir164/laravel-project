<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameType::truncate();
        $set = [
            [
                'name' => 'Quiz',
                'slug' => 'quiz',
                'description' => 'Create a quiz based activity',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Coupon',
                'slug' => 'coupon',
                'description' => 'Create a sport coupon based activity',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        GameType::insert($set);
    }
}
