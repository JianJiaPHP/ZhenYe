<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\CoinInfo;
use App\Models\FilGoods;
use App\Models\Order;
use App\Models\PriceBalanceInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceRepurchasingIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\UsdtWithdraw;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $where = [];
        $order_number = request()->query('order_number');
        if ($order_number){
            $where[] = ['order_number',$order_number];
        }
        $pay_states = request()->query('pay_states');
        if ($pay_states){
            $where[] = ['pay_states',$pay_states];
        }
        $data =  Order::query()->with(['Order_list','Order_list.Goods_list','user_list','address_data'])->where($where)->orderBy('id', 'desc');
        $phone = request()->query('phone');
        $whereUse[] = ['phone','like',"%$phone%"];
        $data = $data->when($where,function ($query) use ($whereUse){
            $query->whereHas('user_list',function ($query) use ($whereUse){
                $query->where($whereUse);
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
//        DB::beginTransaction();
//        try {
//            $params = request()->all();
//            $result = Order::query()->create([
//                'title' => $params['title'],
//                'text' => $params['text'],
//                'day' => $params['day'],
//                'img' => $params['img'],
//                'price' => $params['price'],
//                'trusteeship' => $params['trusteeship'],
//                'gas_price' => $params['gas_price'],
//                'pledge' => $params['pledge'],
//                'produce' => $params['produce'],
//                'state' => $params['state'],
//                'reward' => $params['reward'],
//            ]);
//            if (!$result) {
//                throw new \Exception('系统超时');
//            }
//            DB::commit();
//            return choose($result);
//        } catch (\Exception $exception) {
//            DB::rollback();
//            return fail($exception->getMessage());
//        }
    }

    /**
     * 更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        DB::beginTransaction();
        try {
            $params = request()->all();
            $result = Order::query()->where('id',$id)->update([
                'logistics' => $params['logistics'],
                'pay_states' => $params['pay_states'], // 6发货
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return choose($result);
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
//        DB::beginTransaction();
//        try {
//            $result = Order::query()->where('id', $id)->delete();
//            if (!$result) {
//                throw new \Exception('系统超时');
//            }
//            DB::commit();
//            return success();
//        } catch (\Exception $exception) {
//            DB::rollback();
//            return fail($exception->getMessage());
//        }
    }


}
