<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CoinBuy extends Model
{
    protected $table = 'coin_buy';
    protected $fillable = ['id','type','user_id', 'username', 'status', 'price','total_money', 'total_number', 'get_number','addtime', 'adddate','updatetime','created_at','updated_at'];



}
