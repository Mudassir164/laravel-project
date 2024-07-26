<?php   namespace App\Libraries;
use Google\Client;
class FcmAuthToken
{

    public function getOAuthToken()
    {
        try { //Send CURL Request to API

            $client = new Client();
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . base_path('play-active-as-firebase-token.json'));
            $client->useApplicationDefaultCredentials();
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            // Fetch token with assertion; the client will handle token refreshing
            $client->fetchAccessTokenWithAssertion();

            return $client->getAccessToken()['access_token'];


        } catch (\Exception $e) {
            //handle exception
            throw new \Exception($e->getMessage());
        }
    }

}
