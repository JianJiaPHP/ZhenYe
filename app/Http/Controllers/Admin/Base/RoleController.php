<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\AdminResource;
use App\Models\AdminRole;
use App\Models\AdminRoleAdministrator;
use App\Models\AdminRoleMenu;
use App\Models\AdminRoleResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * 角色列表
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function index()
    {
        $params = request()->all();
        $data = AdminRole::query()->where('id', '>', 1)
            ->with(['menus', 'resources'])->orderBy('id', 'desc');

        $keyword = $params['name'];
        $data->when($keyword, function ($query, $keyword) {
            return $query->where('name', 'like', "%$keyword%")
                ->orWhere('desc', 'like', "%$keyword%");
        });

        $result = $data->paginate(request('limit', 15));
        return success($result);
    }

    /**
     * 角色添加
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function store()
    {
        $params = request()->all();

        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            $role = AdminRole::query()->create([
                'name' => $params['name'],
                'desc' => $params['description'],
            ]);
            if (!$role) {
                throw new \Exception('系统超时');
            }
            if (!empty($params['menus'])) {
                $menuData = [];
                $menus = explode(',', $params['menus']);
                foreach ($menus as $v) {
                    $menuData[] = [
                        'menu_id' => $v,
                        'role_id' => $role->id,
                        'created_at' => $now
                    ];
                }
                $adminRoleMenu = AdminRoleMenu::query()->insert($menuData);
                if (!$adminRoleMenu) {
                    throw new \Exception('系统超时');
                }
            }

            if (!empty($params['resources'])) {
                $resourcesData = [];
                $resources = explode(',', $params['resources']);
                foreach ($resources as $v) {
                    $resourcesData[] = [
                        'resource_id' => $v,
                        'role_id' => $role->id,
                        'created_at' => $now
                    ];
                }
                $adminRoleResource = AdminRoleResource::query()->insert($resourcesData);
                if (!$adminRoleResource) {
                    throw new \Exception('系统超时');
                }
            }

            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }


    /**
     * 角色更新
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function update($id)
    {
        $params = request()->all();


        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            $result = AdminRole::query()->where('id', $id)->update([
                'name' => $params['name'],
                'desc' => $params['description'],
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            // 删除角色资源
            $adminRoleResource = AdminRoleResource::query()->where('role_id', $id)->delete();
            if (!$adminRoleResource) {
                throw new \Exception('系统超时');
            }
            // 删除角色菜单
            $adminRoleMenu = AdminRoleMenu::query()->where('role_id', $id)->delete();
            if (!$adminRoleMenu) {
                throw new \Exception('系统超时');
            }
            // 添加角色菜单
            if (!empty($params['menus'])) {
                $menuData = [];
                $menus = explode(',', $params['menus']);
                foreach ($menus as $v) {
                    $menuData[] = [
                        'menu_id' => $v,
                        'role_id' => $id,
                        'created_at' => $now
                    ];
                }
                $adminRoleMenu = AdminRoleMenu::query()->insert($menuData);
                if (!$adminRoleMenu) {
                    throw new \Exception('系统超时');
                }
            }

            // 添加角色资源
            if (!empty($params['resources'])) {
                $resourcesData = [];
                $resources = explode(',', $params['resources']);
                foreach ($resources as $v) {
                    $resourcesData[] = [
                        'resource_id' => $v,
                        'role_id' => $id,
                        'created_at' => $now
                    ];
                }
                $adminRoleResource = AdminRoleResource::query()->insert($resourcesData);
                if (!$adminRoleResource) {
                    throw new \Exception('系统超时');
                }
            }
            // 和此角色有关的管理员ID
            $administratorIds = AdminRoleAdministrator::query()->where('role_id', $id)
                ->pluck('administrator_id')->toArray();
            foreach ($administratorIds as $v) {
                // 删除资源缓存
                AdminResource::delAdminResourceByAdministratorId($v);
                // 删除菜单缓存
                AdminMenu::delAdminByAdministratorId($v);
            }

            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }

    }

    /**
     * 角色删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function destroy($id)
    {
        $id = explode(',', $id);

        $exist = AdminRoleAdministrator::query()->where('role_id', $id)->exists();
        if ($exist) {
            return fail('该角色下还有管理员不能删除');
        }

        DB::beginTransaction();
        try {

            $result = AdminRole::destroy($id);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            $adminRoleResource = AdminRoleResource::query()->where('role_id', $id)->delete();
            if (!$adminRoleResource) {
                throw new \Exception('系统超时');
            }
            $adminRoleMenu = AdminRoleMenu::query()->where('role_id', $id)->delete();
            if (!$adminRoleMenu) {
                throw new \Exception('系统超时');
            }
            // 和此角色有关的管理员ID
            $administratorIds = AdminRoleAdministrator::query()->where('role_id', $id)
                ->pluck('administrator_id')->toArray();
            foreach ($administratorIds as $v) {
                // 删除资源缓存
                AdminResource::delAdminResourceByAdministratorId($v);
                // 删除菜单缓存
                AdminMenu::delAdminByAdministratorId($v);
            }

            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 获取所有角色
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function getAll()
    {
        $data = AdminRole::query()
            ->orderBy('id', 'desc')
            ->select('id', 'name')
            ->get();
        return success($data);
    }

}
