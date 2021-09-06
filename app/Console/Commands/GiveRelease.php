<?php

namespace App\Console\Commands;

use App\Models\CoinInfo;
use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\HycOrder;
use App\Models\LogInfo;
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
use Illuminate\Console\Command;

class GiveRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GiveRelease';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pay = new Pay();
        $where[] =  ['created_at','>',Carbon::now()->addYear(-1)];
        $where['status'] = 1;
        $list = PriceGiftIntegralInfo::query()->where($where)->get()->toArray();
        $list = array_group_by($list,'user_id');
        foreach ($list as $k=>$v){
            $sum = 0;
            $user = User::query()->where(['id'=>$k])->select('id','balance','gift_integral','repurchasing_integral')->first();
            foreach ($v as $k1=>$v1){
                $sum = round($sum + $v1['price']/365, 4);
            }
            //扣赠送积分
            $pay->pay_give($k,2,$user['gift_integral']+$sum,$sum,"赠送积分线性释放扣除");
            //释放余额
            $cny_sum = round($sum*0.7, 4);
            $pay->pay_cny($k,1,$user['balance']+$cny_sum,$cny_sum,"线性释放余额");
            //线性释放复购积分
            $fg_sum = round($sum*0.3, 4);
            $pay->pay_fg($k,1,$user['repurchasing_integral']+$fg_sum,$fg_sum,"线性释放复购积分");
        }
        //HYC币价增长
        $price = CoinInfo::query()->orderBy('id','desc')->value('price');
        CoinInfo::query()->create(['price'=>$price + 0.02,'adddate'=>Carbon::now()]);
        return success(1);
    }
}
