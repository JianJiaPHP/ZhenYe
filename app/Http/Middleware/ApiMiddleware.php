<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use http\Exception\BadConversionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ApiMiddleware
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

        $params = $request->all();
//         $s = auth('api')->user();
//         dd($s);exit();
//        $payload = auth('admin')->payload();
//
//        $tokrn = auth('api')->claims(['sub' => 'bar', 'admin_user_id' => 1220])
//            ->login(User::query()->first());
//
//        dd($tokrn);
        $apiSign = config('api.sign');
        //检查是否开始api的sign  没有开始直接下一步
        if (!$apiSign) {
            return $next($request);
        }
        $apiKey = config('api.key');
        $ywSign = $request->header('YwSign');
        $ywTime = $request->header('YwTime');
        // 随机数
        $ywRandom = $request->header('YwRandom');

        if (empty($ywSign) || empty($ywTime)) {
            return otherReturn(101, 'signFail');
        }
        if (abs($ywTime - time()) > 600) {
            return otherReturn(101, 'signFail');
        }

        //Random是否存在于redis中，检查当前请求是否是重复请求
        $key = "apisign:" . $ywTime . ":" . $ywRandom;
        // 存在就重复提交
        if (Redis::get($key)) {
            return otherReturn(101, 'signFail');
        }

        ksort($params);
        $signArr = [];
        foreach ($params as $k => $v) {
            if ($v != "") {
                array_push($signArr, $k . '=' . urldecode($v));
            }
        }
        array_push($signArr, "key=" . $apiKey);
        $sign = implode('&', $signArr);
        $sign = strtoupper(md5($sign));
        if ($sign != $ywSign) {
            return otherReturn(101, 'signFail');
        }
        //将timestamps+random存进redis 一天过期
        Redis::SETEX($key, 60 * 60 * 24, $ywRandom);
        return $next($request);
    }


}
