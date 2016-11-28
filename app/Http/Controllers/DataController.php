<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;

use App\Http\Requests;

class DataController extends Controller
{
    public function getBanners() {
        $data = Data::where('key', 'banners')->first();
        if ($data) $result = json_decode($data->value);
        else $result = [];
        return response()->json($result);
    }
}
