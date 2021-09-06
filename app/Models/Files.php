<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * 文件
 * Class Files
 * @package App\Models
 */
class Files extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'file_size', 'original_name', 'object', 'path', 'uploader_id', 'uploader_type', 'upload_at'
    ];

    public $timestamps = false;

}
