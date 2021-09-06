<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\AdminResource;
use App\Models\AdminRoleResource;
use App\Models\CoinInfo;
use App\Models\Exchange;
use App\Models\Interchange;
use App\Models\PriceBalanceInfo;
use App\Models\PriceFilInfo;
use App\Models\PriceGiftIntegralInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceLockFilInfo;
use App\Models\PriceRepurchasingIntegralInfo;
use App\Models\PriceUsdtInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InfoController extends Controller
{


    public $model;
    public function __construct()
    {
        $pramsmodel = request()->query('model');
        switch ($pramsmodel){
            case 'price_usdt_info': $this->model = new PriceUsdtInfo();break;//USDT资金记录
            case 'price_lock_fil_info': $this->model = new PriceLockFilInfo();break;//冻结fil资金记录
            case 'price_repurchasing_integral_info': $this->model = new PriceRepurchasingIntegralInfo();break;//复购资金记录
            case 'price_hyc_info': $this->model = new PriceHycInfo();break;//HYC资金记录
            case 'price_gift_integral_info': $this->model = new PriceGiftIntegralInfo();break;//赠送积分资金记录
            case 'price_fil_info': $this->model = new PriceFilInfo();break;//FIL资金记录
            case 'price_balance_info': $this->model = new PriceBalanceInfo();break;//余额资金记录
            case 'coin_info': $this->model = new CoinInfo();break;//HYC币价明细
            case 'interchange': $this->model = new Interchange();break;
            case 'exchange': $this->model = new Exchange();break;
            default : $this->model = new PriceUsdtInfo();break;
        }
    }

    /**
     * 列表
     * @return \Illuminate\Http\JsonResponse
     * author hy
     */
    public function index()
    {
        $phone = request()->query('phone');
        $sm = request()->query('model');
      if($sm == 'interchange'){
            $phone_user = request()->query('out_username');
            $where[] = ['out_username','like',"%$phone_user%"];
            $limit = request()->query('limit', 10);
            $data =  $this->model->where($where)->paginate($limit);
            return success($data);
        }elseif ($sm == 'exchange'){
            $phone_user = request()->query('username');
            $where[] = ['username','like',"%$phone_user%"];
            $limit = request()->query('limit', 10);
            $data =  $this->model->where($where)->paginate($limit);
            return success($data);
        }else{
          $data = $this->model->query()->with(['user_list'])->orderBy('id', 'desc');
          $where[] = ['phone','like',"%$phone%"];
          $data = $data->when($where,function ($query) use ($where){
              $query->whereHas('user_list',function ($query) use ($where){
                  $query->where($where);
              }) ;
          });
        }
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
