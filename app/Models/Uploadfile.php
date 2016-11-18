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
        return $user->checkAccess($this->uploader_id);
    }

    public static function convertToUrl($path) {
        if ($path)
            return asset(Storage::url($path));
        return $path;
    }
}
