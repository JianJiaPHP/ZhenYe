<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PriceUsdtInfo extends Model
{
    protected $table = 'price_usdt_info';
    protected $fillable = ['id','user_id','username', 'status', 'total_money', 'price', 'type', 'addtime','adddate','remark','created_at', 'updated_at','deleted_at'];


    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
