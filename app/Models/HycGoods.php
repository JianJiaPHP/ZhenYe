<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class HycGoods extends Model
{
    use SoftDeletes;
    protected $table = 'miner_hyc';
    protected $fillable = ['id', 'title', 'img', 'price', 'total_price','price_hyc','state', 'day', 'number', 'reward' ,'limit_count', 'created_at', 'updated_at','deleted_at'];


    public function getImgAttribute($value)
    {
        return Files::query()->whereIn('id',explode(',',$value))->select('id','path')->get();
    }
}
