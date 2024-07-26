<?php   namespace App\Libraries;

class PushNotification
{

    public static function send($post_data)
    {
        try {
            $Fcm = new FcmAuthToken();
            $bearer_token = $Fcm->getOAuthToken();
            //Send CURL Request to API
            $apiURL = env('FCM_SERVER_URL');
            
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bearer '.$bearer_token;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //Send the request
            $response = curl_exec($ch);

            //Close request
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }

            curl_close($ch);

        } catch (\Exception $e) {
            //handle exception
            throw new \Exception($e->getMessage());
        }
    }

}
