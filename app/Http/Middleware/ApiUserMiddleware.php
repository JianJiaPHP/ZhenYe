<?php


namespace App\Http\Middleware;


use App\Models\UserActive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class ApiUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {

        if (!auth('api')->check()) {
            return otherReturn(401, '请重新登录');
        }
        $userId = auth('api')->id();
        //如果没有就重新登录
        if (!$userId) {
            return otherReturn(401, '请重新登录');
        }
//        // redis 中是否存在
//        $key = 'user:' . $userId;
//        if (!Redis::EXISTS($key)) {
//            return otherReturn(401, '请重新登录');
//        }
//        $len = strlen('Bearer ');
//        $token = substr(request()->header('authorization'), $len);
//        if (Redis::get($key) != $token) {
//            return otherReturn(401, '您的账号已在别处登录');
//        }
//
//        $today = Carbon::now()->toDateString();
//        if (!Redis::HEXISTS($today, $key)) { //先判断redis 是否存在
//            Redis::hset($today, $key, 1); //不存在就加上
//            if (!UserActive::query()->whereDate('created_at', $today)
//                ->where('user_id', $userId)->exists()) { //在判断数据库 一天一个用户只能有一条数据
//                UserActive::query()->create([
//                    'user_id' => $userId,
//                    'created_at' => Carbon::now()->toDateTimeString()
//                ]);
//
//            }
//
//        }


        return $next($request);
    }

}
