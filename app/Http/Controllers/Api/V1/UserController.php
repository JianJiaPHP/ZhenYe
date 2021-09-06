<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserAddressRequests;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\IFTTTHandler;


class UserController extends Controller
{
    /**
     * 用户收货地址列表
     */
    public function address_list()
    {
        $userid = auth('api')->id();
        $list = Address::query()->where(['user_id'=>$userid])->paginate(request('limit',15))->toArray();
        return success($list);
    }

    /**
     * 新增收货地址
     */
    public function address_add(UserAddressRequests $addressRequests)
    {
        $params = $addressRequests->validated();
        $userid = auth('api')->id();
        if ($params['status'] == 1){
            $res = Address::query()->where(['user_id'=>$userid,'status'=>1])->update(['status'=>2]);//原来的默认设置为不默认
        }
        $params['user_id'] = $userid;
        $res = Address::query()->create($params);
        if (!$res){
            return fail('提交失败');
        }
        return success($res);
    }

    /**
     * 删除收货地址
     */
    public function address_del(){
        $params = request()->all();
        if (empty($params['id'])){
            return fail("提交失败");
        }
        $res = Address::query()->where(['id'=>$params['id']])->delete();
        return choose($res);
    }

    /**
     * 修改收货地址
     */
    public function address_update(UserAddressRequests $addressRequests)
    {
        $params = $addressRequests->validated();
        $userid = auth('api')->id();
        if ($params['status'] == 1){
            $res = Address::query()->where(['user_id'=>$userid,'status'=>1])->update(['status'=>2]);//原来的默认设置为不默认
        }
        $params['user_id'] = $userid;

        $res = Address::query()->where(['id'=>$params['id']])->update($params);
        if (!$res){
            return fail('提交失败');
        }
        return success($res);
    }

    /**
     * 查询直推下级
     */
    public function get_subordinate(){
        $params = request()->all();
        if ($params['id'] == 0){
            $user = auth('api')->id();
            $data['data'] = User::query()->where(['parent_id'=>$user])->select('id','phone','filcoin',"parent_count")->get()->toArray();
            $data['count'] = count($data['data']);
        }else{
            $data['data'] = User::query()->where(['parent_id'=>$params['id']])->select('id','phone','filcoin',"parent_count")->get()->toArray();
            $data['count'] = count($data['data']);
        }
        return success($data);
    }


}
