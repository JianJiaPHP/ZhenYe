<?php

namespace App\Console\Commands;

use App\Models\CoinBuy;
use App\Models\CoinSell;
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

class CancelTheOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CancelTheOrder';

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
        //撤销我要买订单
        $pay = new Pay();
        $where['status'] = 1;
        $where[] = ['adddate','<',Carbon::now()];
        $list_buy = CoinBuy::query()->where($where)->get()->toArray();
        foreach ($list_buy as $v){
            $moeny = $v['total_money'] - ($v['price']*$v['get_number']);
            if ($moeny >0){
                $user_cny = User::query()->where(['id'=>$v['user_id']])->value('balance');
                $pay->pay_cny($v['user_id'],1,$user_cny+$moeny,$moeny,'挂买撤回');
                CoinBuy::query()->where(['id'=>$v['id']])->update(['status'=>3]);
            }
        }
        $list_sell = CoinSell::query()->where($where)->get()->toArray();
        foreach ($list_sell as $value){
            $coin = $value['total_number'] - $value['get_number'];
            if ($coin >0 && $value['type'] == 1){
                $user_hyc = User::query()->where(['id'=>$value['user_id']])->value('hyc');
                $pay->pay_hyc($value['user_id'],1,$user_hyc+$coin,$coin,'挂卖撤回');
                CoinSell::query()->where(['id'=>$value['id']])->update(['status'=>3]);
            }elseif ($coin >0 && $value['type'] == 2){
                $user_fil = User::query()->where(['id'=>$value['user_id']])->value('filcoin');
                $pay->pay_fil($value['user_id'],1,$user_fil+$coin,$coin,'挂卖撤回');
                CoinSell::query()->where(['id'=>$value['id']])->update(['status'=>3]);
            }
        }
        return success(1);
    }
}
