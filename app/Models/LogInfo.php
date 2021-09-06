<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LogInfo extends Model
{
    protected $table = 'admin_operating_logs';
    protected $fillable = ['id', 'uid', 'router', 'method', 'content','desc','ip','created_at', 'updated_at'];



}
