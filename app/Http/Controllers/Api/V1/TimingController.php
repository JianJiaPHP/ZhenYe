<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartIdRequests;
use App\Http\Requests\Api\CartSeleteRequests;
use App\Models\CoinBuy;
use App\Models\CoinInfo;
use App\Models\CoinSell;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\Goods;
use App\Models\HycGoods;
use App\Models\HycOrder;
use App\Models\Log;
use App\Models\LogInfo;
use App\Models\Order;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceUsdtInfo;
use App\Models\UsdtRecord;
use App\Models\UsdtWithdraw;
use App\Models\User;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;
use Ramsey\Uuid\Uuid;


class TimingController extends Controller
{


    /**
     * 赠送积分释放365天
     */
    public function fil_ping(){
        //发放收益 ， 购买金额质押 矿机过期HYC放回
        $pay = new Pay();
        $list = HycOrder::query()->where(['status'=>1,'stage'=>1])->get()->toArray();
        dd($list);
        $list = array_group_by($list,'user_id');
        foreach ($list as $k=>$v){
            $moeny_ok = $pay->pay_hyc_ok($k);
            if (!$moeny_ok){
                continue;
            }
            $sum = 0;
            $money = $moeny_ok['hyc'];
            foreach ($v as $value){
                //判断矿机过期  选择停机还是过期
                $total_price = $value['total_price'] + $value['day_price'];
                if (Carbon::now()>=$value['end_time']){
                    HycOrder::query()->where('id',$value['id'])->update(['status'=>2,'total_price'=>$total_price]);
                    $price_hyc =  HycOrder::query()->where(['id'=>$value['id']])->value('price_hyc');//押金
                    $money = $money + $price_hyc;
                    //归还HYC
                }else{
                    HycOrder::query()->where('id',$value['id'])->update(['status'=>0,'total_price'=>$total_price,'last_time'=>Carbon::now()]);
                    $price_hyc = 0;
                }
                //给钱
                $money = $money + $value['day_price'];
                //增加收入明细
                $pay->pay_hyc($value['user_id'],1,$money,$price_hyc + $value['day_price'],"HYC矿机产出",11);
                $sum = $sum +$price_hyc + $value['day_price'];
            }
            User::query()->where(['id'=>$k])->update(['hyc'=>$moeny_ok['hyc']+$sum]);
        }
        return success(1);
    }



    /**
     * 识别充值发币
     */
    public function fil_ping5()
    {
        //火币充值记录
        $where[] = ['updated_at','>',Carbon::now()->addHour(-1)];
        $where['status'] = 1;
        $list = UsdtRecord::query()->where($where)->get()->toArray();
        //充值查询
        $whereT[] = ['updated-at','>',Carbon::now()->addHour(-1)];
        $whereT['currency'] = 'usdt';
        $whereT['state'] = 'safe';
        foreach ($list as $k=>$v){
            $whereT['amount'] = $v['num'];
            $data = UsdtWithdraw::query()->where($whereT)->first();
            if (!empty($data)){
                //确认充值
                //判断coin账户正常
                $is = (new Pay())->pay_usdt_ok($v['user_id']);
                if (!$is){
                    continue;
                }
                //修改充值订单
                UsdtRecord::query()->where('id',$v['id'])->update(['status'=>2]);//完成
                //修改火币记录订单
                UsdtWithdraw::query()->where('id',$data['id'])->update(['state'=>'ok']);
                //修改账户金额
                $moeny = $is['usdt'] + $v['num'];
                PriceUsdtInfo::query()->create([
                   'user_id'=>$v['user_id'],
                   'status'=>1,
                   'total_money'=> $moeny,
                   'price'=> $v['num'],
                   'type'=> 20,
                   'addtime'=> time(),
                   'adddate'=> Carbon::now(),
                   'remark'=> 'USDT充值',
                ]);
                //用户表修改
                User::query()->where(['id'=>$v['user_id']])->update(['usdt'=>$moeny]);
            }
        }
        dd($list);
    }
    /**
     * hyc矿机发币
     */
    public function fil_ping7()
    {
        $pay = new Pay();
        $list = HycOrder::query()->where(['status'=>1])->get()->toArray();
        $list = array_group_by($list,'user_id');
        foreach ($list as $k=>$v){
            $moeny_ok = $pay->pay_hyc_ok($k);
            if (!$moeny_ok){
                continue;
            }
            $sum = 0;
            $money = $moeny_ok['hyc'];
            foreach ($v as $value){
                //判断矿机过期  选择停机还是过期
                $total_price = $value['total_price'] + $value['day_price'];
                if (Carbon::now()>=$value['end_time']){
                    HycOrder::query()->where('id',$value['id'])->update(['status'=>2,'total_price'=>$total_price]);
                }else{
                    HycOrder::query()->where('id',$value['id'])->update(['status'=>0,'total_price'=>$total_price]);
                }
                //给钱
                $money = $money + $value['day_price'];
                //增加收入明细
                PriceHycInfo::query()->create([
                    'user_id' => $value['user_id'],
                    'status' => '1',
                    'total_money' => $money ,
                    'price' => $value['day_price'],//产币数量
                    'type' => 11,
                    'addtime' => time(),
                    'adddate' => Carbon::now(),
                    'remark' => "HYC矿机产出",
                ]);
                $sum = $sum+$value['day_price'];
            }
            User::query()->where(['id'=>$k])->update(['hyc'=>$moeny_ok['hyc']+$sum]);
        }
    }


