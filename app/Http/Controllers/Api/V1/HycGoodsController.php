<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddShoppingRequests;
use App\Http\Requests\Api\HycMinerOrderCreateRequests;
use App\Http\Requests\Api\MinerOrderCreateRequests;
use App\Http\Requests\Api\OrderCreateRequests;
use App\Http\Requests\Api\OrderSeleteRequests;
use App\Http\Requests\Api\UserAddressRequests;
use App\Models\Address;
use App\Models\CoinInfo;
use App\Models\Files;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\HycGoods;
use App\Models\HycOrder;
use App\Models\Order;
use App\Models\OrderList;
use App\Models\PriceHycInfo;
use App\Models\PriceUsdtInfo;
use App\Models\User;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;
use Ramsey\Uuid\Uuid;


class HycGoodsController extends Controller
{

    /**
     * 商品列表
     */
    public function hyc_goods_list()
    {
        $params = request()->all();
        $where = [];
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', "%" . $params['title'] . "%"];
        }

        $where['state'] = 1;
        $list = HycGoods::query()->where($where)->paginate(request('limit', 15))->toArray();
        return success($list);
    }

    /**
     * 商品购买生成订单
     * 生成订单商品ID  中间表
     */
    public function hyc_miner_order_create(HycMinerOrderCreateRequests $hycorderCreateRequests)
    {
        $params = $hycorderCreateRequests->all();
        try {
            //该用户账户余额
            $user = User::query()->where(['id'=>auth('api')->id()])->select(['id','parent_id','filcoin','usdt','hyc','pay_password'])->first();

            //交易密码确认
            if (!\Hash::check( MD5($params['password']),$user['pay_password']))
            {
                return fail('支付密码错误');
            }
            //判断coin账户正常
            $is = (new Pay())->pay_fil_ok($user['id']);
            if (!$is){
                return fail("金额错误！请联系管理员或客服！");
            }
            //币价汇率
//            $real = CoinInfo::query()->orderBy('id','desc')->value('price');

            //商品详情
            $goodsData = HycGoods::query()->where(['id' => $params['miner_id']])->first();
            if ($goodsData['state'] == 0 || $goodsData['number'] < 1){
                return fail("商品库存不足！");
            }
            if (empty($goodsData)){
                return fail("未查到商品信息！");
            }

            //查询限购
            $sums = HycOrder::query()->where(['user_id' => auth('api')->id(),'miner_id'=>$params['miner_id']])
                ->where('status', '!=', '2')->sum('number');


            if ($params['miner_id'] == 12){
                $number1 = HycOrder::query()->where(['user_id' => auth('api')->id(),'miner_id'=>4])
                    ->where('status', '!=', '2')->sum('number');
                $sums = $sums+$number1;
            }elseif($params['miner_id'] == 13){
                $number1 = HycOrder::query()->where(['user_id' => auth('api')->id(),'miner_id'=>5])
                    ->where('status', '!=', '2')->sum('number');
                $sums = $sums+$number1;
            }elseif ($params['miner_id'] == 15){
                $number1 = HycOrder::query()->where(['user_id' => auth('api')->id(),'miner_id'=>6])
                    ->where('status', '!=', '2')->sum('number');
                $sums = $sums+$number1;
            }
            if (($sums + $params['number']) > $goodsData['limit_count']) {
                return fail("超过限购!购买失败");
            }

            //核算金额
            $moeny = round(($goodsData['price'] * $params['number']),4) ;
            if ($user['hyc'] <= $moeny){
                return fail("HYC金额不足");
            }
            DB::beginTransaction();
            //明细扣除金额
            $total_money = $user['hyc'] - $moeny;
            PriceHycInfo::query()->create([
                'user_id' => $user['id'],
                'status' => '2',
                'total_money' => $total_money,//金额值
                'price' => $moeny,//扣除数量
                'type' => 13,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "HYC矿机购买",
            ]);
            $res = User::query()->where(['id'=>$user['id']])->update(['hyc'=>$total_money]);
            if (!$res){
                DB::rollBack();
                return fail("支付错误");
            }
            //生成订单\
            $order_id = HycOrder::query()->create(
                [
                    'order_number' => uuid(),
                    'type' => $params['type'] ?: 2,
                    'day' => $goodsData['day'] ?: 30,
                    'price' => $goodsData['price'] * $params['number'],
                    'price_hyc' => $moeny,
                    'number' => $params['number'],
                    'stage'=>1,
                    'miner_id' => $params['miner_id'],
                    'day_price' => $goodsData['total_price'] * $params['number'],
                    'end_time'=>Carbon::now()->addDay($goodsData['day']?: 30)->toDateString(),
                    'user_id' => auth('api')->id()
                ]);
            //商品数量减少
            HycGoods::query()->where(['id'=>$params['miner_id']])->decrement('number');
            if (empty($order_id)) {
                DB::rollBack();
                return fail('支付失败');
            }
            DB::commit();
            //上级分红
            if ($user['parent_id']>0){
                $user_parent_id = User::query()->where(['id'=>$user['parent_id']])->first();
                $pay = new Pay();
                $moenys = $goodsData['price'] * $params['number'] * 0.05;
                $pay->pay_hyc($user['parent_id'],1,$user_parent_id['hyc'] + $moenys,$moenys,"HYC矿机购买直推奖励");
            }
            return success($order_id);
        } catch (\Exception $exception) {
            return fail($exception->getMessage());
        }
    }

    /**
     * 订单列表
     */
    public function hyc_miner_order_list()
    {
        $params = request()->all();//type : 1:查询可用 2：查询全部
        if ($params['type'] == 1) {
            $where[] = ['status', '!=', 2];
        }
        $where['user_id'] = auth('api')->id();
        $list = HycOrder::query()->with(['goods_list'])->where($where)->get()->toArray();
        return success($list);
    }


    /**
     * 商品详情
     */
    public function hyc_miner_order_details()
    {
        $params = request()->all();
        if (empty($params['goods_id'])) {
            return fail("The :goods_id and :required");
        }
        $data = HycGoods::query()
            ->where(['id' => $params['goods_id']])
            ->first()->toArray();
        return success($data);
    }

    /**
     * 运行HYC矿机
     */
    public function hyc_on_status(){
        $user_id = auth('api')->id();
        $is = HycOrder::query()->where(['user_id'=>$user_id,'status'=>1])->exists();
        if ($is){
            HycOrder::query()->where(['user_id'=>$user_id,'status'=>0])->update(['status'=>1]);
            return choose(1);
        }
        $res = HycOrder::query()->where(['user_id'=>$user_id,'status'=>0])->update(['status'=>1]);
        return choose($res);
    }


}
