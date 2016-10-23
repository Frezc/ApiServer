<?php

namespace App\Http\Controllers;

use App\Uploadfile;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Storage;

class UploadController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function uploadImage(Request $request) {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,bmp,png'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $path = 'images/' . md5(md5($content).'$'.$user->id) . '.' . $file->getClientOriginalExtension();
        Storage::put('public/' . $path, $content);
        $uploadedFile = Uploadfile::where('path', $path)->first();
        if ($uploadedFile) {
            $uploadedFile->uploader_id = $user->id;
            $uploadedFile->exist = 1;
            $uploadedFile->save();
        } else {
            Uploadfile::create([
                'path' => $path,
                'uploader_id' => $user->id
            ]);
        }

        return response()->json(['file' => $path]);
    }
}
