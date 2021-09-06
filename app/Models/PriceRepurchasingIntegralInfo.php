<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PriceRepurchasingIntegralInfo extends Model
{
    protected $table = 'price_repurchasing_integral_info';
    protected $fillable = ['id','user_id','username', 'status', 'total_money', 'price', 'type','remark', 'addtime','adddate','created_at', 'updated_at'];


    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
