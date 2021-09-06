<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminLoginLog;
use App\Models\AdminOperatingLogs;

class LogController extends Controller
{
    /**
     * 操作日志列表
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     * @date 2019/12/13 下午4:05
     */
    public function operatingLog()
    {
        $params = request()->all();
        $data = AdminOperatingLogs::with('administrator')->orderBy('created_at', 'desc');

        $keyword = $params['keyword'];
        $data->when($keyword, function ($query, $keyword) {
            return $query->where('router', 'like', "%$keyword%")
                ->orWhere('method', 'like', "%$keyword%")
                ->orWhere('content', 'like', "%$keyword%")
                ->orWhere('desc', 'like', "%$keyword%");
        });

        $result = $data->paginate(request()->get('limit',15));

        return success($result);
    }

    /**
     * 登陆日志列表
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     * @date 2019/12/19 下午2:44
     */
    public function loginLog()
    {
        $params = request()->all();
        $data = AdminLoginLog::with('administrator')->orderBy('created_at', 'desc');

        $keyword = $params['keyword'];
        $data->when($keyword, function ($query, $keyword) {
            return $query->where('ip', 'like', "%$keyword%")
                ->orWhere('country', 'like', "%$keyword%")
                ->orWhere('region', 'like', "%$keyword%")
                ->orWhere('city', 'like', "%$keyword%")
                ->orWhere('county', 'like', "%$keyword%")
                ->orWhere('isp', 'like', "%$keyword%");
        });

        $result = $data->paginate(request()->get('limit',15));

        return success($result);

    }

}
