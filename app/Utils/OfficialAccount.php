<?php


namespace App\Utils;

// 公众号
use App\Helpers\HttpHelper;
use App\Models\User;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * 公众号相关
 * Class OfficialAccount
 * @package App\Utils
 */
class OfficialAccount
{
    use HttpHelper;

    private $app;

    public function __construct()
    {
        $this->app = Factory::officialAccount(config('wechat.official_account.default'));
    }

    /**
     * 消息接收
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     * author II
     */
    public function token()
    {
        $this->app->server->push(function ($message) {
            $openId = $message['FromUserName'];
            $userInfo = $this->getUserInfo($openId);
            if ($message['Event'] == 'subscribe' && $message['MsgType'] == 'event') {
                return "您好！欢迎关注简历办公!\n 绑定账号请输入:BD#手机号 \n 如:BD#123xxxx1234";
            }

        });


        return $this->app->server->serve();

    }


    /**
     * 根据openid获取用户信息
     * @param $openId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * author II
     */
    public function getUserInfo($openId)
    {
        return $this->app->user->get($openId);
    }


    public function templateMessageSend($openid)
    {

//        dump($this->app->user->get("oXTs_0V6A2HYf4pSiAgjZv3bNQCY"));
//        $s = $users = $this->app->user->list();


//        dd($s);
//        dd($this->app->user->select($s['data']['openid']));

        $result = $this->app->template_message->sendSubscription([
            'touser' => $openid,
            'template_id' => 'cEs9TfB1rX8t28nZ784ZpyMfC_6UpGcRz6IsQS0Rphg',
            'url' => 'https://easywechat.org',
            'scene' => 'SCENE',
            'title' => '一次性订阅',
            "data" => [
                "content" => [
                    "value" => "VALUE",
                    "color" => "COLOR",
                ]
            ]
//            'data' => [
//                'first' => 'VALUE',
//                'keyword1' => 'VALUE2',
//                'keyword2' => 'VALUE2',
//                'keyword3' => 'VALUE2',
//                'keyword4' => 'VALUE2',
//                'remark' => 'VALUE2',
//            ],
        ]);

        dd($result);
    }


}
