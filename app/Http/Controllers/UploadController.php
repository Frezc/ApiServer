<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Uploadfile;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Storage;

class UploadController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('log', ['only' => ['uploadImage']]);
    }

    /*
     * [POST] upload/image
     */
    public function uploadImage(Request $request) {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,bmp,png'   // 验证文件类型
        ]);
        $user = JWTAuth::parseToken()->authenticate();
        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        // 重命名
        $path = 'images/' . md5(md5($content).'$'.$user->id) . '.'
            . $file->getClientOriginalExtension();
        // 保存
        Storage::put('public/' . $path, $content);
        $path = Storage::url($path);
        $uploadedFile = Uploadfile::where('path', $path)->first();
        if ($uploadedFile) {
            // 数据中有该文件的记录
            $uploadedFile->exist = 1;
            $uploadedFile->save();
        } else {
            // 没有则新建
            Uploadfile::create([
                'path' => $path,
                'uploader_id' => $user->id
            ]);
        }
        return $path;
    }
}
