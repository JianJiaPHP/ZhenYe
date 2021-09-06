<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GoodsType extends Model
{
    protected $table = 'goods_type';
    protected $fillable = ['id', 'name', 'created_at', 'updated_at'];



}
