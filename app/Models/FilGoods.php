<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class FilGoods extends Model
{
    use SoftDeletes;
    protected $table = 'miner_fil';
    protected $fillable = ['id', 'title', 'text', 'day', 'img', 'price', 'trusteeship', 'gas_price', 'pledge','produce','state','reward', 'created_at', 'updated_at'];


    public function getImgAttribute($value)
    {
        return Files::query()->whereIn('id',explode(',',$value))->select('id','path')->get();
    }

}
