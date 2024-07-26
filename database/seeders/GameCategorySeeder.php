<?php

namespace Database\Seeders;

use App\Models\GameCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameCategory::truncate();
        $set = [
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        GameCategory::insert($set);
    }
}
