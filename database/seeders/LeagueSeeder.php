<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        League::truncate();
        $set = [
            [
                'title' => 'Newcomer',
                'score' => '0',
                'rating'=> 3,
                'image' => "leagues/rewardNewComer.png",
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Regular',
                'score' => '500',
                'rating'=> 4,
                'image' => "leagues/rewardNewComer.png",
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Quiz Master',
                'score' => '1000',
                'rating'=> 5,
                'image' => "leagues/rewardNewComer.png",
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        League::insert($set);
    }
}
