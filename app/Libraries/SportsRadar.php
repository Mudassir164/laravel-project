<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class SportsRadar
{
    private $client, $api_key, $config;

    public function __construct()
    {
        $this->api_key = env('SPORTRADAR_API_KEY');
        $path_keys = "trial/v4/en/";
        $this->client = new Client([
            'base_uri' => env('SPORTRADAR_SOCCER_API', 'https://api.sportradar.com/soccer/') . $path_keys,
        ]);
        $this->config = [
            'headers' => [
                'accept' => 'application/json',
            ],
        ];
    }

    public function seasons(Request $request)
    {
        try {
            $query['api_key'] = $this->api_key;
            $api = 'seasons.json';
            $params = '?' . http_build_query($query);
            $endpoint = $api . $params;
            $response = $this->client->get($endpoint, $this->config);
            $content = $response->getBody()->getContents();
            return $content;
        } catch (GuzzleException $th) {
            throw $th;
        }
    }

    public function seasonSchedule($seasonID, Request $request)
    {
        try {
            if ($request->has('start')) $query['start'] = $request->get('start');
            if ($request->has('limit')) $query['limit'] = $request->get('limit');
            $query['api_key'] = $this->api_key;
            $api = 'seasons/' . $seasonID . '/schedules.json';
            $params = '?' . http_build_query($query);
            $endpoint = $api . $params;
            $response = $this->client->get($endpoint, $this->config);
            $content = $response->getBody()->getContents();
            return $content;
        } catch (GuzzleException $th) {
            throw $th;
        }
    }

    public function eventLineUp($eventID)
    {
        try {
            $query['api_key'] = $this->api_key;
            $api = 'sport_events/' . $eventID . '/lineups.json';
            $params = '?' . http_build_query($query);
            $endpoint = $api . $params;
            $response = $this->client->get($endpoint, $this->config);
            $content = $response->getBody()->getContents();
            return $content;
        } catch (GuzzleException $th) {
            throw $th;
        }
    }
}
