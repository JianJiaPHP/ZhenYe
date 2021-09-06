<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrderList extends Model
{
    protected $table = 'order_list';
    protected $fillable = ['id', 'order_id', 'goods_id', 'sum', 'created_at', 'updated_at','deleted_at'];


    /**
     * 商品明细
     */
    public function Goods_list(){
        return $this->hasOne(Goods::class,'id','goods_id');
    }
}