    /**
     * 线性释放
     */
    public function fil_ping3()
    {
        //fil线性释放
        $data = User::query()->where('filcoin_lock','>',0)->select(['id','filcoin_lock'])->get()->toArray();
        foreach ($data as $k=>$v){
            $user_price_info = PriceFilInfo::query()->where(['user_id' => $v['id']])->orderBy('id', 'desc')->value('total_money') ?: 0;
            $user_price_lock_info = PriceLockFilInfo::query()->where(['user_id' => $v['id']])->orderBy('id', 'desc')->value('total_money') ?: 0;
            $user_filcoin = User::query()->where(['id' => $v['id']])->select(['filcoin', 'filcoin_lock'])->first();
            if ($user_filcoin['filcoin'] != $user_price_info || $user_filcoin['filcoin_lock'] != $user_price_lock_info) {
                //错误日志
                LogInfo::query()->create([
                    'uid' => $v['id'],
                    'router' => 'filGrant',
                    'method' => 'add',
                    'content' => 'fil金额错误',
                    'desc' => 'fil金额错误',
                    'ip' => '127.0.0.1',
                ]);
                continue;
            }
            $money = $user_filcoin['filcoin'] + ($v['filcoin_lock'] / 180);
            //增加收入明细
            PriceFilInfo::query()->create([
                'user_id' => $v['id'],
                'status' => '1',
                'total_money' =>$money ,
                'price' => $v['filcoin_lock'] / 180,//产币数量
                'type' => 11,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "fil矿机产币锁仓线性释放",
            ]);

            //锁仓线性释放 修改
            $money1 = $user_filcoin['filcoin_lock'] - ($v['filcoin_lock'] / 180);
            PriceLockFilInfo::query()->create([
                'user_id' => $v['id'],
                'status' => '2',
                'total_money' => $money1,
                'price' => $v['filcoin_lock'] / 180,//产币数量
                'type' => 11,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "fil矿机产币锁仓线性释放",
            ]);
            //用户表修改
            User::query()->where(['id' => $v['id']])->update(['filcoin' => $money,'filcoin_lock'=>$money1]);
        }
        return success(11);
    }


    public function fil_ping2()
    {
        $list = FilOrder::query()->where(['pay_states' => 1, 'type' => 1, 'encapsulation' => 1, 'pledge_status' => 1])->get()->toArray();
        foreach ($list as $k => $v) {
            $produce = FilGoods::query()->where(['id' => $v['goods_id']])->value('produce');
            $produce = $produce * $v['count'];
            //fil矿机产币 封装完成 质押完成
            //可用分25%
            $user_price_info = PriceFilInfo::query()->where(['user_id' => $v['user_id']])->orderBy('id', 'desc')->value('total_money') ?: 0;
            $user_price_lock_info = PriceLockFilInfo::query()->where(['user_id' => $v['user_id']])->orderBy('id', 'desc')->value('total_money') ?: 0;
            $user_filcoin = User::query()->where(['id' => $v['user_id']])->select(['filcoin', 'filcoin_lock'])->first();
            if ($user_filcoin['filcoin'] != $user_price_info || $user_filcoin['filcoin_lock'] != $user_price_lock_info) {
                //错误日志
                LogInfo::query()->create([
                    'uid' => $v['user_id'],
                    'router' => 'filGrant',
                    'method' => 'add',
                    'content' => 'fil金额错误',
                    'desc' => 'fil金额错误',
                    'ip' => '127.0.0.1',
                ]);
                continue;
            }
            PriceFilInfo::query()->create([
                'user_id' => $v['user_id'],
                'status' => '1',
                'total_money' => $user_filcoin['filcoin'] + ($produce * 0.25),//产币数量
                'price' => $produce * 0.25,//产币数量
                'type' => 10,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "fil矿机产币收益",
            ]);
            User::query()->where(['id' => $v['user_id']])->update(['filcoin' => $user_filcoin['filcoin'] + ($produce * 0.25)]);
            //锁仓分75%
            PriceLockFilInfo::query()->create([
                'user_id' => $v['user_id'],
                'status' => '1',
                'total_money' => $user_filcoin['filcoin_lock'] + ($produce * 0.75),//产币数量
                'price' => $produce * 0.75,//产币数量
                'type' => 10,
                'addtime' => time(),
                'adddate' => Carbon::now(),
                'remark' => "fil矿机产币收益",
            ]);
            User::query()->where(['id' => $v['user_id']])->update(['filcoin_lock' => $user_filcoin['filcoin_lock'] + ($produce * 0.75)]);
        }
        return success(1);
    }

    /**
     * 锁仓区分
     */
    public function cai_ping()
    {
        $list = User::query()->where('filcoin','>',0)->select('id','filcoin','filcoin_lock')->get();
        dd($list);
    }

}
