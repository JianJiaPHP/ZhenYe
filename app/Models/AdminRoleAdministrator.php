<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

/**
 * 管理员角色
 * Class AdminRoleAdministrator
 * @package App\Models
 */
class AdminRoleAdministrator extends Model
{
    protected $table = 'admin_role_administrator';
    protected $fillable = ['administrator_id', 'role_id', 'created_at'];

    public $timestamps = false;

    private static $KEY = "admin_role_administrator";

    /**
     * 根据管理员获取角色
     * @param $administratorId
     * @return array|mixed
     * author hy
     */
    public static function getRoleIdsByAdministratorId($administratorId)
    {
        $key = self::$KEY . ":" . "administrator:" . $administratorId;
        $data = Redis::get($key);
        if (!$data) {
            $roleIds = self::query()->where('administrator_id', $administratorId)
                ->pluck('role_id')->toArray();
            Redis::set($key, json_encode($roleIds));

            return $roleIds;
        }
        return json_decode($data, true);
    }

    /**
     * 根据管理员删除缓存
     * @param $administratorId
     * @return mixed
     * author hy
     */
    public static function delRoleIdsByAdministratorId($administratorId)
    {
        $key = self::$KEY . ":" . "administrator:" . $administratorId;
        return Redis::del($key);
    }


}
