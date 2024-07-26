<?php

namespace App\Jobs;

use App\Models\{Notification, WebUser,User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GameStartPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $webUserId,$game,$route;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($webUserId,$game,$route)
    {
        $this->webUserId = $webUserId;
        $this->game = $game;
        $this->route = $route;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification = new Notification();
        $webUser = WebUser::find($this->webUserId);
        $longitude = $webUser->longitude;
        $latitude = $webUser->latitude;
        $users = User::byRadius($latitude,$longitude);
        if(count($users)){
            foreach ($users as $user) {
                if($user->device_token){
                    $notification->storeNotification([
                        'ref' => get_class($user),
                        'title' => 'Game will be Start in 30 Minutes',
                        'identifier' => 'game_start_30_minutes',
                        'route_name' => $this->route,
                        'ref_id' => $this->game->id,
                        'sender_id' => $webUser->id,
                        'receiver_id' => $user->id,
                        'replacers' => '['.$user->name.','.$this->game->name.']',
                    ]);
                }
            }
        }
    }
}
