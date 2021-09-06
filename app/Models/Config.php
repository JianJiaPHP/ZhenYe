<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Config extends Model
{
    protected $table = 'config';
    protected $fillable = ['id', 'group', 'key', 'value', 'desc', 'created_at', 'updated_at'];

    /**
     * 获取所有配置信息
     * @return mixed
     * @author Aii
     * @date 2020/1/17 上午9:34
     */
    public function getAll()
    {
        return Cache::rememberForever('config', function () {
            $config = self::query()->get(['group', 'key', 'value'])->toArray();
            $data = [];
            foreach ($config as $v) {
                $data[$v['group'] . "." . $v['key']] = $v['value'];
            }
            return $data;
        });
    }


    /**
     * 刷新缓存
     * @return bool
     * @author Aii
     * @date 2020/1/17 上午10:09
     */
    public function refresh()
    {
        Cache::forget('config');

        return self::getAll();
    }

    /**
     * 更新或者添加
     * @param array $params 配置值
     * @param int $id 配置ID
     * @return mixed
     * @author Aii
     * @date 2020/1/17 上午10:50
     */
    public  function updateOrCreate($params, $id = null)
    {
        if (is_null($id)) {
            $result = self::query()->create($params);
        } else {
            $result = self::query()->where('id', $id)->update($params);
        }

        self::refresh();

        return $result;
    }

}
