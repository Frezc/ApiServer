<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

class OrderController extends Controller
{
    public function get(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0',
            'status' => 'in:0,1,2,3',
            'applicant_check' => 'in:0,1',
            'recruiter_check' => 'in:0,1',
            'role' => 'in:applicant,recruiter'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);

        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'created_at');
        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $status = $request->input('status', -1);
        $applicant_check = $request->input('applicant_check', -1);
        $recruiter_check = $request->input('recruiter_check', -1);
        $role = $request->input('role', 'applicant');

        $builder = Order::query()
            ->when($status >= 0, function ($query) use ($status) {
                return $query->where('status', $status);
            })->when($applicant_check >= 0, function ($query) use ($applicant_check) {
                return $query->where('applicant_check', $applicant_check);
            })->when($recruiter_check >= 0, function ($query) use ($recruiter_check) {
                return $query->where('recruiter_check', $recruiter_check);
            })->when($role, function ($query) use ($role, $id) {
                return $query->where($role . '_id', $id);
            });
        $total = $builder->count();

        $builder
            ->orderBy($orderby, $direction)
            ->skip($offset)
            ->limit($limit);
        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }
}
