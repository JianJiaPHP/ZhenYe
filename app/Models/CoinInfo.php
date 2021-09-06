<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CoinInfo extends Model
{
    protected $table = 'coin_info';
    protected $fillable = ['id', 'price', 'status', 'adddate', 'addtime','created_at','updated_at'];



}
