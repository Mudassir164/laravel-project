<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CreateOrganizerRequest;
use App\Models\Role;
use App\Models\{
    Game,
    WebUser,
};
use Exception;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{

    private $webUser;

    public function __construct(WebUser $webUser)
    {
        $this->webUser  =   $webUser;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request)
    {
        try {
            $limit  =   $request->limit ?? 10;
            $data   =   $this->webUser->getAllOrganizer(
                [
                    'parent_id' => auth()->id()
                ],
                $request->all()
            )
                ->paginate($limit)->toArray();
            $data['key'] = $request->get('key');
            $data['order'] = $request->get('order');
            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
            return $this->responseToClient($set);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateOrganizerRequest $request)
    {
        try {
            $data = $this->webUser->registerOrganizer($request);
            $response = compact('data');
            $response['message'] = 'Organizer created successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = $this->webUser->getFullUserData($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'User not found!'], 404);
            }
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $response   =   $this->webUser->updateOrganizer($id, $request);
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            $statusCode = $exception->getCode() ? $exception->getCode() : 400;
            return $this->responseToClient(['message' => $exception->getMessage()], $statusCode);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $response   =   $this->webUser->deleteOrganizer($id);
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            $statusCode = $exception->getCode() ? $exception->getCode() : 400;
            return $this->responseToClient(['message' => $exception->getMessage()], $statusCode);
        }
    }

    public function gameDetails($id){
        try {
            $data = Game::with(['questions'])->where('id',$id)->first();
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function statusUpdate($id)
    {
        try {
            $data = $this->webUser->getFullUserData($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'Organizer not found!'], 404);
            }
            $this->webUser->updateOrganizerStatus($id, ['status' => $data->status ? 0 : 1]);
            $data = $this->webUser->getFullUserData($id);
            $response = compact('data');
            $response['message'] = 'Status changed successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }
}
