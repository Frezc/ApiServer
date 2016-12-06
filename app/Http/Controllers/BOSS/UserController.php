<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/10/29
 * Time: 17:20
 */

namespace App\Http\Controllers\BOSS;


use App\Exceptions\MsgException;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotifications;
use App\Models\Company;
use App\Models\CompanyApply;
use App\Models\JobEvaluate;
use App\Models\Message;
use App\Models\Order;
use App\Models\RealNameVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['updateRealNameApply', 'updateCompanyApply', 'updateRole']]);
    }

    /*
     * [GET] users
     */
    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'company_id' => 'integer',
            'role_name' => 'in:user,admin,banned',
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $q = $request->input('kw');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);
        $company_id = $request->input('company_id');
        $role_name = $request->input('role_name');

        $builder = User::search($q);

        $company_id && $builder->where('company_id', $company_id);
        if ($role_name) {
            $role = Role::where('name', $role_name)->first();
            $builder->where('role_id', $role->id);
        }

        $total = $builder->count();

        $builder->orderBy('id', $direction);
        $builder->skip($offset);
        $builder->limit($limit);

        $users = $builder->get();

        $users->each(function ($user) {
            $user->setHidden(['password']);
            $user->bindRoleName();
        });

        return response()->json(['total' => $total, 'list' => $users]);
    }

    /*
     * [GET] real_name_applies
     */
    public function getAllRealNameApplies(Request $request) {
        $this->validate($request, [
            'status' => 'integer|in:1,2,3',
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0',
            'user_id' => 'integer'
        ]);

        $status = $request->input('status', 1);
        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $user_id = $request->input('user_id');

        $builder = RealNameVerification::where('status', $status);
        if ($user_id) $builder->where('user_id', $user_id);
        $total = $builder->count();
        $builder->orderBy('id', 'desc')
                ->skip($offset)
                ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }

    /*
     * [GET] company_applies
     */
    public function getAllCompanyApplies(Request $request) {
        $this->validate($request, [
            'status' => 'integer|in:1,2,3',
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0',
            'user_id' => 'integer'
        ]);

        $status = $request->input('status', 1);
        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $user_id = $request->input('user_id');

        $builder = CompanyApply::where('status', $status);
        if ($user_id) $builder->where('user_id', $user_id);
        $total = $builder->count();
        $builder->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }

    /*
     * [GET] orders
     */
    public function getOrders(Request $request) {
        $this->validate($request, [
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0',
            'user_id' => 'integer'
        ]);

        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $user_id = $request->input('user_id');

        $builder = Order::query();
        if ($user_id) $builder->where('applicant_id', $user_id)->orWhere('recruiter_id', $user_id);
        $total = $builder->count();
        $builder->orderBy('created_at', 'desc')
            ->skip($offset)
            ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }

    /*
     * [POST] real_name_applies/{id}
     */
    public function updateRealNameApply(Request $request, $id) {
        $rna = RealNameVerification::findOrFail($id);
        $this->validate($request, [
            'action' => 'required|in:acc,rej',
            'reason' => 'string'
        ]);

        $action = $request->input('action');
        $reason = $request->input('reason');

        if ($rna->status != 1) {
            throw new MsgException('You can\'t update this apply.', 400);
        }

        if ($action == 'acc') {
            $rna->status = 2;
            $user = User::find($rna->user_id);
            if ($user) {
                $user->real_name_verified = 1;
                $user->save();
            }
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $user->id, '您的实名认证已通过。'));
        } else {
            $rna->status = 3;
            $rna->reason = $reason;
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $rna->user_id, '您的实名认证已被拒绝。理由为 ' . $reason));
        }
        $rna->save();

        return response()->json($rna);
    }

    /*
     * [POST] company_applies/{id}
     */
    public function updateCompanyApply(Request $request, $id) {
        $ca = CompanyApply::findOrFail($id);
        $this->validate($request, [
            'action' => 'required|in:acc,rej',
            'reason' => 'string'
        ]);

        $action = $request->input('action');
        $reason = $request->input('reason');

        if ($ca->status != 1) {
            throw new MsgException('You can\'t update this apply.', 400);
        }

        if ($action == 'acc') {
            $ca->status = 2;
            $company = Company::create(array_only($ca->toArray(), [
                'name', 'url', 'address', 'logo', 'description', 'contact_person', 'contact',
                'business_license'
            ]));
            $user = User::find($ca->user_id);
            if ($user) {
                $user->company_id = $company->id;
                $user->company_name = $company->name;
                $user->save();
            }
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $user->id, '您的企业认证已通过。'));
        } else {
            $ca->status = 3;
            $ca->reason = $reason;
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $ca->user_id, '您的企业认证已被拒绝。理由为 ' . $reason));
        }

        $ca->save();
        return response()->json($ca);
    }

    /*
     * [POST] users/{id}/role
     */
    public function updateRole(Request $request, $id) {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'role' => 'required|in:user,banned'
        ]);
        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->first();
        $user->role_id = $role->id;
        $user->save();
        $user->bindRoleName();
        return response()->json($user);
    }

    /*
     * [GET] evaluates
     */
    public function getEvaluates(Request $request) {
        $this->validate($request, [
            'user_id' => 'integer|exists:users,id',
            'job_id' => 'integer|exists:tjz_jobs,id',
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0'
        ]);

        $user_id = $request->input('user_id');
        $job_id = $request->input('job_id');
        $off = $request->input('off');
        $siz = $request->input('siz');

        $builder = JobEvaluate::query();
        $builder->when($user_id, function ($query) use ($user_id) {
            return $query->where('user_id', $user_id);
        })->when($job_id, function ($query) use ($job_id) {
            return $query->where('job_id', $job_id);
        })->orderBy('id', 'desc');

        $total = $builder->count();
        $list = $builder->skip($off)->limit($siz)->get()->each(function ($item) {
            $item->setHidden(['updated_at']);
        });
        return response()->json(['total' => $total, 'list' => $list]);
    }
}