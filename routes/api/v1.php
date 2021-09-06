<?php
Route::prefix('v1')->namespace('V1')->group(function () {
    # 不需要登录的路由
    Route::group([], function () {
        Route::get('deposit-withdraw', 'PingController@deposit_withdraw');
        Route::get('coin-info', 'PingController@coin_info');
        #项目配置数据
        Route::get('version', 'LoginController@version');
        #登陆接口
        Route::post('login', 'LoginController@login');
        #注册
        Route::post('register', 'LoginController@register');
        #登录
        Route::post('login', 'LoginController@login');
        #修改密码
        Route::put('forgetpass', 'LoginController@forgetpass');
        #发送短信
        Route::post('sendSms', 'LoginController@sendSms');

        Route::post('upload', 'LoginController@upload');

        Route::get('banner', 'LoginController@banner');
        #实时币价
        Route::get('price_bi', 'LoginController@price_bi');

        Route::get('data-transfer', 'PingController@DataTransfer');

        Route::get('data-hyc-mine', 'PingController@DataHycMine');

        Route::get('data-fil-mine', 'PingController@DataFilMine');
    });

    Route::middleware('api.user')->group(function (){
        #修改交易密码
        Route::put('forget-pay-pass','LoginController@forget_pay_pass');
        ##个人中心板块-------------------------------
            #新增我的地址
            Route::post('address-add','UserController@address_add');
            #查询直推下级 / 间接下级
            Route::post('get-subordinate','UserController@get_subordinate');
            #我的收货地址列表
            Route::get('address-list','UserController@address_list');
            #删除收货地址
            Route::delete('address-del','UserController@address_del');
            #修改收货地址
            Route::put('address-update','UserController@address_update');
        ##健康商城
            #商品分类
            Route::get('goods-type','GoodsController@goods_type');
            #商品购买
            Route::post('pay-goods','GoodsController@pay_goods');
            #商品列表
            Route::get('goods-list','GoodsController@goods_list');
            #热搜商品
            Route::get('goods-hot','GoodsController@goods_hot');
            #商品详情
            Route::get('goods-details','GoodsController@goods_details');
            #生成订单
            Route::post('order-create','GoodsController@order_create');
            #订单列表
            Route::post('order-list','GoodsController@order_list');
            #查询订单详情
            Route::get('order-selete','GoodsController@order_selete');
                #商品加入购物车
                Route::post('add-cart','CartController@add_cart');
                #查询用户购物车
                Route::post('list-cart','CartController@cart_list');
                #修改购物车某一个商品数量
                Route::post('hincry-cart','CartController@hincry_cart');
                #删除购物车某一个商品
                Route::post('del-cart','CartController@del_cart');
        #fil矿机商城
        Route::get('fil-goods-list','FilGoodsController@fil_goods_list');
        #fil矿机生成订单
        Route::post('miner-order-create','FilGoodsController@miner_order_create');
        #fil商品详情
        Route::post('miner-order-details','FilGoodsController@miner_order_details');
        #fil矿机订单列表
        Route::get('miner-order-list','FilGoodsController@miner_order_list');
        #fil 购买
        Route::post('pay-fil','FilGoodsController@pay_fil');

            #hyc列表
            Route::get('hyc-goods-list','HycGoodsController@hyc_goods_list');
            #hyc矿机生成订单
            Route::post('hyc-miner-order-create','HycGoodsController@hyc_miner_order_create');
            #hyc商品详情
            Route::post('hyc-miner-order-details','HycGoodsController@hyc_miner_order_details');
            #hyc矿机订单列表
            Route::get('hyc-miner-order-list','HycGoodsController@hyc_miner_order_list');
            #运行HYC矿机
            Route::put('hyc-on-status','HycGoodsController@hyc_on_status');

            Route::get('fil_ping','TimingController@fil_ping');


    });
});





