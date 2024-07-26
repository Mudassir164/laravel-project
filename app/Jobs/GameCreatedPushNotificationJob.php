<?php

namespace App\Jobs;

use App\Models\{Notification, User, WebUser};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GameCreatedPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $game;
    private $userId;
    private $route;

    public function __construct($userId, $game, $route)
    {
        $this->game = $game;
        $this->userId = $userId;
        $this->route = $route;
    }

    public function handle()
    {
        $user = WebUser::find($this->userId);
        if (!$user) {
            Log::error("User with ID {$this->userId} not found.");
            return;
        }

        $this->notifyUsersInSameCity($user);
        $this->notifyPubOwner($user);
        $this->notifyGameCreator($user);
    }

    private function notifyUsersInSameCity($user)
    {
        $notification = new Notification();
        $cityId = $user->city_id;

        $users = User::where('city_id', $cityId)->where('status', 1)->get();
        foreach ($users as $user) {
            if ($user->device_token) {
                $notification->storeNotification([
                    'ref' => get_class($user),
                    'title' => 'New game has been created',
                    'identifier' => 'game_created',
                    'route_name' => $this->route,
                    'ref_id' => $this->game->id,
                    'sender_id' => $this->userId,
                    'receiver_id' => $user->id,
                    'replacers' => '[' . $user->name . ']'
                ]);
            }
        }
    }

    private function notifyPubOwner($user)
    {
        $notification = new Notification();

        if ($user->parent_id) {
            $pubOwner = WebUser::find($user->parent_id);
            if ($pubOwner && $pubOwner->device_token) {
                $notification->storeNotification([
                    'ref' => get_class($pubOwner),
                    'title' => 'New game has been created',
                    'identifier' => 'game_created',
                    'route_name' => $this->route,
                    'ref_id' => $this->game->id,
                    'sender_id' => $this->userId,
                    'receiver_id' => $user->parent_id,
                    'replacers' => '[' . $pubOwner->owner . ']'
                ]);
            }
        }
    }

    private function notifyGameCreator($user)
    {
        $notification = new Notification();

        if ($user->device_token) {
            $notification->storeNotification([
                'ref' => get_class($user),
                'title' => 'Your new game has been created',
                'identifier' => 'game_created',
                'route_name' => $this->route,
                'ref_id' => $this->game->id,
                'sender_id' => $this->userId,
                'receiver_id' => $this->game->web_user_id,
                'replacers' => '[' . $user->user_name . ']'
            ]);
        }
    }
    
}
