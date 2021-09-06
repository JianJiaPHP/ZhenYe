<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class Address extends Model
{
    use SoftDeletes;
    protected $table = 'address';
    protected $fillable = ['id','user_id', 'address_name', 'consignee', 'address','phone', 'status','created_at', 'updated_at', 'deleted_at'];


    public function user_list()
    {
        return $this->hasOne(User::class, "id", 'user_id')->select('id','phone');
    }

}
