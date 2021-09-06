<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PriceLockFilInfo extends Model
{
    protected $table = 'price_lock_fil_info';
    protected $fillable = ['id','user_id','username', 'status', 'total_money', 'price', 'type','remark', 'addtime','adddate','created_at', 'updated_at','deleted_at'];


    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
