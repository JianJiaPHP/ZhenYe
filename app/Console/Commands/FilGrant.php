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

class FilGrant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FilGrant';

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
                'remark'=>"fil矿机产币收益--线性释放库",
            ]);
            User::query()->where(['id'=>$v['user_id']])->update(['filcoin_lock'=>$user_filcoin['filcoin_lock'] + ($produce*0.75)]);
        }
        return success(1);
    }
}
