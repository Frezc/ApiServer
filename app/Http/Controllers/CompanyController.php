<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\PushNotifications;
use App\Models\Company;
use App\Models\CompanyApply;
use App\Models\Job;
use App\Models\JobTime;
use App\Models\Message;
use App\Models\Uploadfile;
use App\Models\UserCompany;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

class CompanyController extends Controller {

    function __construct(){

        $this->middleware('jwt.auth',['only'=>[ 'getApply', 'postApply', 'update', 'addUser', 'unlink']]);
        $this->middleware('log', ['only' => ['postApply', 'update',  'addUser', 'unlink']]);
        $this->middleware('role:user', ['only' => [ 'postApply', 'update', 'addUser', 'unlink']]);
    }

    /*
     * [GET] companies/{id}
     */
    public function get($id) {
        return response()->json(Company::findOrFail($id));
    }

    /*
     * [GET] releaseJob
     */

    /*
     * [GET] companies
     */
    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $keywords = $request->input('kw');
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);
        $builder = Company::search($keywords);

        $total = $builder->count();

        //排列a
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    /*
     * [GET] companies/apply
     */
    public function getApply(Request $request) {
        $this->validate($request, [
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0'
        ]);

        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $self = JWTAuth::parseToken()->authenticate();

        $builder = CompanyApply::where('user_id', $self->id)
            ->orderBy('updated_at', 'desc');
        $total = $builder->count();
        $list = $builder->skip($offset)->limit($size)->get();

        return response()->json(['total' => $total, 'list' => $list]);
    }

    /*
     * [POST] companies/apply
     */
    public function postApply(Request $request) {
        $this->validate($request, [
            'name' => 'required|between:1,50',
            'url' => 'string',
            'address' => 'required|string',
            'logo' => 'exists:uploadfiles,path',
            'description' => 'string',
            'contact_person' => 'required|max:16',
            'contact' => 'required|max:50',
            'business_license' => 'required|exists:uploadfiles,path'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $logo = $request->input('logo');
        if ($logo) {
            $uploadFile = Uploadfile::where('path', $logo)->first();
            $uploadFile->makeSureAccess($self);
        }
        $business_license = $request->input('business_license');
        $uploadFile = Uploadfile::where('path', $business_license)->first();
        $uploadFile->makeSureAccess($self);

        $params = array_only($request->all(), ['name', 'url', 'address', 'logo',
            'description', 'contact_person', 'contact', 'business_license']);
        $params['user_id'] = $self->id;
        $params['user_name'] = $self->nickname;
        $params['status'] = 1;
        $companyApply = CompanyApply::create($params);

        return response()->json($companyApply);
    }

    /*
     * [POST] companies/{id}
     */
    public function update(Request $request, $id) {
        $company = Company::findOrFail($id);
        $this->validate($request, [
            'url' => 'string',
            'address' => 'string',
            'logo' => 'exists:uploadfiles,path',
            'description' => 'string',
            'contact_person' => 'max:16',
            'contact' => 'max:50'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $company->makeSureAccess($self);
        $company->update(array_only($request->all(), ['url', 'address', 'logo', 'description', 'contact_person', 'contact']));
        return response()->json($company);
    }

    /*
     * [POST] companies/{id}/users
     */
    public function addUser(Request $request, $id) {
        $company = Company::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $company->makeSureAccess($self);
        $this->validate($request, [
            'user_id' => 'exists:users,id'
        ]);

        $user = User::find($request->input('user_id'));
        if ($user->company_id) {
            throw new MsgException('你不能添加已经有企业的用户了', 400);
        }

        $user->company_id = $company->id;
        $user->company_name = $company->name;
        $user->save();

        $this->dispatch(
            new PushNotifications(Message::getSender(Message::$NOTI_HELPER), $user->id,
                '你被用户 ' . $self->nickname . ' 添加进了企业 ' . $company->name));

        return '添加成功';
    }

    /*
     * [POST] unlink_company
     */
    public function unlink() {
        $self = JWTAuth::parseToken()->authenticate();
        if (!$self->company_id) {
            throw new MsgException('你没有从属的企业。', 400);
        }
        $self->company_id = null;
        $self->company_name = null;
        $self->save();

        return '移除成功';
    }


}
