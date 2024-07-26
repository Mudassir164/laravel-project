<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\SportEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Exception;

class SportEventController extends Controller
{
    private $sportEvent;

    public function __construct(SportEvent $sportEvent)
    {
        $this->sportEvent = $sportEvent;
    }

    public function list(Request $request)
    {
        try {
            $data = $this->sportEvent->list($request);
            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
            return $this->responseToClient($set);
        }catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function sportEvents(Request $request)
    {
        try {
            $data = $this->sportEvent->getEvents($request);
            return $this->responseToClient($data);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    
}
