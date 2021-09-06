<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Verify extends Model
{
    protected $table = 'verify';
    protected $fillable = ['id', 'addtime', 'phone', 'number', 'delete','created_at', 'updated_at'];



}
