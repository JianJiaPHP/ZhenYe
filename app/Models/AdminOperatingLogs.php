<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AdminOperatingLogs extends Model
{
    protected $table = 'admin_operating_logs';
    protected $fillable = ['id','uid','router','method','content','desc','ip','created_at','updated_at'];

    /**
     * 关联管理员
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function administrator()
    {
        return $this->hasOne(Administrator::class,'id','uid');
    }

}
