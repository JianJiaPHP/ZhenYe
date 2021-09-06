<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Goods extends Model
{
    protected $table = 'goods';
    protected $fillable = ['id', 'title', 'stock', 'original_price','introduce', 'price', 'img', 'text', 'postage', 'type', 'created_at', 'updated_at'];

    /**
     * 商品分类名
     */
    public function type_name()
    {
        return $this->hasOne(GoodsType::class, "id", 'type')->select('id','name');
    }

    public function getImgAttribute($value)
    {
        return Files::query()->whereIn('id',explode(',',$value))->select('id','path')->get();
    }

}
