<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class FilOrder extends Model
{
    use SoftDeletes;
    protected $table = 'order_miner_fil';
    protected $fillable = ['id','order_number','pay_states', 'type', 'price', 'validity_at','real_price','type_bi', 'user_id','real_pay','pledge_fil_id','pledge_hyc_id','pledge_status','goods_id','count','created_at', 'updated_at','deleted_at'];


    /**
     * 商品明细
     * @param $value
     * @return string
     */
    public function goods_list()
    {
        return $this->hasOne(FilGoods::class, "id", 'goods_id');
    }

}
