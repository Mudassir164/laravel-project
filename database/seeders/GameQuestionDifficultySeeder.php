<?php

namespace Database\Seeders;

use App\Models\GameQuestionDifficulty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameQuestionDifficultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameQuestionDifficulty::truncate();
        $set = [
            [
                'name' => 'Easy',
                'slug' => 'easy',
                'points' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Difficult',
                'slug' => 'difficult',
                'points' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        GameQuestionDifficulty::insert($set);
    }
}
