<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Models\UserLeaderboard;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserLeaderboardController extends Controller
{
    private $userLeaderboard;
    public $user;

    public function __construct(UserLeaderboard $userLeaderboard, User $user)
    {
        $this->userLeaderboard  =   $userLeaderboard;
        $this->user             =   $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $data   =   $this->userLeaderboard->getAllLeaderboard($request);
            return $this->responseToClient($data);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }

    public function eventLeaderboard($id,Request $request)
    {
        try {
            $data   =   $this->user->getLeaderboardByGame($id,$request);
            return $this->responseToClient($data);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseToClient(['message' => $e->getMessage()], 400);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserLeaderboard  $userLeaderboard
     * @return \Illuminate\Http\Response
     */
    public function show(UserLeaderboard $userLeaderboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserLeaderboard  $userLeaderboard
     * @return \Illuminate\Http\Response
     */
    public function edit(UserLeaderboard $userLeaderboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserLeaderboard  $userLeaderboard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserLeaderboard $userLeaderboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserLeaderboard  $userLeaderboard
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserLeaderboard $userLeaderboard)
    {
        //
    }
}
