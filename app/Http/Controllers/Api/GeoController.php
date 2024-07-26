<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{City, Country};
use Illuminate\Http\Request;

class GeoController extends Controller
{
    private $city, $country;
    public function __construct()
    {
        $this->city = new City();
        $this->country = new Country();
    }

    public function getCountries(Request $request)
    {
        try {
            $search = $request->get('search') ?? false;
            $data = $this->country;
            $data = $data->when($search, function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
            if($request->has('type') && $request->type == 'web'){
                $data =  $data->orderby('name')->paginate(50)->toArray(); 
                $set['data'] = $data['data'];
                unset($data['data']);
                $set['page'] = $data;
            }else{
                $set['data'] = $data->orderby('name')->get()->toArray();
            }
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function getCities($id)
    {
        try {
            $exists = $this->country::where('id', $id)->exists();
            if (!$exists)
                throw new \Exception('invalid id');
            $data = $this->city->where('country_id', $id)->get();
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

}




