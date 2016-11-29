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
use App\Models\Company;
use App\Models\CompanyApply;
use App\Models\Order;
use App\Models\RealNameVerification;
use App\Models\User;
use App\Models\Uploadfile;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['updateRealNameApply', 'updateCompanyApply']]);
    }

    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'company_id' => 'integer',
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $q = $request->input('kw', '');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);
        $company_id = $request->input('company_id');

        $q_array = $q ? explode(" ", trim($q)) : [];

        if ($company_id) {
            $company = Company::find($company_id);
            if (!$company) return response()->json(['total' => 0, 'list' => []]);
            $builder = $company->users();
        } else {
            $builder = User::query();
        }

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
        $builder->orderBy('created_at', 'desc')
                ->skip($offset)
                ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }

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
        $builder->orderBy('created_at', 'desc')
            ->skip($offset)
            ->limit($size);
        $result = $builder->get();
        return response()->json(['total' => $total, 'list' => $result]);
    }

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

        $rna->status = $action == 'acc' ? 2 : 3;
        if ($action == 'rej') $rna->reason = $reason;
        $rna->save();

        return response()->json($rna);
    }

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
            Company::create(array_only($ca->toArray(), [
                'name', 'url', 'address', 'logo', 'description', 'contact_person', 'contact',
                'business_license'
            ]));
        } else {
            $ca->status = 3;
            $ca->reason = $reason;
        }

        $ca->save();
        return response()->json($ca);
    }
}