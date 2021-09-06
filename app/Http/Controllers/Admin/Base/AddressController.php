<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddressController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data = Address::query()->with('user_list')->orderBy('id', 'desc');
        $phone = request()->query('phone');
        $where[] = ['phone','like',"%$phone%"];
        $data = $data->when($where,function ($query) use ($where){
            $query->whereHas('user_list',function ($query) use ($where){
                $query->where($where);
            }) ;
        });
        $limit = request()->query('limit', 10);
        $data = $data->paginate($limit);
        return success($data);
    }

    /**
     * 添加
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function store()
    {
        $params = request()->all();
//        $result = User::query()->create([
//            'img' => $params['img'],
//            'text' => $params['text'],
//            'title' => $params['title'],
//            'type' => $params['type'],
//        ]);
        return choose($params);
    }

    /**
     * 更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        $params = request()->all();
        $result = Address::query()->where('id', $id)->update([
            'address_name' => $params['address_name'],
            'address' => $params['address'],
            'consignee' => $params['consignee'],
            'phone' => $params['phone'],
            'status' => $params['status'],
        ]);
        return choose($result);
    }

    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = Address::query()->where('id', $id)->delete();
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return success();
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }


}
