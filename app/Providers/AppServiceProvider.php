<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 获取所有接口
        $interfaces = $this->getFiles(app_path('Services'));
        // 获取所有的impls
        $impls = $this->getFiles(app_path('Services/impl'));
        sort($interfaces);
        sort($impls);
        // 合并
        $services = array_combine($interfaces, $impls);

        // 注入
        foreach ($services as $k => $v) {
            $this->app->bind('App\\Services\\' . $k, 'App\\Services\impl\\' . $v);
        }
//
//        \DB::listen(function ($query) {
//            $sql=$query->sql;
//            $bindings=$query->bindings;
//            $time=$query->time;
//
//            \Log::debug(json_encode($query));
////            \Log::debug(var_export(compact('sql','bindings','time'),true));
//
//        });
    }


    /**
     * 获取所有文件
     * @param $file
     * @return array
     * author hy
     */
    private function getFiles($file)
    {
        $array = array();
        //1、先打开要操作的目录，并用一个变量指向它
        //打开当前目录下的目录pic下的子目录common。
        $handler = opendir($file);
        //2、循环的读取目录下的所有文件
        /* 其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，为了不陷于死循环，所以还要让$filename !== false。一定要用!==，因为如果某个文件名如果叫’0′，或者某些被系统认为是代表false，用!=就会停止循环 */
        while (($filename = readdir($handler)) !== false) {
            // 3、目录下都会有两个文件，名字为’.'和‘..’，不要对他们进行操作
            if (substr(strrchr($filename, '.'), 1) == 'php') {
                // 4、进行处理
                array_push($array, str_replace(strrchr($filename, "."), "", $filename));
            }
        }
        //5、关闭目录
        closedir($handler);
        return $array;
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
