<?php

namespace App\Jobs;

use App\Models\{Notification, WebUser};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GameEndPushNotificationJobForWeb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId, $game, $route;

    /**
     * Create a new job instance.
     *
     * @param int $userId
     * @param mixed $game
     * @param string $route
     */
    public function __construct($userId, $game, $route)
    {
        $this->userId = $userId;
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
        $user = WebUser::find($this->userId);
        if (!$user) {
            Log::error("User with ID {$this->userId} not found.");
            return;
        }

        $this->notifyPubOwner($user);
        $this->notifyGameCreator($user);
    }

    /**
     * Notify the pub owner of the organizer.
     *
     * @param WebUser $user
     */
    private function notifyPubOwner($user)
    {
        if ($user->parent_id) {
            $pubOwner = WebUser::find($user->parent_id);
            if ($pubOwner && $pubOwner->device_token) {
                $notification = new Notification();
                $notification->storeNotification([
                    'ref' => get_class($pubOwner),
                    'title' => 'Game has ended',
                    'identifier' => 'game_completed',
                    'route_name' => $this->route,
                    'ref_id' => $this->game->id,
                    'sender_id' => $this->userId,
                    'receiver_id' => $user->parent_id,
                    'replacers' => '[' . $pubOwner->owner . ']'
                ]);
            }
        }
    }

    /**
     * Notify the game creator.
     *
     * @param WebUser $user
     */
    private function notifyGameCreator($user)
    {
        if ($user->device_token) {
            $notification = new Notification();
            $notification->storeNotification([
                'ref' => get_class($user),
                'title' => 'Your game has ended',
                'identifier' => 'game_completed',
                'route_name' => $this->route,
                'ref_id' => $this->game->id,
                'sender_id' => $this->userId,
                'receiver_id' => $this->game->web_user_id,
                'replacers' => '[' . $user->user_name . ']'
            ]);
        }
    }
}
