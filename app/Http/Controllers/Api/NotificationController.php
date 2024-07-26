<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }


    public function NotificationList(Request $request)
    {
        try {
            $limit = $request->get('limit') ?? 10;

            $id = auth()->id();

            $notifications = $this->notification
            ->where('receiver_id', $id)
            ->where('read', 0)
            ->where('ref', 'App\Models\User')
            ->paginate($limit);

            $data = $notifications->items();
            $page = $notifications->toArray();
            unset($page['data']);

            return $this->responseToClient(compact('data', 'page'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
