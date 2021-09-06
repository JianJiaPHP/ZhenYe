<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\CoinInfo;
use App\Models\FilGoods;
use App\Models\Goods;
use App\Models\GoodsType;
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

class GoodsController extends Controller
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
        $type = request()->query('type');
        if ($title){
            $where[] = ['title','like',"%$title%"];
        }
        if ($type){
            $where[] = ['type', $type];
        }
        $data =  Goods::query()->with(['type_name'])->where($where)->orderBy('sort', 'desc');
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
            $result = Goods::query()->create([
                'title' => $params['title'],
                'stock' => $params['stock'],
                'original_price' => $params['original_price'],
                'price' => $params['price'],
                'img' => $params['img'],
                'text' => $params['text'],
                'postage' => $params['postage'],
                'type' => $params['type'],
                'hot' => $params['hot'],
                'put' => $params['put'],
                'sort' => $params['sort'],
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
            $result = Goods::query()->where('id',$id)->update([
                'title' => $params['title'],
                'stock' => $params['stock'],
                'original_price' => $params['original_price'],
                'price' => $params['price'],
                'img' => $params['img'],
                'text' => $params['text'],
                'postage' => $params['postage'],
                'type' => $params['type'],
                'hot' => $params['hot'],
                'put' => $params['put'],
                'sort' => $params['sort'],
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
            $result = Goods::query()->where('id', $id)->delete();
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
