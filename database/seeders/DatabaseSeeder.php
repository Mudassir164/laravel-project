<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // UserSeeder::class,
            // CountriesTableSeeder::class,
            // StatesTableSeeder::class,
            // CitiesTableChunkOneSeeder::class,
            // CitiesTableChunkTwoSeeder::class,
            // CitiesTableChunkThreeSeeder::class,
            // CitiesTableChunkFourSeeder::class,
            // CitiesTableChunkFiveSeeder::class,
            // RoleSeeder::class,
            // GameTypeSeeder::class,
            // GameCategorySeeder::class,
            // GameQuestionTypeSeeder::class,
            // GameQuestionDifficultySeeder::class,
            // WebUserSeeder::class,
            // WebRoutesSeeder::class,
            // RoleWebRouteSeeder::class,
            // QuestionTemplateSeeder::class,
            // NotificationTemplateSeeder::class,
            // PositionsTableSeeder::class,
            LeagueSeeder::class,
        ]);
    }
}
