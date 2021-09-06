<?php

namespace App\Console\Commands;

use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\HycOrder;
use App\Models\LogInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceUsdtInfo;
use App\Models\UsdtRecord;
use App\Models\UsdtWithdraw;
use App\Models\User;
use App\Utils\Pay;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UsdtRecharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UsdtRecharge';

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
        //火币充值记录
        $where[] = ['updated_at','>',Carbon::now()->addHour(-1)];
        $where['status'] = 1;
        $list = UsdtRecord::query()->where($where)->get()->toArray();
        //充值查询
        $whereT[] = ['updated-at','>',Carbon::now()->addHour(-1)];
        $whereT['currency'] = 'usdt';
        $whereT['state'] = 'safe';
        foreach ($list as $v){
            $whereT1['amount'] = $v['num'];
            $whereT2['amount'] = $v['num']-1;
            $data = UsdtWithdraw::query()->where($whereT)->where($whereT1)->first()?UsdtWithdraw::query()->where($whereT)->where($whereT2)->first():[];
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
        return success(1);
    }
}
