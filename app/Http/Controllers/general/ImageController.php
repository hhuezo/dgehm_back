<?php

namespace App\Http\Controllers\general;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function getGeneralImage($imgName)
    {
        $imgRoute   = storage_path('app/public/general/' . $imgName);

        $exists = file_exists($imgRoute);

        if (!$exists) abort(404);

        return response()->file($imgRoute);
    }
}
