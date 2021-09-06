<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UsdtRecord extends Model
{
    protected $table = 'usdt_record';
    protected $fillable = ['id','user_id','username', 'num', 'hash_pay', 'status', 'remark', 'created-at','updated-at','created_at', 'updated_at','deleted_at'];

    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
