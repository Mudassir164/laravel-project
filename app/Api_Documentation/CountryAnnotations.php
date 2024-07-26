<?php

namespace App\Api_Documentation;

class CountryAnnotations {
        /** 
        *@OA\Info(
        *      version="1.0.0",
        *      title="Play Active As",
        * )
        *
        * @OA\Get(
        *     path="/api/countries",
        *     summary="Get list of countries",
        *     description="Returns a list of all countries.",
        *     tags={"Country"},
        *     @OA\Response(
        *         response=200,
        *         description="List of countries",
        *         @OA\JsonContent()
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Countries not found"
        *     ),
        *     security={}
        * )
        *
        * @OA\Get(
        *     path="/api/cities/{id}",
        *     summary="Get cities by country ID",
        *     description="Returns a list of cities based on the country ID.",
        *     tags={"Country"},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         description="ID of the country",
        *         required=true,
        *         @OA\Schema(type="integer")
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="List of cities",
        *         @OA\JsonContent()
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Country not found"
        *     ),
        *     security={}
        * )
        */

public function country(){}

}
        