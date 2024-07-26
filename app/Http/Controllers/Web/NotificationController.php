<?php

namespace App\Http\Controllers\Web;

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

    public function notificationNew()
    {
        try {
            $id = auth()->id();

            $notifications = $this->notification
                ->where('receiver_id', $id)
                ->where('read', 0)
                ->where('ref', 'App\Models\WebUser')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return $this->responseToClient(['data' => $notifications]);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function notificationList(Request $request)
    {
        try {
            $limit = $request->get('limit') ?? 10;

            $id = auth()->id();

            $notifications = $this->notification
                ->where('receiver_id', $id)
                ->where('ref', 'App\Models\WebUser')
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            $data = $notifications->items();
            $page = $notifications->toArray();
            unset($page['data']);

            return $this->responseToClient(compact('data', 'page'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function notificationStatusUpdate(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required',
            ]);

            $status = $request->input('status');

            $data = $this->notification->notificationStatusUpdate($id, $status);

            if ($data) {
                $response = [
                    'data' => $data,
                    'message' => 'Status Updated Successfully!',
                ];

                return $this->responseToClient($response);
            } else {
                return $this->responseToClient(['message' => 'Status Not Updated'], 404);
            }

        } catch (\Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }

    public function bulkNotificationStatusUpdate(Request $request){
        $ids = $request->input('ids');
        $status = $request->input('status');

        if (!empty($ids)) {
            $this->notification->whereIn('id', $ids)->update(['read' => $status]);

            return response()->json(['success' => true]);
        } else {
            return response()->json(['message' => 'No notifications found'], 404);
        }
        }
}
