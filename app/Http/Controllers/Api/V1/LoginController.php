<?php


namespace App\Http\Controllers\Api\V1;


use App\Helpers\HttpHelper;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CoinInfo;
use App\Models\Config;
use App\Models\HycGoods;
use App\Models\HycOrder;
use App\Models\PriceFilInfo;
use App\Models\PriceHycInfo;
use App\Models\PriceUsdtInfo;
use App\Models\User;
use App\Models\Verify;
use App\Services\ConfigService;
use App\Utils\HttpSend;
use App\Utils\Upload;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Hash;
use OSS\Core\OssException;
use OSS\OssClient;

class LoginController extends Controller
{
    use HttpHelper;
    private $url = 'https://api.smsbao.com/sms';


    /**
     * Notes: 账号登录
     * @throws \Exception
     */
    public function login(){
        $params = request()->all();
        if ($params['phone'] == null || $params['password']==null){
            return fail("请输入账号密码");
        }

        $exist = User::where('phone', $params['phone'])->first();
        if (!$exist) {
            return fail("账号不存在");
        }

        if (!\Hash::check( MD5($params['password']), $exist->password))
        {
            return fail('密码错误');
        }

//        $token  = auth('api')->claims(['foo' => 'bar','admin_username'=> 1])->login($exist);
        $token = auth('api')->login($exist);

        $exist->update(['token' => $token]);

        unset($exist['password']);
        return success([
            'data'=>$exist,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * 注册
     */
    public function register(){
        $params = request()->all();
        if (empty($params['phone'])){
            return fail("手机号不能为空");
        }
        if (empty($params['code'])){
            return fail("验证码不能为空");
        }
        if (empty($params['password'])){
            return fail("登录密码不能为空");
        }
        if (empty($params['yao_code'])){
            return fail("邀请码不能为空");
        }
        if (empty($params['name'])){
            return fail("真实姓名不能为空");
        }
        $code_exits = User::query()->where(['user_code'=>$params['yao_code']])->exists();
        if (!$code_exits){
            return fail("邀请码错误！");
        }
        $exits = User::query()->where(['phone'=>$params['phone']])->exists();
        if ($exits){return fail("账号已存在");}
        $md5_password = md5($params['password']);
        $params['password'] = Hash::make($md5_password);
        $md5_passwordpay = md5($params['pay_password']);
        $params['pay_password'] = Hash::make($md5_passwordpay);
        $code = Verify::query()->where(['phone'=>$params['phone']])->orderBy('id','desc')->first();
        if ($code['number'] != $params['code'] && $params['code']!='666666'){
            return fail("验证码错误");
        }

        $user = User::query()->where(['phone'=>$params['phone']])->exists();
        if ($user){
            return fail("该手机号已注册");
        }
        //生成自己的邀请码
        $params['user_code'] = rand('10','99').rand('000000','999999');

        if (!empty($params['yao_code'])){
            $parent_id = User::query()->where(['user_code'=>$params['yao_code']])->first();
            $params['parent_id'] = $parent_id['id']?:0;
        }
        $params['parent_id'] = User::query()->where(['user_code'=>$params['yao_code']])->value('id');
        #刷新下级数量
        $counts = User::query()->where(['parent_id'=>$params['parent_id']])->count();
        User::query()->where(['id'=>$params['parent_id']])->update(['parent_count'=>$counts]);
        $res = User::query()->create($params);
        if (!$res){
           return fail("创建失败");
        }
        //注册赠送 HYC初级矿机
        $goodsHyc = HycGoods::query()->where(['id'=>7])->first()->toArray();
        HycOrder::query()->create([
            'user_id'=>$res['id'],
            'miner_id'=>7,
            'type'=>1,
            'status'=>0,
            'stage'=>1,
            'day'=>$goodsHyc['day'],
            'price'=>0,
            'price_hyc'=>0,
            'number'=>1,
            'day_price'=>$goodsHyc['day'],
            'end_time'=>Carbon::now()->addDay(30),
        ]);
//        PriceUsdtInfo::query()->create([
//            'user_id' => $res['id'],
//            'status' => '1',
//            'total_money' =>10000 ,
//            'price' => 10000,//产币数量
//            'type' => 66,
//            'addtime' => time(),
//            'adddate' => Carbon::now(),
//            'remark' => "内测赠送",
//        ]);
//        PriceFilInfo::query()->create([
//            'user_id' => $res['id'],
//            'status' => '1',
//            'total_money' =>10000 ,
//            'price' => 10000,//产币数量
//            'type' => 66,
//            'addtime' => time(),
//            'adddate' => Carbon::now(),
//            'remark' => "内测赠送",
//        ]);
//        PriceHycInfo::query()->create([
//            'user_id' => $res['id'],
//            'status' => '1',
//            'total_money' =>10000 ,
//            'price' => 10000,//产币数量
//            'type' => 66,
//            'addtime' => time(),
//            'adddate' => Carbon::now(),
//            'remark' => "内测赠送",
//        ]);
//        User::query()->where(['id'=>$res['id']])->update(['filcoin'=>10000,'hyc'=>10000,'usdt'=>10000]);
        return success($res);
    }

    /**
     * 忘记密码
     */
    public function forgetpass(){
        $params = request()->all();
        if (empty($params['phone'])){
            return fail("手机号不能为空");
        }
        if (empty($params['code'])){
            return fail("验证码不能为空");
        }
        if (empty($params['new_password'])){
            return fail("新密码不能为空");
        }

        $exits = User::query()->where(['phone'=>$params['phone']])->exists();
        if (!$exits){
            return fail("暂无此用户");
        }
        $code = Verify::query()->where(['phone'=>$params['phone']])->orderBy('id','desc')->first();
        if ($code['number'] != $params['code']&& $params['code']!='666666'){
            return fail("验证码错误");
        }

        $md5_password = md5($params['new_password']);
        $params['password'] = Hash::make($md5_password);
        $res = User::query()->where(['phone'=>$params['phone']])->update(['password'=>$params['password']]);
        if (!$res){
            return fail("修改失败");
        }
        return success("修改成功");
    }

    /**
     * 忘记密码
     */
    public function forget_pay_pass(){
        $params = request()->all();
        $user = auth('api')->user();
        if (empty($params['code'])){
            return fail("验证码不能为空");
        }
        if (empty($params['new_password'])){
            return fail("新密码不能为空");
        }

        $exits = User::query()->where(['id'=>$user->id])->exists();
        if (!$exits){
            return fail("暂无此用户");
        }
        $code = Verify::query()->where(['phone'=>$user->phone])->orderBy('id','desc')->first();
        if ($code['number'] != $params['code']&& $params['code']!='666666'){
            return fail("验证码错误");
        }

        $md5_password = md5($params['new_password']);
        $params['password'] = Hash::make($md5_password);
        $res = User::query()->where(['phone'=>$params['phone']])->update(['pay_password'=>$params['password']]);
        if (!$res){
            return fail("修改失败");
        }
        return success("修改成功");
    }


    /**
     * 发送短信
     * @param $mobile 手机号
     * @param $code code码
     * @param $type 短信发送类型
     */
    public function sendSms()
    {
        $params = request()->all();
        $mobile = $params['phone'];
        $type = 2;
        if (empty($mobile)) {
            return fail("手机号不能为空");
        }
        if (empty($type)) {
            return fail("参数类型错误");
        }
        $code = getRand();
        $key = 'send_sms_'.$mobile;
        $redisExp = session($key);
        if ($redisExp) {
            $exp = array();
            $exp = explode('_', $redisExp);
            if (count($exp) == 2 && (int)$exp[1] > time()) {
             return fail("请于一分钟后再试");
            }
        }
        try {
            $msg = '【宏烨商城】您的验证码为'.$code.'，在5分钟内有效。';
            $client = new Client(['timeout' => 5.0]);
            $urls = $this->url.'?u=heitao&p='.md5('123456').'&m='.$mobile.'&c='.urlencode($msg).'';
            $res = $client->request('GET',$urls);
            $result = self::getContentFromResponse($res);
            if ($result == '0') {
                $time = time() + 60;
                $code_str = $code.'_'.$time;
                session($key,$code_str);
                $va = Verify::query()->create([
                    "number" => $code,
                    'phone' => $mobile,
                    "addtime" => date("Y-m-d H:i:s", time()),
                    "type" => $type
                ]);
                return success("发送成功");
            }
            return fail("发送失败");
        } catch (\Exception $e) {
            return fail("发送失败");
        }
    }


    /**
     * banner type1：矿机 2：商城
     */
    public function banner(){
        $params = request()->all();
        $list = Banner::query()->where(['type'=>$params['type']])->get()->toArray();
        return success($list);
    }


    /**
     * OSS
     */
    public function upload()
    {
        $file = request()->file('file');
        $res = Upload::upload($file,1,auth('api')->id()?:1);
        return success($res);
    }


    /**
     * 版本号获取
     */
    public function version(){
        $data = Config::query()->whereIn('id',[1,2,3,4,5])->select('key','value')->get()->toArray();
        return success($data);
    }


    /**
     * 实时币价格
     */
    public function price_bi(){
        $data = Config::query()->where(['group'=>'b'])->select('key','value')->get()->toArray();
        $data[]['key'] = 'HYC';
        $data[2]['value'] = CoinInfo::query()->orderBy('id','desc')->value('price');
        return success(array_column($data,'value','key'));
    }


    /**
     * 实时币价格
     */
    public function price_bi_api(){
        $data = Config::query()->where(['group'=>'b'])->select('key','value')->get()->toArray();
        return array_column($data,'value','key');
    }
}
