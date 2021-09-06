<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Interchange extends Model
{
    protected $table = 'interchange';
    protected $fillable = ['id','type','out_number', 'into_number', 'fee_number', 'rate', 'out_user_id', 'into_user_id','out_username','into_username','addtime', 'adddate'];

    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }
}
