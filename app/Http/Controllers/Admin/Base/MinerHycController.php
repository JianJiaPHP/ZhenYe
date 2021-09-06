<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\CoinInfo;
use App\Models\FilGoods;
use App\Models\HycGoods;
use App\Models\PriceBalanceInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceRepurchasingIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\UsdtWithdraw;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MinerHycController extends Controller
{

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $where = [];
        $title = request()->query('title');
        if ($title){
            $where[] = ['title','like',"%$title%"];
        }
        $data =  HycGoods::query()->where($where)->orderBy('id', 'desc');
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
        DB::beginTransaction();
        try {
            $params = request()->all();
            $result = HycGoods::query()->create([
                'title' => $params['title'],
                'img' => $params['img'],
                'price' => $params['price'],
                'total_price' => $params['total_price'],
                'day' => $params['day'],
                'limit_count' => $params['limit_count'],
                'state' => $params['state'],
                'number' => $params['number'],
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return choose($result);
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 更新
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function update($id)
    {
        DB::beginTransaction();
        try {
            $params = request()->all();
            $result = HycGoods::query()->where('id',$id)->update([
                'title' => $params['title'],
                'img' => $params['img'],
                'price' => $params['price'],
                'total_price' => $params['total_price'],
                'day' => $params['day'],
                'limit_count' => $params['limit_count'],
                'state' => $params['state'],
                'number' => $params['number'],
            ]);
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return choose($result);
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }

    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = HycGoods::query()->where('id', $id)->delete();
            if (!$result) {
                throw new \Exception('系统超时');
            }
            DB::commit();
            return success();
        } catch (\Exception $exception) {
            DB::rollback();
            return fail($exception->getMessage());
        }
    }


}
