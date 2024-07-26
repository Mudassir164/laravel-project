<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GameFeedbackRequest;
use App\Models\GameFeedback;
use Illuminate\Http\Request;

class GameFeedbackController extends Controller
{

    private $gameFeedback;

    public function __construct(GameFeedback $gameFeedback)
    {
        $this->gameFeedback =   $gameFeedback;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(GameFeedbackRequest $request)
    {
        try {
            $request->merge(['user_id' => auth()->user()->id]);
            $data    =   $this->gameFeedback->storeGameFeedback($request);
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GameFeedback  $gameFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(GameFeedback $gameFeedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GameFeedback  $gameFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(GameFeedback $gameFeedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GameFeedback  $gameFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GameFeedback $gameFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GameFeedback  $gameFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(GameFeedback $gameFeedback)
    {
        //
    }
}
