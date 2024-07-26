<?php

namespace Database\Seeders;

use App\Models\QuestionTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        QuestionTemplate::truncate();
        $set = [
            [
                'question' => 'Who win the match in [team_a] and [team_b]?',
                'replacers' => '[team_a,team_b]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // [
            //     'question' => 'Who will get the ball first in [team_a] and [team_b]?',
            //     'replacers' => '[team_a,team_b]',
            //     'status' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now()
            // ],
            // [
            //     'question' => 'Which team score the first goal?',
            //     'replacers' => '[team_a,team_b]',
            //     'status' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now()
            // ],
            [
                'question' => 'Which team score more goals in first half?',
                'replacers' => '[team_a,team_b]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'question' => 'Which team score more goals in second half?',
                'replacers' => '[team_a,team_b]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'question' => 'Which team score more goals?',
                'replacers' => '[team_a,team_b]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        QuestionTemplate::insert($set);
    }
}
