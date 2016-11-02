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
        if ($oldPath) {
            $old = Uploadfile::where('path', $oldPath)->first();
            $old->used--;
            $old->save();
        }
        $this->used++;
        $this->save();
    }

    public function makeSureAccess($user) {
        if ($this->uploader_id != $user->id) {
            /* throw */
            throw new MsgException('你没有权限访问此图片', 401);
        }
        return true;
    }

    public static function convertToUrl($path) {
        if ($path)
            return asset(Storage::url($path));
        return $path;
    }
}
