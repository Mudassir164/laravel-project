<?php

namespace App\Models;

use App\Libraries\PushNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function App\Helpers\time_elapsed_string;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['ref','title', 'identifier', 'route_name', 'ref_id', 'sender_id', 'receiver_id',
        'replacers', 'read', 'is_push'];
    protected $appends = ['text', 'time'];

    public function getTextAttribute()
    {
        $text = str_replace(explode(',', $this->tags->wildcards), explode(',', $this->replacers), $this->tags->body);
        return str_replace(array( '[', ']' ), '', strip_tags($text));
    }

    public function getTimeAttribute()
    {
        return time_elapsed_string($this->created_at);
    }

    public function tags()
    {
        return $this->belongsTo(NotificationTemplate::class, 'identifier', 'identifier');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function getRecords($receiver_id)
    {
        $response['count'] = $this->getUnReadCount($receiver_id);
        $response['notifications'] = $this->getByReceiver($receiver_id);

        return $response;
    }

    public function getUnReadCount($receiver_id)
    {
        return $this->where('receiver_id', $receiver_id)->where('read', 0)->count();
    }

    public function getTotalCount($receiver_id)
    {
        return $this->where('receiver_id', $receiver_id)->count();
    }

    public function getByReceiver($receiver_id, $limit = 10)
    {
        $notifications = $this->with(['tags', 'sender'])->where('receiver_id', $receiver_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $this->whereReceiverId($receiver_id)->update(['read' => 1]);

        return $notifications;
    }

    /*public function makeNotificationBody($notification)
    {
        $response['sender'] = explode(',', $notification->replacers)[0];
        $body = str_replace(explode(',', $notification->tags->wildcards),
            explode(',', $notification->replacers), $notification->tags->body);
        $notification->body = $body;

        return $notification;
    }*/


    public function makeNotificationBody($notification)
    {
        $body = str_replace(explode(',', $notification->tags->wildcards),
            explode(',', $notification->replacers), $notification->tags->body);

        return strip_tags($body);
    }

    public function scopeSearch($query,$value){
        return $query->Where('title','like','%'.$value.'%');
    }

    public function storeNotification($request)
    {
        return $this->create($request);
    }

    public static function makeNotificationText($tags, $replacers)
    {
        return str_replace(explode(',', $tags->wildcards), explode(',', $replacers), $tags->body);
    }

    public static function sendPushNotification($notification)
    {
        $ref = $notification->ref;
        $device_token = $ref::find($notification->receiver_id)->device_token;

        if (!empty($device_token)) {
            $row = self::with(['tags'])->find($notification->id);
            $body = self::makeNotificationText($row->tags, $row->replacers);
            
            $notification = [
                'title' => !empty($row->title) ? $row->title : env('APP_NAME'),
                'body' => str_replace(array( '[', ']' ), '', strip_tags($body)),
                // 'sound' => 'default'
            ];
            $data = [
                'identifier' => $row->identifier,
                'sender_id' => strval($row->sender_id),
                'receiver_id' => strval($row->receiver_id),
                'ref_id' => strval($row->ref_id),
                'time' => time_elapsed_string($row->created_at)
            ];

            $message = [
                'token' => $device_token,
                'notification' => $notification,
                'data' => $data,
                // 'priority' => 'high'
            ];


            PushNotification::send(json_encode(['message'=>$message]));
        }
    }

    public function sendSystemNotification($request)
    {
        $users = User::where('status', '1')->get();
        foreach ($users as $user) {
            $this->storeNotification([
                'title' => !empty($request->title) ? $request->title : 'System Notification',
                'identifier' => $request->identifier,
                'route_name' => 'dashboard',
                'receiver_id' => $user->id,
                'is_push' => 0
            ]);
        }
    }

    public static function sendPushCron() {
        $notifications = self::where('is_push', 0)->get();
        foreach ($notifications as $notification) {
            self::sendPushNotification($notification);
            $notification->is_push = 1;
            $notification->save();
        }
    }

    public function makeParams($request){
        $limit = $request->has('limit') ? $request->limit : 10;
        $search = $request->has('search') ? $request->search : '';
        $type = $request->has('type') ? $request->type : '';
        $sort = $request->has('sort') ? $request->sort : '';
        $user_id = $request->user()?$request->user()->id:null;
        $params = [
            'limit'=>$limit,
            'search'=>$search,
            'type'=>$type,
            'sort'=>$sort,
            'user_id' => $user_id,
        ];
        $notifications = $this->allNotifications($params);
        if($notifications){
            foreach($notifications as $notification){
                $notification->notificationText = str_replace(array( '[', ']' ), '', strip_tags($notification->text));
            }
        }
        $notifications = $notifications->toArray();
        return $this->makeResponseListing($notifications);
    }

    public function allNotifications($params){
        $user_id = $params['user_id'];
        $query = Notification::where('receiver_id',$user_id)->with(['sender', 'order.post.images']);

        if(isset($params['search']) && $params['search'] !== ''){
            $query->search($params['search']);
        }
        $limit = isset($params['limit']) && $params['limit'] !== '' ? $params['limit'] : 10;
        $notifications = $query->orderBy('id', 'desc')->paginate($limit);
        return $notifications;
    }

    private function makeResponseListing($reviews){
        $response['message'] = 'Data retrive';
        $response['data'] = $reviews['data'];
        $response['page'] = [
            "current_page" => $reviews["current_page"],
            "from" => $reviews["from"],
            "last_page" => $reviews["last_page"],
            "last_page_url" => $reviews["last_page_url"],
            "next_page_url" => $reviews["next_page_url"],
            "path" => $reviews["path"],
            "per_page" => $reviews["per_page"],
            "prev_page_url" => $reviews["prev_page_url"],
            "to" => $reviews["to"],
            "total" => $reviews["total"]
        ];
        return $response;
    }

    public function notificationStatusUpdate($id, $status)
    {
        $notification = $this->find($id);

        $receiverId = $notification->receiver_id;

        $webUserId = auth()->id();
        
        if ($receiverId === $webUserId) {
            $notification->read = $status;
            $notification->update();
            return $notification;
        }
        return null;
    }

    // public function updateNotificationsStatusBulk($ids, $status)
    // {
    //     $this->notification->whereIn('id', $ids)->update(['read' => $status]);
        
    //     // return $notification;

    //     // $notification = $this->find($id);

    //     // $receiverId = $notification->receiver_id;

    //     // $webUserId = auth()->id();
        
    //     // if ($receiverId === $webUserId) {
    //     //     $notification->read = $status;
    //     //     $notification->update();
    //     //     return $notification;
    //     // }
    //     return null;
    // }
}
