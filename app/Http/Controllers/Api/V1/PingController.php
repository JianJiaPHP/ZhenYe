<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;

use App\Models\Config;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\LogInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceLockFilInfo;
use App\Models\UsdtWithdraw;
use App\Models\User;
use App\Services\ConfigService;
use App\Utils\lib;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class PingController extends Controller
{

    public $lib;
    function __construct()
    {
        $this->lib = new lib();
        define('ACCOUNT_ID', ''); // your account ID
        define('ACCESS_KEY',''); // your ACCESS_KEY
        define('SECRET_KEY', ''); // your SECRET_KEY
    }
    public function fil_ping(){

    }
    public function fil_ping2(){
        $list = FilOrder::query()->where(['pay_states'=>1,'type'=>1,'encapsulation'=>1])->get()->toArray();
        foreach ($list as $k=>$v){
            $produce = FilGoods::query()->where(['id'=>$v['goods_id']])->value('produce');
            $produce = $produce * $v['count'];
            //fil矿机产币 封装完成 质押完成
            //可用分25%
            $user_price_info = PriceFilInfo::query()->where(['user_id'=>$v['user_id']])->orderBy('id', 'desc')->value('total_money')?:0;
            $user_price_lock_info = PriceLockFilInfo::query()->where(['user_id'=>$v['user_id']])->orderBy('id', 'desc')->value('total_money')?:0;
            $user_filcoin = User::query()->where(['id'=>$v['user_id']])->select(['filcoin','filcoin_lock'])->first();
            if ($user_filcoin['filcoin'] != $user_price_info || $user_filcoin['filcoin_lock'] != $user_price_lock_info){
                //错误日志
                LogInfo::query()->create([
                    'uid'=>$v['user_id'],
                    'router'=>'filGrant',
                    'method'=>'add',
                    'content'=>'fil金额错误',
                    'desc'=>'fil金额错误',
                    'ip'=>'127.0.0.1',
                ]);
                continue;
            }
            PriceFilInfo::query()->create([
                'user_id'=>$v['user_id'],
                'status'=>'1',
                'total_money'=>$user_filcoin['filcoin'] + ($produce*0.25),//产币数量
                'price'=>$produce*0.25,//产币数量
                'type'=>10,
                'addtime'=>time(),
                'adddate'=>Carbon::now(),
                'remark'=>"fil矿机产币收益",
            ]);
            User::query()->where(['id'=>$v['user_id']])->update(['filcoin'=>$user_filcoin['filcoin'] + ($produce*0.25)]);
            //锁仓分75%
            PriceLockFilInfo::query()->create([
                'user_id'=>$v['user_id'],
                'status'=>'1',
                'total_money'=>$user_filcoin['filcoin_lock'] + ($produce*0.75),//产币数量
                'price'=>$produce*0.75,//产币数量
                'type'=>10,
                'addtime'=>time(),
                'adddate'=>Carbon::now(),
                'remark'=>"fil矿机产币收益",
            ]);
            User::query()->where(['id'=>$v['user_id']])->update(['filcoin_lock'=>$user_filcoin['filcoin_lock'] + ($produce*0.75)]);
        }
        return success(1);
    }


    /**
     * 123
     */
    public function deposit_withdraw()
    {
        $res = $this->lib->deposit_withdraw('usdt','deposit','',500,'next');
        //新增数据
        $res_id = [];
        foreach ($res['data'] as $k=>$v){
            $res['data'][$k]['created-at'] = date('Y-m-d H:i:s', $v['created-at'] / 1000);
            $res['data'][$k]['updated-at'] = date('Y-m-d H:i:s', $v['updated-at'] / 1000);
            $res_id[] = $v['id'];
        }
        $dataId = UsdtWithdraw::query()->pluck('id')->toArray();
        $diff_id = array_diff($res_id,$dataId);
        if (!$diff_id){
            return fail(0);
        }
        foreach ($res['data'] as $v1){
            if (in_array($v1['id'],$diff_id)){
                UsdtWithdraw::query()->create($v1);
            }
        }
        return success(1);
    }

    /**
     * 刷新Coin数据
     */
    public function coin_info(){
        $resfil = $this->lib->get_detail_merged('filusdt');
        if ($resfil->status!='error' && $resfil->tick->close>0){
            $USDT = Config::query()->where(['key'=>'USDT'])->value('value')?:'6.45';
            $value = $resfil->tick->close * $USDT;
            Config::query()->where(['key'=>'FIL'])->update(['value'=>$value]);
        }
        return success(1);
    }


}
