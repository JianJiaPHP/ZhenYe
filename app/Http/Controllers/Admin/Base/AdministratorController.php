<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\AdminMenu;
use App\Models\AdminResource;
use App\Models\AdminRoleAdministrator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorController extends Controller
{
    /**
     * 管理员列表
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function index()
    {
        $params = request()->all();

        $data = Administrator::with('roles')->orderBy('created_at', 'desc')
            ->where('id', '>', 1);

        $keyword = $params['account'];
        $data->when($keyword, function ($query, $keyword) {
            return $query->where('account', 'like', "%$keyword%");
        });

        $result = $data->select('id', 'account', 'nickname', 'avatar', 'created_at')
            ->paginate(request()->get('limit', 15));

        return success($result);
    }

    /**
     * 添加管理员
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function store()
    {
        $params = request()->all();
        $exist = Administrator::where('account', $params['account'])->first();
        if ($exist) {
            return fail('该账号已存在');
        }
        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            $md5Password = md5($params['password']);
            $result = Administrator::query()->create([
                'account' => $params['account'],
                'nickname' => $params['nickname'],
                'avatar' => 'http://placeimg.com/300/200',
                'password' => Hash::make($md5Password),
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            // 添加角色管理员
            if (!empty($params['roleIds'])) {
                $rolesData = [];
                $roles = explode(',', $params['roleIds']);
                foreach ($roles as $v) {
                    $rolesData[] = [
                        'role_id' => $v,
                        'administrator_id' => $result->id,
                        'created_at' => $now
                    ];
                }
                $adminRoleAdministrator = AdminRoleAdministrator::query()->insert($rolesData);
                if (!$adminRoleAdministrator) {
                    throw new \Exception('系统超时');
                }
            }

            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }

    }

    /**
     * 管理更新
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function update($id)
    {
        $params = request()->all();
        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            $md5Password = md5($params['password']);
            $result = Administrator::query()->where('id', $id)->update([
                'account' => $params['account'],
                'nickname' => $params['nickname'],
                'avatar' => 'http://placeimg.com/300/200',
                'password' => Hash::make($md5Password),
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }

            if (AdminRoleAdministrator::query()
                ->where('administrator_id', $id)
                ->exists()) {
                $adminRoleAdministrator = AdminRoleAdministrator::query()
                    ->where('administrator_id', $id)
                    ->delete();
                if (!$adminRoleAdministrator) {
                    throw new \Exception('系统超时');
                }
            }


            // 添加角色管理员
            if (!empty($params['roleIds'])) {
                $rolesData = [];
                $roles = explode(',', $params['roleIds']);
                foreach ($roles as $v) {
                    $rolesData[] = [
                        'role_id' => $v,
                        'administrator_id' => $id,
                        'created_at' => $now
                    ];
                }
                $adminRoleAdministrator = AdminRoleAdministrator::query()->insert($rolesData);
                if (!$adminRoleAdministrator) {
                    throw new \Exception('系统超时');
                }
            }
            // 删除菜单缓存
            AdminMenu::delAdminByAdministratorId($id);
            // 删除资源缓存
            AdminResource::delAdminResourceByAdministratorId($id);
            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 管理员删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @author Aii
     */
    public function destroy($id)
    {
        $id = explode(',', $id);

        DB::beginTransaction();
        try {
            $result = Administrator::destroy($id);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            $adminRoleAdministrator = AdminRoleAdministrator::query()
                ->where('administrator_id', $id)
                ->delete();
            if (!$adminRoleAdministrator) {
                throw new \Exception('系统超时');
            }
            // 删除菜单缓存
            AdminMenu::delAdminByAdministratorId($id);
            // 删除资源缓存
            AdminResource::delAdminResourceByAdministratorId($id);

            DB::commit();
            return success();

        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }

    }


}
