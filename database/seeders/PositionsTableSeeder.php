<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Position::truncate();
        $set = [
            [
                'title' => 'First',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Second',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Third',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Fourth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Fifth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Sixth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Seventh',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Eighth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Ninth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Tenth',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        Position::insert($set);
    }
}
