<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartIdRequests;
use App\Http\Requests\Api\CartSeleteRequests;
use App\Models\Goods;
use App\Models\Order;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;
use Ramsey\Uuid\Uuid;


class CartController extends Controller
{


    protected $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis = app("redis.connection");
    }

    /**
     * 购物车添加
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function add_cart(CartSeleteRequests $cartSeleteRequests)
    {
        $params = $cartSeleteRequests->all();
        $user_id = auth('api')->id();
        //cart：userid /  商品id => 商品数量
        $res = $this->redis->hincrBy('cart:' . $user_id . '', $params['goods_id'], $params['count']);
        if (!$res) {
            return fail("添加失败");
        }
        return success("添加成功");
    }


    /**
     * 用户购物车
     * @return \Illuminate\Http\JsonResponse
     */
    public function cart_list()
    {
        $user_id = auth('api')->id();
        $data = $this->redis->hGetAll('cart:' . $user_id . '');
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $res = Goods::query()->with(['type_name'])->where(['id' => $k])->first()->toArray();
                $res['count'] = $v;
                $list[] = $res;
            }
        }else{
            $list = $data;
        }
        return success($list);
    }


    /**
     * 修改商品数量
     */
    public function hincry_cart(CartSeleteRequests $cartSeleteRequests)
    {
        $params = $cartSeleteRequests->all();
        $user_id = auth('api')->id();
        $data = $this->redis->hget('cart:' . $user_id . '', $params['goods_id']);
        if ($params['count'] + $data == 0) {
            $res_del = $this->redis->hdel('cart:' . $user_id . '', $params['goods_id']);
            return choose($res_del);
        }
        $res = $this->redis->hincrBy('cart:' . $user_id . '', $params['goods_id'], $params['count']);
        return choose($res);
    }

    /**
     * 删除商品
     * @return \Illuminate\Http\JsonResponse
     */
    public function del_cart(CartIdRequests $cartIdRequests)
    {
        $params = $cartIdRequests->all();
        $user_id = auth('api')->id();
        $res = $this->redis->hdel('cart:' . $user_id . '', $params['goods_id']);
        return choose($res);
    }


}
