<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddShoppingRequests;
use App\Http\Requests\Api\MinerOrderCreateRequests;
use App\Http\Requests\Api\OrderCreateRequests;
use App\Http\Requests\Api\OrderSeleteRequests;
use App\Http\Requests\Api\PayFilRequests;
use App\Http\Requests\Api\UserAddressRequests;
use App\Models\Address;
use App\Models\Files;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\HycGoods;
use App\Models\LogInfo;
use App\Models\Order;
use App\Models\OrderList;
use App\Models\PriceFilInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceUsdtInfo;
use App\Utils\Pay;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;
use Ramsey\Uuid\Uuid;


class FilGoodsController extends Controller
{

    /**
     * 商品列表
     */
    public function fil_goods_list()
    {
        $params = request()->all();
        $where = [];
        if (!empty($params['title'])){
            $where[] = ['title', 'like', "%".$params['title']."%"];
        }

        $where['state'] = 1;
        $list = FilGoods::query()->where($where)->paginate(request('limit',15))->toArray();
        return success($list);
    }

    /**
     * 商品购买生成订单
     * 生成订单商品ID  中间表
     */
    public function miner_order_create(MinerOrderCreateRequests $orderCreateRequests){
        $params = $orderCreateRequests->all();
        try {
            $goodsData = FilGoods::query()->where(['id'=>$params['goods_id']])->first();
            $num = $goodsData['price'] * $params['count'];
            if ($num<1){
                return fail("下单失败");
            }
            //生成订单\
            $order_id = FilOrder::query()->create(
                [
                    'order_number'=>uuid(),
                    'type'=>$params['type'],
                    'price'=>$num,
                    'count'=>$params['count'],
                    'goods_id'=>$params['goods_id'],
                    'encapsulation'=>2,
                    'user_id'=>auth('api')->id()
                ]);
            if (empty($order_id)){
                return fail('error Order create');
            }
            return success($order_id);
        } catch (\Exception $exception) {
            return fail($exception->getMessage());
        }
    }


    /**
     * 商品详情
     */
    public function miner_order_details(){
        $params = request()->all();
        if (empty($params['goods_id'])){
            return fail("The :goods_id and :required");
        }
        $data = FilGoods::query()
            ->where(['id'=>$params['goods_id']])
            ->first()->toArray();
        return success($data);
    }


//    /**
//     * 查询订单信息
//     */
//    public function order_selete(OrderSeleteRequests $orderSeleteRequests){
//        $params = $orderSeleteRequests->all();
//        $data = Order::query()
//            ->with(['address_data','Order_list' ,'Order_list.Goods_list','Order_list.Goods_list.type_name'])
//            ->where(['id'=>$params['id']])
//            ->first()->toArray();
//        return success($data);
//    }

    /**
     * 订单列表
     */
    public function miner_order_list(){
        $user_id = auth('api')->id();
        $data = FilOrder::query()
            ->with(['goods_list'])
            ->where(['user_id'=>$user_id])
            ->get();
        return success($data);
    }

    /**
     * 购买Fil矿机
     */
    public function pay_fil(PayFilRequests $payFilRequests)
    {

        $params = $payFilRequests->all();
        //该用户账户余额
        $user = \App\Models\User::query()->where(['id'=>auth('api')->id()])->select(['id','filcoin','usdt','pay_password'])->first();
        //交易密码确认
        if (!\Hash::check( MD5($params['password']),$user['pay_password']))
        {
            return fail('支付密码错误');
        }
        //币价汇率
        $real = (new LoginController())->price_bi_api();
        //订单信息
        $filData = FilOrder::query()->where(['order_number'=>$params['order_number']])->first();
        //商品信息
        $goods = FilGoods::query()->where(['id'=>$filData['goods_id']])->first();
        if (empty($goods)){
            return fail("未查到商品信息！");
        }
        if ($filData['pay_states']==1){
            return fail("该订单已支付");
        }
            $is = (new Pay())->pay_fil_ok($user['id']);
            if (!$is){
                return fail("金额错误！请联系管理员或客服！");
            }
        if ($params['type_bi'] == 'USDT'){
            //核算金额
            $reals['value'] = $real['USDT'];
            $reals['key'] = 'USDT';
            $moeny = round($filData['price'] / $real['USDT'],6) ;
            if ($user['usdt']< $moeny){
                return fail("USDT金额不足");
            }
            //明细扣除金额
            $total_money = $user['usdt'] - $moeny;
            PriceUsdtInfo::query()->create([
                'user_id' => $user['id'],
                'status' => '2',
                'total_money' => $total_money,//金额值
                'price' => $moeny,//产币数量
                'type' => 13,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "FIL矿机购买",
            ]);
            $res = \App\Models\User::query()->where(['id'=>$user['id']])->update(['usdt'=>$total_money]);
            if (!$res){
                return fail("支付错误");
            }
        }elseif ($params['type_bi'] == 'FIL'){
            $reals['value'] = $real['FIL'];
            $reals['key'] = 'FIL';
            $moeny = round($filData['price'] / $real['FIL'],4) ;
            if ($user['filcoin']< $moeny){
                return fail("FIL金额不足");
            }
            //明细扣除金额
            $total_money = $user['filcoin'] - $moeny;
            PriceFilInfo::query()->create([
                'user_id' => $user['id'],
                'status' => '2',
                'total_money' => $total_money,//金额值
                'price' => $moeny,//产币数量
                'type' => 13,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "FIL矿机购买",
            ]);
            $res = \App\Models\User::query()->where(['id'=>$user['id']])->update(['filcoin'=>$total_money]);
            if (!$res){
                return fail("支付错误");
            }
        }else{
            return fail("支付错误！");
        }
        //矿机订单修改
        FilOrder::query()->where(['order_number'=>$params['order_number']])->update([
            'pay_states'=>1,
            'validity_at'=>Carbon::now()->addDay($goods['day']),
            'encapsulation' => 2,
            'real_price' => $reals['value'],
            'type_bi' => $reals['key'],
            'real_pay' => $moeny,
            'pledge_status' => 1,
        ]);
        return success("支付成功");

    }
}
