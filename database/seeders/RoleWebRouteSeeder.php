<?php

namespace Database\Seeders;

use App\Models\RoleWebRoute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleWebRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RoleWebRoute::truncate();
        DB::table('role_web_routes')->insert([
            //pubOwner
            [
                'role_id' => 2,
                'web_route_id' => 1,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 2,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 3,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 4,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 5,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 6,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 7,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 8,
            ],
            [
                'role_id' => 2,
                'web_route_id' => 9,
            ],
            //admin
            [
                'role_id' => 1,
                'web_route_id' => 10,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 11,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 12,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 13,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 14,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 15,
            ],
            [
                'role_id' => 1,
                'web_route_id' => 16,
            ],
            //sponsor
            [
                'role_id' => 3,
                'web_route_id' => 17,
            ],
            [
                'role_id' => 3,
                'web_route_id' => 18,
            ],
            [
                'role_id' => 3,
                'web_route_id' => 19,
            ],
            //organizer
            [
                'role_id' => 4,
                'web_route_id' => 1,
            ],
            [
                'role_id' => 4,
                'web_route_id' => 2,
            ],
            [
                'role_id' => 4,
                'web_route_id' => 5,
            ],
            [
                'role_id' => 4,
                'web_route_id' => 6,
            ],
            [
                'role_id' => 4,
                'web_route_id' => 9,
            ]
        ]);
    }
}
