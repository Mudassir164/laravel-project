<?php

namespace Database\Seeders;

use App\Models\GameQuestionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameQuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameQuestionType::truncate();
        $set = [
            [
                'name' => 'Multiple Choice',
                'slug' => 'multiple_choice',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Free Text',
                'slug' => 'free_text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'Photo',
            //     'slug' => 'photo',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => 'Video',
            //     'slug' => 'video',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'name' => 'True Or False',
                'slug' => 'true_or_false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        GameQuestionType::insert($set);
    }
}
