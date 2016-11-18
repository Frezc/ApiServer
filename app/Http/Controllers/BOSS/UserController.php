<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/10/29
 * Time: 17:20
 */

namespace App\Http\Controllers\BOSS;


use App\Http\Controllers\Controller;
use App\Models\RealNameVerification;
use App\Models\User;
use App\Models\Uploadfile;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $q = $request->input('kw', '');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);

        $q_array = $q ? explode(" ", trim($q)) : [];
        $builder = User::query();
        foreach ($q_array as $qi) {
            $builder->where(function ($query) use ($qi) {
                $query->orWhere('nickname', 'like', '%' . $qi . '%')
                    ->orWhere('email', 'like', '%' . $qi . '%');
            });
        }

        $total = $builder->count();

        $builder->orderBy('id', $direction);
        $builder->skip($offset);
        $builder->limit($limit);

        $users = $builder->get();

        $users->each(function ($user) {
            $user->setHidden(['password']);
        });

        return response()->json(['total' => $total, 'list' => $users]);
    }

    public function getAllRealNameApplies(Request $request) {
        $this->validate($request, [
            'examine_status' => 'integer|in:0,1,2',
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0',
            'user_id' => 'integer'
        ]);

        $status = $request->input('examine_status', 0);
        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $user_id = $request->input('user_id');

        $builder = RealNameVerification::where('is_examined', $status);
        if ($user_id) $builder->where('user_id', $user_id);
        $total = $builder->count();
        $builder->orderBy('created_at', 'desc')
                ->skip($offset)
                ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }
}