<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SportEvent;
use Illuminate\Http\Request;

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
    
}
