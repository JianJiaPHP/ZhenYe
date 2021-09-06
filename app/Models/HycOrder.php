<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class HycOrder extends Model
{
    use SoftDeletes;
    protected $table = 'miner_hyc_order';
    protected $fillable = ['id','user_id','miner_id', 'type','stage', 'price_hyc','status', 'day', 'price', 'total_price','number','day_price','run_day','end_time','last_time','created_at', 'updated_at','deleted_at'];


    /**
     * 商品明细
     * @param $value
     * @return string
     */
    public function goods_list()
    {
        return $this->hasOne(HycGoods::class, "id", "miner_id");
    }

}
