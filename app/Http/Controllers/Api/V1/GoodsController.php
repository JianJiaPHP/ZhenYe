<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddShoppingRequests;
use App\Http\Requests\Api\OrderCreateRequests;
use App\Http\Requests\Api\OrderSeleteRequests;
use App\Http\Requests\Api\PayFilRequests;
use App\Http\Requests\Api\UserAddressRequests;
use App\Models\Address;
use App\Models\CoinInfo;
use App\Models\Files;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\Order;
use App\Models\OrderList;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\User;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;
use Ramsey\Uuid\Uuid;


class GoodsController extends Controller
{

    /**
     * 商品分类
     */
    public function goods_type()
    {
        $list = GoodsType::query()->get();
        return success($list);
    }

    /**
     * 商品列表
     */
    public function goods_list()
    {
        $params = request()->all();
        $where = [];
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', "%" . $params['title'] . "%"];
        }
        if (!empty($params['type'])) {
            $where['type'] = $params['type'];
        }

        $where['put'] = 1;
        $list = Goods::query()->with(['type_name'])->where($where)->orderBy('sort','desc')->paginate(request('limit', 15))->toArray();
        return success($list);
    }

    /**
     * 热搜商品
     * @return \Illuminate\Http\JsonResponse
     */
    public function goods_hot()
    {
        $list = Goods::query()->with(['type_name'])->where(['put' => 1, 'hot' => 1])->paginate(request('limit', 15))->toArray();
        return success($list);
    }


    /**
     * 商品购买生成订单
     * 生成订单商品ID  中间表
     */
    public function order_create(OrderCreateRequests $orderCreateRequests)
    {
        $params = $orderCreateRequests->all();
        DB::beginTransaction();
        try {

            //goods_id 商品id
//            $params['goods_list']  =  json_decode($params['goods_list'] );

            if (empty($params['goods_list'])) {
                return fail('goods_list error');
            }
            $num = 0;
            //计算金额
            foreach ($params['goods_list'] as $k2 => $v2) {
                $price = Goods::query()->where(['id' => $k2])->value('price');
                $num += $price * $v2;
            }
            //生成订单\
            $order_id = Order::query()->create(
                [
                    'order_number' => uuid(),
                    'type' => $params['type'],
                    'price' => $num,
                    'address_id' => $params['address_id'],
                    'user_id' => auth('api')->id()
                ]);
            if (empty($order_id)) {
                DB::rollBack();
                return fail('error Order create');
            }
            //存商品明细
            foreach ($params['goods_list'] as $k => $v) {
                $res = OrderList::query()->create(
                    [
                        'order_id' => $order_id->id,
                        'goods_id' => $k,
                        'sum' => $v,
                    ]);
            }
            if (!$res) {
                DB::rollBack();
                throw new \Exception("请求超时");
            }
            DB::commit();
            foreach ($params['goods_list'] as $k => $v) {
                Redis::hdel('cart:' . auth('api')->id() . '', $k);
            }
            return success($order_id);
        } catch (\Exception $exception) {
            DB::rollBack();
            return fail($exception->getMessage());
        }


    }


    /**
     * 商品详情
     */
    public function goods_details()
    {
        $params = request()->all();
        if (empty($params['goods_id'])) {
            return fail("The :goods_id and :required");
        }
        $data = Goods::query()
            ->with(['type_name'])
            ->where(['id' => $params['goods_id']])
            ->get()->toArray();
        return success($data);
    }

    /**
     * 查询订单信息
     */
    public function order_selete(OrderSeleteRequests $orderSeleteRequests)
    {
        $params = $orderSeleteRequests->all();
        $data = Order::query()
            ->with(['address_data', 'Order_list', 'Order_list.Goods_list', 'Order_list.Goods_list.type_name'])
            ->where(['id' => $params['id']])
            ->first()->toArray();
        return success($data);

    }

    /**
     * 订单列表
     */
    public function order_list()
    {
        $user_id = auth('api')->id();
        $data = Order::query()
            ->with(['address_data', 'Order_list', 'Order_list.Goods_list', 'Order_list.Goods_list.type_name'])
            ->where(['user_id' => $user_id])
            ->get();
        return success($data);
    }


    /**
     * 商品支付 pay
     * @param PayFilRequests $payFilRequests
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function pay_goods()
    {
        $pay = new Pay();
        $params = request()->all();
        if (empty($params['order_number'])){
            return fail("参数错误");
        }
        if (empty($params['password'])){
            return fail("参数错误");
        }
        if (empty($params['pay_type'])){
            return fail("参数错误");
        }
        //该用户账户余额
        $user = \App\Models\User::query()->where(['id' => auth('api')->id()])->select(['id','parent_id', 'filcoin', 'usdt','repurchasing_integral', 'balance','hyc','pay_password','gift_integral'])->first();
        $parent_user = \App\Models\User::query()->where(['id' => $user['parent_id']])->select(['id','parent_id', 'filcoin', 'usdt', 'pay_password','gift_integral'])->first();
        $hyc_price = CoinInfo::query()->orderBy('id','desc')->value('price');
        //交易密码确认
        if (!\Hash::check(MD5($params['password']), $user['pay_password'])) {
            return fail('支付密码错误');
        }
        //币价汇率
        $real = (new LoginController())->price_bi_api();
        //订单信息
        $filData = Order::query()->with(['Order_list','Order_list.Goods_list'])->where(['order_number' => $params['order_number']])->first()->toArray();
        //商品信息
        if (empty($filData['order_list'])) {
            return fail("未查到商品信息！");
        }
        $moenys = 0;
        foreach ($filData['order_list'] as $v){
            $moenys = $moenys + ($v['sum']*$v['goods_list']['price']);
        }
        if ($filData['pay_states'] == 1) {
            return fail("该订单已支付");
        }
        $is = (new Pay())->pay_fil_ok($user['id']);
        if (!$is) {
            return fail("金额错误！请联系管理员或客服！");
        }
        //核算金额
        switch ($params['pay_type']){
            case '1':
                //USDT
                $reals['value'] = $real['USDT'];
                $reals['key'] = 'USDT';
                $moeny = round($moenys / $real['USDT'], 6);
                if ($user['usdt'] < $moeny) {
                    return fail("USDT金额不足");
                }
                //明细扣除金额
                $total_money = $user['usdt'] - $moeny;
                PriceUsdtInfo::query()->create([
                    'user_id' => $user['id'],
                    'status' => '2',
                    'total_money' => $total_money,//金额值
                    'price' => $moeny,//产币数量
                    'type' => 14,
                    'addtime' => time(),
                    'adddate' => Carbon::now(),
                    'remark' => "健康商城商品购买",
                ]);
                $res = \App\Models\User::query()->where(['id' => $user['id']])->update(['usdt' => $total_money]);
                if (!$res) {
                    return fail("支付错误");
                }
                break;
            case '2':
                //余额90%+hyc10%
                $moeny90 = round($moenys * 0.9, 4);
                $HYC = round($moenys * 0.1 / $hyc_price, 4);
                if ($user['balance'] < $moeny90 || $user['hyc']<$HYC){
                    return fail("金额不足");
                }
                $reals['value'] = $hyc_price;
                $reals['key'] = 'HYC/CNY';
                $moeny = $HYC;
                //cny
                $ok1 = $pay->pay_cny($user['id'],2,$user['balance']-$moeny90,$moeny90,"购买商品");
                //hyc
                $ok2 = $pay->pay_hyc($user['id'],2,$user['hyc']-$HYC,$HYC,"购买商品");
                if (!$ok1||!$ok2){
                    return fail("支付错误");
                }
                break;
            case '3':
                //复购积分70% + HYC 30%
                $moeny70 = round($moenys * 0.7, 4);
                $HYC = round($moenys * 0.3 / $hyc_price, 4);
                $reals['value'] = $hyc_price;
                $reals['key'] = 'HYC/FG';
                $moeny = $HYC;
                if ($user['repurchasing_integral'] < $moeny70 || $user['hyc']<$HYC){
                    return fail("金额不足");
                }
                //cny
                $ok1 = $pay->pay_fg($user['id'],2,$user['repurchasing_integral']-$moeny70,$moeny70,"购买商品");
                //hyc
                $ok2 = $pay->pay_hyc($user['id'],2,$user['hyc']-$HYC,$HYC,"购买商品");
                if (!$ok1||!$ok2){
                    return fail("支付错误");
                }
                break;
            default:
                return fail("支付错误");
                break;
        }
        //矿机订单修改
        Order::query()->where(['order_number' => $params['order_number']])->update([
            'pay_states' => 1,
            'real_price' => $reals['value'],
            'type_bi' =>$params['pay_type'],
            'real_pay' => $moeny,
        ]);
        //购买产品赠送 自己
        $integral  = $moenys * 0.6;
        $integralz  = $moenys * 0.1;
        PriceGiftIntegralInfo::query()->create([
            'user_id' => $user['id'],
            'status'=>1,
            'total_money'=>$user['gift_integral']+$integral,
            'price'=>$integral,
            'type'=>16,
            'addtime'=>time(),
            'adddate'=>Carbon::now(),
            'remark'=> '购买商城产品赠送积分'
        ]);
        User::query()->where(['id'=>$user['id']])->update(['gift_integral'=>$user['gift_integral']+$integral]);
        if (!empty($parent_user)) {
            PriceGiftIntegralInfo::query()->create([
                'user_id' => $user['parent_id'],
                'status' => 1,
                'total_money' => $parent_user['gift_integral'] + $integralz,
                'price' => $integralz,
                'type' => 17,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => '下级直接奖励赠送积分'
            ]);
            User::query()->where(['id' => $user['parent_id']])->update(['gift_integral' => $parent_user['gift_integral'] + $integralz]);
        }
        return success("支付成功");
    }


}
