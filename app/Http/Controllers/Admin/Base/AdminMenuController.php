<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\AdminRoleMenu;
use Illuminate\Support\Facades\DB;

class AdminMenuController extends Controller
{

    /**
     * 菜单列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data = AdminMenu::getAll();

        return success($data);
    }

    /**
     * 菜单添加
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function store()
    {
        $params = request()->all();

        $result = AdminMenu::query()->create([
            'parent_id' => $params['parent_id'],
            'path' => $params['path'],
            'icon' => $params['icon'],
            'name' => $params['name'],
            'sort' => $params['sort'],
            'is_hidden' => $params['is_hidden'],
        ]);
        // 删除缓存
        AdminMenu::delAdminMenuAll();
        return choose($result);
    }

    /**
     * 菜单更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        $params = request()->all();

        $result = AdminMenu::query()->where('id', $id)->update([
            'parent_id' => $params['parent_id'],
            'path' => $params['path'],
            'icon' => $params['icon'],
            'name' => $params['name'],
            'sort' => $params['sort'],
            'is_hidden' => $params['is_hidden'],
        ]);
        // 删除缓存
        AdminMenu::delAdminMenuAll();
        return choose($result);
    }

    /**
     * 删除菜单
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = AdminMenu::query()->where('id', $id)->delete();
            if (!$result) {
                throw new \Exception('系统超时');
            }
            if (AdminRoleMenu::query()->where('menu_id', $id)->exists()) {
                $adminRoleResource = AdminRoleMenu::query()->where('menu_id', $id)->delete();
                if (!$adminRoleResource) {
                    throw new \Exception('系统超时');
                }
            }

            // 删除缓存
            AdminMenu::delAdminMenuAll();
            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 所有列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function listAll()
    {
        $data = AdminMenu::getAll();

        array_push($data, [
            'parent_id' => 0,
            'name' => '顶级',
            'id' => 0
        ]);

        return success($data);
    }

}
