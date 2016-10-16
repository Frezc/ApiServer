<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Hash;
use Storage;
use App\Uploadfile;
use JWTAuth;

class UploadController extends Controller
{
	public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function uploadImage(Request $request) {
    	$this->validate($request, [
    		'file' => 'required|mimes:jpeg,bmp,png'
    	]);

    	$file = $request->file('file');
    	$content = file_get_contents($file->getRealPath());
    	$path = 'images/'.md5($content).'.'.$file->getClientOriginalExtension();
    	Storage::put('public/'.$path, $content);
    	Uploadfile::create([
    		'path' => $path,
    		'uploader_id' => JWTAuth::parseToken()->authenticate()->id
    	]);
    	return asset(Storage::url($path));
    }
}
