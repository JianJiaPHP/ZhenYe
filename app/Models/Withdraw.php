<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Withdraw extends Model
{
    protected $table = 'withdraw';
    protected $fillable = ['id','user_id','username', 'type', 'number', 'fee', 'arrived_number', 'hash_pay','status','remark','created_at', 'updated_at','deleted_at'];

    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
