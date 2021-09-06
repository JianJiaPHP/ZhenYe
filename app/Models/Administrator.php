<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Administrator extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "administrators";
    protected $fillable = ['id', 'account', 'nickname', 'avatar', 'password', 'deleted_at', 'created_at', 'updated_at'];

    protected $hidden = ['password'];

    private static $KEY = "administrator";


    /**
     * 根据ID查询信息
     * @param $id
     * @return array
     * author hy
     */
    public static function getAdministratorById($id)
    {
        $key = self::$KEY . ":" . $id;
        $data = Redis::hGetAll($key);
        if (!$data) { //缓存中不存在
            $administrator = self::query()->where('id', $id)->first()->toArray();
            Redis::hmset($key, $administrator);
            // 设置过期
            Redis::EXPIRE($key, auth('admin')->factory()->getTTL() * 60);
            return $administrator;
        }
        return $data;
    }

    /**
     * 根据ID删除缓存
     * @param $id
     * @return mixed
     * author hy
     */
    public static function delAdministratorById($id)
    {
        $key = self::$KEY . ":" . $id;
        return Redis::del($key);
    }

    /**
     * 删除所有
     * @return mixed
     * author hy
     */
    public static function delAdministratorAll()
    {
        $pattern = self::$KEY . "*";
        $keys = Redis::keys($pattern);
        return Redis::del($keys);
    }

    /**
     * 关联角色
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * author hy
     */
    public function roles()
    {
        return $this->hasMany(AdminRoleAdministrator::class, "administrator_id", 'id');
    }

    /**
     * 头像拼接连接
     * @param $value
     * @return mixed
     * @author Aii
     */
    public function getAvatarAttribute($value)
    {
        $url = $value;
        $preg = "/^http(s)?:\\/\\/.+/";
        if (!preg_match($preg, $value)) {
            $url = env('APP_URL') . $value;
        }
        return $url;
    }
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


}
