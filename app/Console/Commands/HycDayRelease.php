<?php

namespace App\Console\Commands;

use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\HycOrder;
use App\Models\LogInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\User;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HycDayRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HycDayRelease';

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
        $list = HycOrder::query()->where(['status'=>1,'stage'=>null])->get()->toArray();
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
                    HycOrder::query()->where('id',$value['id'])->update(['status'=>0,'total_price'=>$total_price,'last_time'=>Carbon::now()]);
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

            //类型2
        //发放收益 ， 购买金额质押 矿机过期HYC放回
        $pay = new Pay();
        $list = HycOrder::query()->where(['status'=>1,'stage'=>1])->get()->toArray();
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
}
