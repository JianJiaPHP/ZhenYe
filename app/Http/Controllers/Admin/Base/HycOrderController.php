<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\FilOrder;
use App\Models\HycOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HycOrderController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data = HycOrder::query()->orderBy('id', 'desc');
        $phone = request()->query('phone');
        $type = request()->query('type');
        $status = request()->query('status');
        $limit = request()->query('limit', 10);
        if ($phone){
            $where[] = ['phone','like',"%$phone%"];
            $data = $data->when($where,function ($query) use ($where){
                $query->whereHas('user_list',function ($query) use ($where){
                    $query->where($where);
                }) ;
            });
        }
        if ($type){
            $data = $data->where('type', $type);
        }
        if ($status){
            $data = $data->where('status', $status);
        }
        $data = $data->with(['goods_list'])->paginate($limit);
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
        return choose($params);
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
            $result = HycOrder::query()->where('id', $id)->delete();
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
