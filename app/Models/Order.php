<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    protected $table = 'order';
    protected $fillable = ['id','user_id','address_id', 'goods_list', 'order_number', 'logistics','pay_states', 'type', 'price','created_at', 'updated_at','deleted_at'];


    /**
     * 商品明细
     * @param $value
     * @return string
     */
    public function Order_list()
    {
        return $this->hasMany(OrderList::class, "order_id", 'id')->select('order_id','goods_id','sum');
    }

    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }

    public function address_data()
    {
        return $this->hasOne(Address::class, "id", 'address_id');

    }
}
