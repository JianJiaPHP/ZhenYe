<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\CoinInfo;
use App\Models\PriceBalanceInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceRepurchasingIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\UsdtWithdraw;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsdtWithdrawController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $data =  UsdtWithdraw::query()->orderBy('id', 'desc');
        $limit = request()->query('limit', 10);
        $data = $data->paginate($limit);
        return success($data);
    }

    /**
     * 添加
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function store()
    {
        $params = request()->all();

//        $result = User::query()->create([
//            'name' => $params['name'],
//            'http_method' => $params['http_method'],
//            'url' => $params['url'],
//        ]);
        return choose($params);
    }

    /**
     * 更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        $params = request()->all();
//        $result = User::query()->where('id', $id)->update([
//            'name' => $params['name'],
//            'pay_password' => Hash::make($params['pay_password']),
//            'password' => Hash::make($params['password']),
//        ]);
        return choose($params);
    }

    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
//        DB::beginTransaction();
//        try {
//            $result = User::query()->where('id', $id)->delete();
//            if (!$result) {
//                throw new \Exception('系统超时');
//            }
//            DB::commit();
//            return success();
//        } catch (\Exception $exception) {
//            DB::rollback();
//            return fail($exception->getMessage());
//        }
    }


}
