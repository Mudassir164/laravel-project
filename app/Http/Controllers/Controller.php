<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected function responseToClient(array $response, $statusCode = 200)
    {
        if (!isset($response['message']))
            $response['message'] = 'success';
        return response()->json($response, $statusCode);
    }

    protected function uploadImage($file, $dir)
    {
        $fileName = rand('00000', '99999') . '_' . time() . '.' . $file->getClientOriginalExtension();
        $uploaded = Storage::putFileAs($dir, $file, $fileName);
        return $uploaded;
    }
}
