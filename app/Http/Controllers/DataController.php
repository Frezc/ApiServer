<?php

namespace App\Http\Controllers;

use App\Models\Data;
use App\Models\JobType;
use Illuminate\Http\Request;

use App\Http\Requests;

class DataController extends Controller
{
    /*
     * [GET] banners
     */
    public function getBanners() {
        $data = Data::where('key', 'banners')->first();
        if ($data) $result = json_decode($data->value);
        else $result = [];
        return response()->json($result);
    }

    /*
     * [GET] job_types
     */
    public function getJobTypes() {
        $types = JobType::all()->map(function ($item) {
            return $item->name;
        });
        return response()->json($types);
    }
}
