<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Services\ConfigService;

class ConfigController extends Controller
{

    /**
     * 获取配置信息
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     * @date 2019/12/13 下午3:22
     */
    public function index()
    {
        $config = Config::query()
            ->get(['id', 'key', 'value'])->toArray();

        $data = [];
        foreach ($config as $k => $v) {
            $data[$v['key']] = [
                'value' => $v['value'],
                'id' => $v['id'],
            ];
        }

        return success($data);
    }


    /**
     * 修改配置信息
     * @param $id int 配置id
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     * @date 2019/12/13 下午3:24
     */
    public function update($id)
    {
        $params = request()->all();

        $result = (new Config())->updateOrCreate(['value' => $params['value']], $id);

        return choose($result);
    }


    /**
     * 根据key值获取
     * @param $key
     * @param ConfigService $service
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function getOne($key, ConfigService $service)
    {
        $data = $service->getOne($key);
        return success($data);
    }

}
