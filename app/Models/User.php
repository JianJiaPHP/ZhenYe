<?php


namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $table = "user";
    protected $fillable = ['id', 'name', 'phone', 'parent_id', 'user_code', 'pay_password', 'password', 'filcoin', 'filcoin_lock', 'cny',
        'cny_lock', 'integral', 'usdt', 'hyc', 'hyc_lock', 'is_static', 'is_member_static', 'miner_number', 'is_static_number', 'address', 'address_name', 'created_at', 'updated_at', 'deleted_at'];

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

    /**
     * 关联省
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * author II
     */
    public function province()
    {
        return $this->hasOne(Provinces::class, 'code', 'province_code');
    }

    /**
     * 关联市
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * author II
     */
    public function city()
    {
        return $this->hasOne(City::class, 'code', 'city_code');
    }

    /**
     * 关联区
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * author II
     */
    public function district()
    {
        return $this->hasOne(District::class, 'code', 'district_code');
    }
}
