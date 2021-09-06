<?php

namespace App\Http\Middleware;

use App\Models\AdminOperatingLogs;
use Closure;

class OperatingLogsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $id = auth('admin')->id();
        //如果没有找到就说明没有登录
        if (!$id) {
            return otherReturn(401,'请重新登录');
        }
        $route = $request->route()->uri;
        $method = $request->method();
        if ($method == 'GET'){
            //如果是获取数据不记录日志
            return $next($request);
        }
        switch ($method){
            case 'POST':
                $content = json_encode($request->all());
                $desc = "执行了添加";
                break;
            case 'PUT':
                $content = json_encode($request->all());
                $desc = "执行了更新";
                break;
            case 'DELETE':
                $content = request()->route('id');
                $desc = "执行了删除";
                break;
            default:
                $content = json_encode($request->all());
                $desc = '';
                break;
        }
        AdminOperatingLogs::create([
            'uid'=>$id,
            'router'=>$route,
            'method'=>$method,
            'content'=>$content,
            'desc'=>$desc,
            'ip'=>$request->getClientIp()
        ]);
        return $next($request);
    }
}
