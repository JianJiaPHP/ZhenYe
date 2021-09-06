<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

/**
 * 后台菜单
 * Class AdminMenu
 * @package App\Models
 */
class AdminMenu extends Model
{
    protected $table = "admin_menu";
    protected $fillable = ['parent_id', 'path', 'icon', 'name', 'sort', 'is_hidden', 'created_at', 'updated_at'];


    private static $KEY = "admin_menu";

    /**
     * 获取所有菜单
     * @return array
     * author hy
     */
    public static function getAll()
    {
        $data = self::query()->orderBy('sort')->get()->toArray();

        return self::unlimitedMenu($data, 0);
    }


    /**
     * 根据管理员获取菜单
     * @param $id
     * @return array
     * author hy
     */
    public static function getNav($id)
    {
        $key = self::$KEY . ":" . "administrator:" . $id;
        $data = Redis::get($key);
        // 缓存存在
        if ($data) {
            return json_decode($data, true);
        }
        // 所有的角色
        $roleIds = AdminRoleAdministrator::getRoleIdsByAdministratorId($id);

        if (in_array(1, $roleIds)) {
            // 当前的菜单
            $menus = self::query()->orderBy('sort')->get()->toArray();
        } else {
            // 当前的菜单
            // 当前管理员的菜单ids
            $menuIds = AdminRoleMenu::query()->whereIn('role_id', $roleIds)
                ->pluck('menu_id')->toArray();

            // 根据资源ids获取资源
            $menus = AdminMenu::query()->whereIn('id', array_unique($menuIds))
                ->get()
                ->toArray();
        }

        $data = self::unlimitedMenu($menus, 0);

        Redis::set($key, json_encode($data));
        return $data;
    }


    /**
     * 删除所有的菜单缓存
     * @return mixed
     * author hy
     */
    public static function delAdminMenuAll()
    {
        $pattern = self::$KEY . ":" . "administrator:" . "*";
        $keys = Redis::keys($pattern);
        return Redis::del($keys);
    }

    /**
     * 根据管理员删除菜单缓存
     * @return mixed
     * author hy
     */
    public static function delAdminByAdministratorId($id)
    {
        $key = self::$KEY . ":" . "administrator:" . $id;

        return Redis::del($key);
    }

    /**
     * 获取菜单
     * @param $menus
     * @param $parentId
     * @return array
     * author hy
     */
    public static function unlimitedMenu($menus, $parentId)
    {
        $data = [];
        foreach ($menus as $v) {
            if ($v['parent_id'] == $parentId) {
                //保存下来，然后继续找儿子的儿子
                $children = $v;
                //递归调用
                $children['children'] = self::unlimitedMenu($menus, $v['id']);
                if (empty($children['children'])) {
                    //如果不存在$child['children']就销毁这个变量
                    unset($children['children']);
                }
                //追加进$result
                array_push($data, $children);
            }
        }
        return $data;

    }

}
