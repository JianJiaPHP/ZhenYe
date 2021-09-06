<?php


namespace App\Utils;


use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use App\Models\LogInfo;
use App\Models\PriceBalanceInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceLockHycInfo;
use App\Models\PriceRepurchasingIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\User;
use Carbon\Carbon;
use Darabonba\OpenApi\Models\Config;

class Pay
{

    /**
     * 判断账户金额对比
     */
    public function pay_fil_ok($user_id){
        $user_price_info = PriceFilInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user_price_lock_info = PriceLockFilInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $usdt = PriceUsdtInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $hyc = PriceHycInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $intz = PriceGiftIntegralInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $intf = PriceRepurchasingIntegralInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $balance = PriceBalanceInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
//        $hyc_lock = PriceUsdtInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user_filcoin = User::query()->where(['id' => $user_id])->select(['filcoin', 'filcoin_lock','usdt','hyc','gift_integral','repurchasing_integral','balance'])->first();
        if ($user_filcoin['filcoin'] != $user_price_info  ) {
            //错误日志
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'filcoin',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['filcoin_lock'] != $user_price_lock_info)
        {
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'filcoin_lock',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['usdt'] != $usdt){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'usdt',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['hyc'] != $hyc){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'hyc',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['gift_integral'] != $intz){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'gift_integral',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['repurchasing_integral'] != $intf){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'repurchasing_integral',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }elseif ($user_filcoin['balance'] != $balance){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fil_ok',
                'method' => 'balance',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
        }
        return 1;
    }


    public function pay_hyc_ok($user_id){
        $hyc = PriceHycInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user = User::query()->where(['id' => $user_id])->select(['hyc'])->first();
        if ($user['hyc'] != $hyc){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_hyc_ok',
                'method' => 'hyc',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }
        return $user;
    }
    public function pay_usdt_ok($user_id){
        $usdt = PriceUsdtInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user = User::query()->where(['id' => $user_id])->select(['usdt'])->first();
        if ($user['usdt'] != $usdt){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_usdt_ok',
                'method' => 'usdt',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }
        return $user;
    }
    //give
    public function pay_give_ok($user_id){
        $give = PriceGiftIntegralInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user = User::query()->where(['id' => $user_id])->select(['gift_integral'])->first();
        if ($user['gift_integral'] != $give){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_give_ok',
                'method' => 'gift_integral',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }
        return 1;
    }
    //balance
    public function pay_balance_ok($user_id){
        $balance = PriceBalanceInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user = User::query()->where(['id' => $user_id])->select(['balance'])->first();
        if ($user['balance'] != $balance){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_balance_ok',
                'method' => 'balance',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }
        return 1;

    }
    /**
     * 操作赠送积分表
     */
    public function pay_give($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_give_ok($user_id);
        if (!$ok){return 0;}
        PriceGiftIntegralInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 19,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['gift_integral'=>$total_money]);
        return 1;
    }

    /**
     * @param $user_id
     * @param $status
     * @param $total_money
     * @param $price
     * @param $remark
     * @return int
     * 操作余额表
     */
    public function pay_cny($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_balance_ok($user_id);
        if (!$ok){return 0;}
        PriceBalanceInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 18,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['balance'=>$total_money]);
        return 1;
    }
    public function pay_hyc($user_id,$status,$total_money,$price,$remark,$type=18){
        $ok = $this->pay_hyc_ok($user_id);
        if (!$ok){return 0;}
        PriceHycInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => $type,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['hyc'=>$total_money]);
        return 1;
    }
    /**
     * @param $user_id
     * @param $status
     * @param $total_money
     * @param $price
     * @param $remark
     * @return int
     * 操作余额表
     */
    public function pay_fg($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_fg_ok($user_id);
        if (!$ok){return 0;}
        PriceRepurchasingIntegralInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 18,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['repurchasing_integral'=>$total_money]);
        return 1;
    }
    public function pay_fg_ok($user_id){
        $give = PriceRepurchasingIntegralInfo::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_money') ?: 0;
        $user = User::query()->where(['id' => $user_id])->select(['repurchasing_integral'])->first();
        if ($user['repurchasing_integral'] != $give){
            LogInfo::query()->create([
                'uid' => $user_id,
                'router' => 'pay_fg_ok',
                'method' => 'repurchasing_integral',
                'content' => '金额错误',
                'desc' => '金额错误',
                'ip' => '127.0.0.1',
            ]);
            return 0;
        }
        return 1;
    }


    /**
     * @param $user_id
     * @param $status
     * @param $total_money
     * @param $price
     * @param $remark
     * @return int
     * 操作余额表
     */
    public function pay_usdt($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_fg_ok($user_id);
        if (!$ok){return 0;}
        PriceUsdtInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 18,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['usdt'=>$total_money]);
        return 1;
    }
    /**
     * @param $user_id
     * @param $status
     * @param $total_money
     * @param $price
     * @param $remark
     * @return int
     * 操作余额表
     */
    public function pay_fil($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_fg_ok($user_id);
        if (!$ok){return 0;}
        PriceFilInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 18,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['filcoin'=>$total_money]);
        return 1;
    }
    /**
     * @param $user_id
     * @param $status
     * @param $total_money
     * @param $price
     * @param $remark
     * @return int
     * 操作余额表
     */
    public function pay_fil_lock($user_id,$status,$total_money,$price,$remark){
        $ok = $this->pay_fg_ok($user_id);
        if (!$ok){return 0;}
        PriceLockFilInfo::query()->create([
            'user_id' => $user_id,
            'status' => $status,
            'total_money' => $total_money,
            'price' => $price,
            'type' => 18,
            'addtime' => time(),
            'adddate' => Carbon::now(),
            'remark' => $remark
        ]);
        User::query()->where(['id'=>$user_id])->update(['filcoin_lock'=>$total_money]);
        return 1;
    }

}
