<?php

namespace App\Http\Controllers\Web;

use App\Models\{
    Sponsorship,
    SportEvent,
    Game,
    Notification
};
use App\Models\WebUser;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\{
    AddSponsorshipRequest,
};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SponsorController extends Controller
{
    private $sponsorship;
    private $game;
    private $sportEvent;


    public function __construct(Sponsorship $sponsorship, Game $game, SportEvent $sportEvent)
    {
        $this->sponsorship = $sponsorship;
        $this->game = $game;
        $this->sportEvent = $sportEvent;
    }
    public function addSponsorship(AddSponsorshipRequest $request)
    {
        try {
            $gameId = $request->game_id;
            $sponsorId = auth()->id();

            $gameData = $this->game->find($gameId);
            if (!$gameData) {
                return $this->responseToClient(['message' => 'Game not found.'], 404);
            }

            $sportEventId = $gameData->sport_event_id;
            $sportEvent = $this->sportEvent->find($sportEventId);

            if (!$sportEvent) {
                return $this->responseToClient(['message' => 'Sport Event not found.'], 404);
            }

            $sportEventObj = $sportEvent->sport_event_obj;


            if (!isset($sportEventObj['sport_event']['start_time'])) {
                return $this->responseToClient(['message' => 'Start time not found in Sport Event object.'], 400);
            }

            $dateTimeString = $sportEventObj['sport_event']['start_time'];


            $startTime = Carbon::parse($dateTimeString);
            $currentTime = Carbon::now();

            if ($currentTime->greaterThanOrEqualTo($startTime)) {
                return $this->responseToClient(['message' => 'Sorry, the Sport Event has been completed.'], 400);
            }

            // Count active sponsorships
            $countSponsorships = $this->sponsorship
                ->where('game_id', $gameId)
                ->where('status', 1)
                ->count();

            if ($countSponsorships >= 3) {
                return $this->responseToClient(['message' => 'Sponsorship limit reached.'], 400);
            }

            // Prepare details for the new sponsorship
            $details = $request->all();
            $data = $this->sponsorship->addSponsorship($details, $startTime->format('F d, Y'), $sponsorId);

            // Return success response
            $response = [
                'data' => $data,
                'message' => 'Sponsorship added successfully!'
            ];

            // Sponsorship Request Push Notification Send To Game Creator (Pubowner/Organizer) 
            $notification = new Notification();

            $webUserId = $gameData->web_user_id;
            $webUser = WebUser::find($webUserId); // game creator details
            $sponsor = WebUser::find($sponsorId); // sponsor details
            $route = $request->route()->uri();

            if ($webUser->device_token) {
                $notification->storeNotification([
                    'ref' => get_class($webUser),
                    'title' => 'Sponsorship Request',
                    'identifier' => 'sponsership_request',
                    'route_name' => $route,
                    'ref_id' => $data['id'],
                    'sender_id' => $sponsorId,
                    'receiver_id' => $webUserId,
                    'replacers' => '[' . $webUser->name . ',' . $sponsor->name . ']',
                ]);
            }

            return $this->responseToClient($response);
        } catch (Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }
    public function sponsorshipDetails($id)
    {
        try {
            $limit =
                $data = $this->sponsorship->where('id', $id)->first();
            $response = ['data' => $data, 'message' => 'Sponsorship is showing Successfully!'];
            return $this->responseToClient($response);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function showSponsorships(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $search = $request->get('search');
            $webUserId = auth()->id();

            $query = $this->sponsorship->where('web_user_id', $webUserId);

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            $data = $query->orderBy('created_at', 'desc')->paginate($limit);

            if ($data->isEmpty()) {
                $response = ['data' => [], 'message' => 'No Sponsorships found'];
                return $this->responseToClient($response);
            }

            $response = ['data' => $data->items(), 'page' => $data->toArray(), 'message' => 'Sponsorships are showing Successfully!'];
            return $this->responseToClient($response);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function updateSponsorship(AddSponsorshipRequest $request, $id)
    {
        try {
            $details = $request->all();
            $data = $this->sponsorship->updateSponsorship($id, $details);

            if ($data) {
                $response = [
                    'data' => $data,
                    'message' => 'Sponsorship Updated Successfully!'
                ];
                return $this->responseToClient($response);
            } else {
                return $this->responseToClient(['message' => 'Sponsorship Not Found'], 404);
            }
        } catch (Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }
    public function deleteSponsorship($id)
    {
        try {
            $this->sponsorship->where('id', $id)->delete();

            $response = [
                'message' => 'Sponsorship Deleted Successfully!'
            ];

            return $this->responseToClient($response);
        } catch (Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }
    public function gameSponsorships($id, Request $request)
    {
        try {
            $status = $request->input('status');
            $limit = $request->input('limit', 10);
            $search = $request->get('search');

            $sponsorshipsQuery = $this->sponsorship->where('game_id', $id)
                ->when($status !== null, function ($query) use ($status) {
                    return $query->where('status', $status);
                });

            if ($search) {
                $sponsorshipsQuery->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('sponsor_name', 'like', '%' . $search . '%');
                });
            }

            // Paginate the results
            $sponsorships = $sponsorshipsQuery->orderBy('created_at', 'desc')->paginate($limit);

            $data = $sponsorships->items();
            $page = $sponsorships->toArray();
            unset($page['data']);

            $response = compact('data', 'page');
            $response['message'] = 'Sponsorships are showing Successfully!';

            // Return the response to the client
            return $this->responseToClient($response);
        } catch (Exception $th) {
            // Handle any exceptions and return an error response
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function sponsorshipStatusUpdate(Request $request, $id)
    {
        try {
            $validate = $request->validate([
                'status' => 'required',
            ]);

            $status = $request->status;

            $data = $this->sponsorship->find($id);

            $sponsorId = $data->web_user_id;

            $sponsor = WebUser::find($sponsorId); // Sponsor details

            $webUserId = auth()->id();

            $webUser = WebUser::find($webUserId); // Pubowner or Organizer details


            $gameId = $data->game_id;
            $statusActive = $this->sponsorship->where('game_id', $gameId)->where('status', 1)->count();
            if ($statusActive >= 3 && $status == 1) {
                return $this->responseToClient(['message' => 'Sponsorship Request Accept Limit Has Exceed.'], 404);
            }

            $data = $this->sponsorship->sponsorshipStatusUpdate($id, $status);


            $notification = new Notification();
            $route = $request->route()->uri();

            if ($data) {
                $response = [
                    'data' => $data,
                    'message' => 'Status Updated Successfully!'
                ];

                if ($data['status'] === '1') {
                    if ($sponsor->device_token) {
                        $notification->storeNotification([
                            'ref' => get_class($sponsor),
                            'title' => 'Sponsorship Request Accepted',
                            'identifier' => 'sponsership_accepted',
                            'route_name' => $route,
                            'ref_id' => $data['id'],
                            'sender_id' => $webUserId,
                            'receiver_id' => $sponsorId,
                            'replacers' => '[' . $sponsor->name . ']',
                        ]);
                    }
                } else {
                    if ($sponsor->device_token) {
                        $notification->storeNotification([
                            'ref' => get_class($sponsor),
                            'title' => 'Sponsorship Request Rejected',
                            'identifier' => 'sponsership_rejected',
                            'route_name' => $route,
                            'ref_id' => $data['id'],
                            'sender_id' => $webUserId,
                            'receiver_id' => $sponsorId,
                            'replacers' => '[' . $sponsor->name . ']',
                        ]);
                    }
                }

                return $this->responseToClient($response);

            } else {
                return $this->responseToClient(['message' => 'Status NOt Updated'], 404);
            }
        } catch (Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }
    public function sponsorRequests(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $search = $request->get('search');
            $id = auth()->id();

            $gamesQuery = $this->game
                ->where('web_user_id', $id)
                ->join('game_types', 'games.game_type_id', '=', 'game_types.id')
                ->select('games.*', 'game_types.name as game_type_name');

            if ($search) {
                $gamesQuery->where('games.name', 'like', '%' . $search . '%');
            }

            $games = $gamesQuery->paginate($limit);

            $games->getCollection()->transform(function ($game) {
                $game->no_of_request = Sponsorship::where('game_id', $game->id)->count();
                return $game;
            });

            $data = $games->items();
            $page = $games->toArray();
            unset($page['data']);

            return $this->responseToClient(compact('data', 'page'));
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}