<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use Illuminate\Support\Facades\DB;

class AdminResourceController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data = AdminResource::query()->orderBy('id', 'desc');
        $name = request()->query('name');
        $limit = request()->query('limit', 10);
        if ($name) {
            $data = $data->where('name', 'like', "%$name%");
        }
        $data = $data->paginate($limit);
        return success($data);
    }

    /**
     * 添加
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function store()
    {
        $params = request()->all();

        $result = AdminResource::query()->create([
            'name' => $params['name'],
            'http_method' => $params['http_method'],
            'url' => $params['url'],
        ]);

        // 删除所有的缓存
        AdminResource::delAdminResourceAll();

        return choose($result);
    }

    /**
     * 更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        $params = request()->all();

        $result = AdminResource::query()->where('id', $id)->update([
            'name' => $params['name'],
            'http_method' => $params['http_method'],
            'url' => $params['url'],
        ]);
        // 删除所有的缓存
        AdminResource::delAdminResourceAll();
        return choose($result);
    }

    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = AdminResource::query()->where('id', $id)->delete();
            if (!$result) {
                throw new \Exception('系统超时');
            }
            if (AdminRoleResource::query()->where('resource_id',$id)->exists()) {
                $adminRoleResource = AdminRoleResource::query()->where('resource_id', $id)->delete();
                if (!$adminRoleResource) {
                    throw new \Exception('系统超时');
                }
            }
            // 删除所有的缓存
            AdminResource::delAdminResourceAll();
            DB::commit();
            return success();
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }

    }

    /**
     * 获取所有的资源
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function all()
    {
        $result = AdminResource::query()
            ->get(['id', 'name', 'url', 'http_method'])
            ->toArray();

        return success($result);
    }
}
