<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UsdtWithdraw extends Model
{
    protected $table = 'usdt_withdraw';
    protected $fillable = ['id','uid','currency', 'tx-hash', 'chain', 'amount', 'address', 'fee','state','error-code','error-msg', 'created-at','updated-at','created_at', 'updated_at','deleted_at'];


}
