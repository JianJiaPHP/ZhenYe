<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

/**
 * 资源
 * Class AdminResource
 * @package App\Models
 */
class AdminResource extends Model
{
    protected $table = "admin_resource";
    protected $fillable = ['name', 'url', 'http_method', 'created_at', 'updated_at'];

    private static $KEY = "admin_resource";


    /**
     * 根据管理员获取资源
     * @return mixed|void
     * author hy
     */
    public static function getAdminResourceByAdministratorId($administratorId)
    {

        $key = self::$KEY . ":" . "administrator:" . $administratorId;
        $data = Redis::get($key);
        if (!$data) {
            // 所有的角色
            $roleIds = AdminRoleAdministrator::getRoleIdsByAdministratorId($administratorId);
            // 当前管理员的资源ids
            $resourceIds = AdminRoleResource::query()->whereIn('role_id', $roleIds)
                ->pluck('resource_id')->toArray();
            // 根据资源ids获取资源
            $adminResources = AdminResource::query()->whereIn('id', array_unique($resourceIds))
                ->get(['url', 'http_method'])
                ->toArray();

            Redis::set($key, json_encode($adminResources));
            return $adminResources;
        }
        return json_decode($data, true);
    }


    /**
     * 根据管理员id删除资源缓存
     * @param $administratorId
     * @return mixed
     * author hy
     */
    public static function delAdminResourceByAdministratorId($administratorId)
    {
        $key = self::$KEY . ":" . "administrator:" . $administratorId;
        return Redis::del($key);
    }


    /**
     * 删除所有的资源
     * @return mixed
     * author hy
     */
    public static function delAdminResourceAll()
    {
        $pattern = self::$KEY . ":" . "administrator:" . "*";
        $keys = Redis::keys($pattern);

        return Redis::del($keys);
    }

}
