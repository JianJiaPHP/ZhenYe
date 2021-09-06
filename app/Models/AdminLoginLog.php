<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AdminLoginLog extends Model
{
    protected $table = "admin_login_logs";
    protected $fillable = ['id','uid','ip','country','city','created_at','updated_at'];

    /**
     * 关联管理员
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function administrator()
    {

        return $this->hasOne(Administrator::class,'id','uid');
    }
}
