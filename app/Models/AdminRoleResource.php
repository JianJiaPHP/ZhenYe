<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * 角色资源
 * Class AdminRoleResource
 * @package App\Models
 */
class AdminRoleResource extends Model
{
    protected $table = "admin_role_resource";

    public $timestamps = false;

    protected $fillable = ['role_id', 'resource_id', 'created_at'];
}
