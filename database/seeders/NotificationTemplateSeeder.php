<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotificationTemplate::truncate();
        NotificationTemplate::insert(
            [
                [
                    'identifier' => "game_end_15_minutes",
                    'body' => "Dear <b>[USERNAME]</b>, Your [GAMENAME] is ready to end in 15 minutes.",
                    'wildcards' => "[USERNAME,GAMENAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "game_start_30_minutes",
                    'body' => "Dear <b>[USERNAME]</b>, Your [GAMENAME] is ready to start in 30 minutes.",
                    'wildcards' => "[USERNAME,GAMENAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "game_created",
                    'body' => "Dear <b>[USERNAME]</b>, New Game is created.",
                    'wildcards' => "[USERNAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "game_completed",
                    'body' => "Dear <b>[USERNAME]</b>, Game has been finished.",
                    'wildcards' => "[USERNAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "incomming_friend_request",
                    'body' => "Dear <b>[USERNAME]</b>, you have a new friend request from <b>[SENDER]</b>",
                    'wildcards' => "[USERNAME,SENDER]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "friend_request_accepted",
                    'body' => "Dear <b>[USERNAME]</b>, your friend request was accepted from <b>[SENDER]</b>",
                    'wildcards' => "[USERNAME,SENDER]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "quiz_created",
                    'body' => "Dear <b>[USERNAME]</b>, New Quiz has created by <b>[ORGANIZER]</b>",
                    'wildcards' => "[USERNAME,ORGANIZER]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "quiz_finished",
                    'body' => "Dear <b>[USERNAME]</b>, A Quiz has been completed.",
                    'wildcards' => "[USERNAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "sponsership_request",
                    'body' => "Dear <b>[USERNAME]</b>, You have got sponsership request from <b>[SPONSER]</b>",
                    'wildcards' => "[USERNAME,SPONSER]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "sponsership_accepted",
                    'body' => "Dear <b>[USERNAME]</b>, Your sponser request was accepted",
                    'wildcards' => "[USERNAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'identifier' => "sponsership_rejected",
                    'body' => "Dear <b>[USERNAME]</b>, Your sponser request was declined",
                    'wildcards' => "[USERNAME]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]
        );
    }
}
