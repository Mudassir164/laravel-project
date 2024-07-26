<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreRewardRequest;
use App\Models\Reward;
use App\Models\UserReward;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function App\Helpers\getAllWebUserIds;

class RewardController extends Controller
{
    private $reward;
    private $userReward;

    public function __construct(Reward $reward, UserReward $userReward)
    {
        $this->reward = $reward;
        $this->userReward = $userReward;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $data = $this->reward->getAllRewards(
                [
                    'web_user_id' => auth()->user()->id
                ],
                $request
            )->toArray();

            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
            return $this->responseToClient($set);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function allActive()
    {
        try {
            $data = $this->reward->getAllActiveRewards(
                [
                    'web_user_id' => auth()->user()->parent_id ?? auth()->user()->id
                ]
            )->toArray();
            return $this->responseToClient(compact('data'));
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
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
    public function store(StoreRewardRequest $request)
    {
        try {
            $data = $request->only(['title', 'status']);
            $data['web_user_id'] = auth()->user()->id;

            if ($request->has('attachment') && $request->attachment) {
                $image = $this->uploadImage($request->file('attachment'), 'rewards');
                $data['attachment'] = $image;
            }
            $data = $this->reward->storeReward($data);
            $response = compact('data');
            $response['message'] = 'Reward created successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = $this->reward->findReward($id);
            return $this->responseToClient(compact('data'));
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function edit(Reward $reward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $reward = $this->reward->findReward($id);
            if ($reward) {
                $data = $request->only(['title', 'status']);
                if ($request->has('attachment') && $request->attachment) {
                    if ($reward->attachment) {
                        Storage::delete($reward->attachment);
                    }
                    $image = $this->uploadImage($request->file('attachment'), 'rewards');
                    $data['attachment'] = $image;
                }
                $this->reward->updateReward($id, $data);
                $data = $this->reward->findReward($id);
                return $this->responseToClient(compact('data'));
            }
            $response['message'] = 'Not Found!';
            return $this->responseToClient($response, 404);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $reward = $this->reward->findReward($id);
            if ($reward) {
                $reward->delete();
                $response['message'] = 'Reward deleted successfully!';
                return $this->responseToClient($response);
            }
            $response['message'] = 'Not Found!';
            return $this->responseToClient($response, 404);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function statusUpdate($id)
    {
        try {
            $data = $this->reward->findReward($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'Reward not found!'], 404);
            }
            $this->reward->updateReward($id, ['status' => $data->status ? 0 : 1]);
            $data = $this->reward->findReward($id);
            $response = compact('data');
            $response['message'] = 'Status changed successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function userRewardRedeem($id)
    {
        try {
            $data = $this->userReward->findUserReward($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'User Reward not found!'], 404);
            }
            $this->userReward->updateUserReward($id, ['is_redeem' => 1]);
            $data = $this->reward->findReward($id);
            $response = compact('data');
            $response['message'] = 'Reward Redeem successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function userRewardList(Request $request)
    {
        try {
            $data = $this->userReward->getAllRewards(
                [
                    'web_user_id' => auth()->user()->id
                ],
                $request
            )->toArray();
            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
            return $this->responseToClient($set);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }
}
