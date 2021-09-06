<?php

namespace App\Console\Commands;

use App\Models\FilGoods;
use App\Models\FilOrder;
use App\Models\LogInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceLockFilInfo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FilLinearRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FilLinearRelease';

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
}
