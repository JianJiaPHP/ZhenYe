<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * 文件
 * Class Files
 * @package App\Models
 */
class Banner extends Model
{
    protected $table = 'banner';

    protected $fillable = [
        'id', 'img', 'text', 'title', 'is_top', 'type', 'created_at', 'updated_at', 'deleted_at'
    ];

}
