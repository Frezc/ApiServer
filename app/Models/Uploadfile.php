<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Uploadfile extends Model
{
    protected $table = 'uploadfiles';
    protected $guarded = ['id'];

    public function replace($oldPath = null) {
        if ($oldPath != $this->path) {
            // 被替换的文件存在时
            if ($oldPath) {
                $old = Uploadfile::where('path', $oldPath)->first();
                // 被使用次数减一
                $old->used--;
                $old->save();
            }
            // 当前文件的被使用次数加一
            $this->used++;
            $this->save();
        }
    }

    public function makeSureAccess($user) {
        return $user->checkAccess($this->uploader_id);
    }

    public static function convertToUrl($path) {
        if ($path)
            return asset(Storage::url($path));
        return $path;
    }
}
