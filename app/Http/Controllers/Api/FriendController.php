<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Friend,Notification};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
class FriendController extends Controller
{
    private $user,$notification;
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->notification = new Notification();
    }

    public function AddFriend(Request $request)
    {
        // $encryptedKey = Crypt::encrypt($request->encrypted_id);
        $key = 12345; // Simple key for demonstration purposes
        $data = $request->encrypted_id;

        //$encrypted = $this->customEncryptToNumbers($data, $key);
        //$decrypted = $this->customDecryptFromNumbers(3412330, $key);

        try {
            $encrypted_id = $request->encrypted_id;
            // $friendId = Crypt::decrypt($encrypted_id);

            if(is_numeric($encrypted_id))
            {
                $friendId = $this->customDecryptFromNumbers($encrypted_id, $key);

                if(is_numeric($friendId))
                {
                    $userId = auth()->id();
                    if($userId != $friendId)
                    {
                        $check_friend = Friend::where('friend_id',$friendId)->where('user_id',$userId)->first();

                        if(!$check_friend || $check_friend->status == 'declined')
                        {

                            //  for sender
                                $attributes = [
                                    'user_id' => $userId,
                                    'friend_id' => $friendId,
                                ];

                                $values = [
                                    'status' => 'pending',
                                ];
                            Friend::updateOrCreate($attributes, $values);

                            //    for receiver

                                $attributes = [
                                    'user_id' => $friendId,
                                    'friend_id' => $userId,
                                ];

                                $values = [
                                    'status' => 'incomming',
                                ];
                            $incomming = Friend::updateOrCreate($attributes, $values);

                            $receiver = $this->user::find($friendId);
                            //Send Push Notification
                            $this->notification->storeNotification([
                                'ref' => get_class($this->user),
                                'title' => 'Incomming Friend Request',
                                'identifier' => 'incomming_friend_request',
                                'route_name' => $request->route()->uri(),
                                'ref_id' => $incomming->id,
                                'sender_id' => auth()->id(),
                                'receiver_id' => $friendId,
                                'replacers' => '['.$receiver->name.','.auth()->user()->name.']',
                            ]);
                            return $this->responseToClient(['status'=>'success','message' => 'Freiend request sent successfully'], 200);
                        }
                        else
                        {
                            if($check_friend->status == 'accepted')
                            {
                                return $this->responseToClient(['status'=>'success','message' => 'Already friends'], 200);

                            }
                            else if($check_friend->status == 'pending')
                            {
                                return $this->responseToClient(['status'=>'success','message' => 'Friend request already has been sent!'], 200);
                            }
                        }

                    }
                    else
                    {
                        return $this->responseToClient(['status'=>'success','message' => 'User can not send request to its self!'], 200);
                    }



                }
                else
                {
                    return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
                }

            }else{
                return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
            }

        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function AcceptFriend(Request $request)
    {
        //$encryptedKey = Crypt::encrypt($request->encrypted_id);
        //echo $encryptedKey;die;
        try {
            $key = 12345;
            $encrypted_id = $request->encrypted_id;
            // $friendId = Crypt::decrypt($encrypted_id);
            if(is_numeric($encrypted_id))
            {

                $friendId = $this->customDecryptFromNumbers($encrypted_id, $key);
                $userId = auth()->id();
                if(is_numeric($friendId))
                {

                    //  for sender
                    $attributes = [
                        'user_id' => $userId,
                        'friend_id' => $friendId,
                    ];

                    $values = [
                        'status' => 'accepted',
                    ];

                    $sender = Friend::updateOrCreate($attributes, $values);


                    //    for receiver

                    $attributes = [
                        'user_id' => $friendId,
                        'friend_id' => $userId,
                    ];

                    $values = [
                        'status' => 'accepted',
                    ];
                    Friend::updateOrCreate($attributes, $values);

                    //Send FCM Push Notification
                    $this->notification->storeNotification([
                        'ref' => get_class($this->user),
                        'title' => 'Friend Request Accepted',
                        'identifier' => 'friend_request_accepted',
                        'route_name' => $request->route()->uri(),
                        'ref_id' => $sender->id,
                        'sender_id' => auth()->id(),
                        'receiver_id' => $friendId,
                        'replacers' => '['.$this->user->find($friendId)->name.','.auth()->user()->name.']',
                    ]);

                    return $this->responseToClient(['status'=>'success','message' => 'Freiend request accpeted'], 200);
                }
                else
                {
                    return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
                }

            }
            else
            {
                return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
            }

        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }


    public function FriendList(Request $request)
    {

        try {
            $userId = auth()->id();
            $friends = Friend::with('hasUser')->where('user_id',$userId)->where('status','!=','declined')->where('status','!=','pending')->get()->map(function($friend) {
                $friend->encoded_friend_id = $this->customEncryptToNumbers("$friend->friend_id", 12345);
                return $friend;
            });
            return $this->responseToClient(compact('friends'));
           // return $this->responseToClient(['status'=>'success','message' => 'Freiend request accpeted'], 200);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function DeclineFriend(Request $request)
    {

        try {

            $key = 12345;
            $encrypted_id = $request->encrypted_id;
            // $friendId = Crypt::decrypt($encrypted_id);
            if(is_numeric($encrypted_id))
            {
                $friendId = $this->customDecryptFromNumbers($encrypted_id, $key);
                if(is_numeric($friendId))
                {
                    $userId = auth()->id();
                    //  for sender
                    $attributes = [
                        'user_id' => $userId,
                        'friend_id' => $friendId,
                    ];

                    $values = [
                        'status' => 'declined',
                    ];

                    Friend::updateOrCreate($attributes, $values);


                    //    for receiver

                    $attributes = [
                        'user_id' => $friendId,
                        'friend_id' => $userId,
                    ];

                    $values = [
                        'status' => 'declined',
                    ];
                    Friend::updateOrCreate($attributes, $values);
                    return $this->responseToClient(['status'=>'success','message' => 'Freiend request declined'], 200);
                }else
                {
                    return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
                }

            }
            else
            {
                return $this->responseToClient(['status'=>'failed','message' => 'Invalid Code'], 400);
            }
           // return $this->responseToClient(['status'=>'success','message' => 'Freiend request accpeted'], 200);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

     function customEncryptToNumbers($data, $key) {
        // Generate a random salt
        $salt = random_int(10, 99); // Change this range as needed for more randomness

        // Convert the input string to a numeric form
        $numericData = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $numericData .= ord($data[$i]);
        }

        // XOR the numeric data with the key and the salt
        $encryptedData = (int)$numericData ^ $key ^ $salt;

        // Combine the salt with the encrypted data for later use in decryption
        return $salt . $encryptedData;
    }

    function customDecryptFromNumbers($encryptedData, $key) {
        // Extract the salt from the encrypted data
        $salt = substr($encryptedData, 0, 2); // Adjust based on the salt length used
        $encryptedData = substr($encryptedData, 2);

        // XOR the encrypted data with the key and the salt
        $numericData = (int)$encryptedData ^ $key ^ $salt;

        // Convert the numeric data back to the original string
        $data = '';
        while ($numericData > 0) {
            $charCode = $numericData % 100; // Adjust based on how numbers were concatenated
            $data = chr($charCode) . $data;
            $numericData = (int)($numericData / 100); // Adjust based on how numbers were concatenated
        }

        return $data;
    }

}
