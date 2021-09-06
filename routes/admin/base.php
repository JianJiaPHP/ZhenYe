<?php

use Illuminate\Support\Facades\Route;


# 不需要登录的路由
Route::group([], function () {
    # 后台登陆
    Route::post('login', 'Base\AuthController@login');

    # 获取配置
    Route::get('getConfig/{key}', 'Base\ConfigController@getOne');
    Route::get('cai_ping', 'TimingController@cai_ping');

});


Route::namespace('Base')->group(function () {
    // 需要登录的
    Route::middleware(['admin.user'])->group(function () {
        // 个人信息
        Route::prefix('me')->group(function () {
            # 个人信息
            Route::get('/', 'MeController@me');
            # 退出登陆
            Route::get('logout', 'MeController@logout');
            # 修改个人密码
            Route::put('updatePassword', 'MeController@updatePwd');
            # 获取登陆者该有的导航栏
            Route::get('getNav', 'MeController@getNav');
            # 上传文件
            Route::post('upload', 'UploadController@upload');

        });
        # 用户表

        # 需要验证权限的
        Route::middleware(['admin.permission'])->group(function () {

            # 配置管理
            Route::get('config', 'ConfigController@index');
            Route::put('config/{id}', 'ConfigController@update');

            # 资源分类列表
            Route::apiResource('adminResourceCategory', 'AdminResourceCategoryController')->except(['show']);
            # 所有资源分类
//            Route::get('adminResourceCategoryAll', 'AdminResourceCategoryController@all');
            # 资源列表
            Route::apiResource('adminResource', 'AdminResourceController')->except(['show']);
            # 所有资源列表
            Route::get('adminResourceAll', 'AdminResourceController@all');
            # 菜单管理
            Route::apiResource('adminMenu', 'AdminMenuController')->except(['show']);
            # 所有菜单
            Route::get('adminMenuListAll', 'AdminMenuController@listAll');

            # 操作日志
            Route::get('operating_log', 'LogController@operatingLog');
            Route::get('login_log', 'LogController@loginLog');

            # 角色管理
            Route::apiResource('role', 'RoleController')->except(['show']);
            # 所有角色
            Route::get('rolesAll', 'RoleController@getAll');
            # 管理员管理
            Route::apiResource('administrators', 'AdministratorController')->except(['show']);
        });
        # OSS
        Route::post('upload', 'UserController@upload');
        # 用户管理
        Route::apiResource('user', 'UserController')->except(['show']);
        # fil矿机管理
        Route::apiResource('filorder', 'FilOrderController')->except(['show']);
        # hyc矿机管理
        Route::apiResource('hycorder', 'HycOrderController')->except(['show']);
        # 资金记录明细合集
        Route::apiResource('info', 'InfoController')->except(['show']);
        # 火币网账户 充值明细表
        Route::apiResource('usdt_with', 'UsdtWithdrawController')->except(['show']);
        # Banner
        Route::apiResource('banner', 'BannerController')->except(['show']);
        # 用户地址
        Route::apiResource('address', 'AddressController')->except(['show']);
        # 提现记录表
        Route::apiResource('withdraw', 'WithdrawController')->except(['show']);
        # 充值记录
        Route::apiResource('user_record', 'UsdtRecordController')->except(['show']);
        # Fil矿机商品
        Route::apiResource('goods_fil', 'MinerFilController')->except(['show']);
        # Hyc矿机商品
        Route::apiResource('goods_hyc', 'MinerHycController')->except(['show']);
        # 健康商城商品类别
        Route::apiResource('goods_type', 'GoodsTypeController')->except(['show']);
        # 健康商城商品管理
        Route::apiResource('goods', 'GoodsController')->except(['show']);
        # 健康商城商品订单管理
        Route::apiResource('order', 'OrderController')->except(['show']);
    });


});
