<?php

namespace App\Http\Controllers\Admin\Base;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequests;
use App\Models\Administrator;
use App\Models\AdminLoginLog;
use App\Utils\Ip;
use App\Utils\Upload;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * 登录
     * @param LoginRequests $loginRequests
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author Aii
     * @date 2020/7/21
     */
    public function login(LoginRequests $loginRequests)
    {
        $params = $loginRequests->validated();
        $exist = Administrator::query()->where('account', $params['account'])->first();
        if (!$exist) {
            return fail("账号不存在");
        }
        if (!Hash::check($params['password'], $exist->password)) {
            return fail("密码错误");
        }

        $token = auth('admin')->login($exist);
        if (!$token) {
            return fail();
        }
        $ips = request()->getClientIp();

        if ($ips != '127.0.0.1') {
            $result = Ip::getIpInfo($ips);
            if ($result) {
                AdminLoginLog::query()->create([
                    'uid' => $exist->id,
                    'ip' => $ips,
                    'country' => !empty($result['country']) ? $result['country'] : '',
                    'city' => !empty($result['regionName']) ? $result['regionName'] : '',
                ]);
            } else {
                AdminLoginLog::query()->create([
                    'uid' => $exist->id,
                    'ip' => $ips,
                ]);
            }
        }

        return success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60
        ]);
    }



}
