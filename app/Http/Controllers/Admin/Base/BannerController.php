<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BannerController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data = Banner::query()->orderBy('id', 'desc');
        $name = request()->query('type');
        $limit = request()->query('limit', 10);
        if ($name) {
            $data = $data->where('type',  $name);
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
        $result = Banner::query()->create([
            'img' => $params['img'],
            'text' => $params['text'],
            'title' => $params['title'],
            'type' => $params['type'],
            'is_top' => $params['is_top'],
        ]);
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
        $result = Banner::query()->where('id', $id)->update([
            'img' => $params['img'],
            'text' => $params['text'],
            'title' => $params['title'],
            'type' => $params['type'],
            'is_top' => $params['is_top'],
        ]);
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
            $result = Banner::query()->where('id', $id)->delete();
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return success();
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }


}
