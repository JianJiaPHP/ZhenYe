<?php

namespace App\Http\Middleware;

use App\Models\Administrator;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use Closure;

class AdminMiddleware
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
        if (!auth('admin')->check()) {
            return otherReturn(401, '请重新登录');
        }

        $user = auth('admin')->user();
        //如果没有就重新登录
        if (!$user) {
            return otherReturn(401, '请重新登录');
        }

        return $next($request);
    }



}
