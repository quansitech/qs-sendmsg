<?php
namespace QsSendMsg\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EasyWeChat\Factory;
use GuzzleHttp\Exception\GuzzleException;

class SendWxMsgJob extends BaseJob {

    public function __construct()
    {
        $config = [
            'app_id' => env('WX_APPID'),
            'secret' => env('WX_APPSECRET'),

            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            //...
        ];

        $this->app = Factory::officialAccount($config);
    }

    public function perform(){
        $args = $this->args;

        try {

            $user = $this->app->user->get($args['open_id']);
            if (!$user['subscribe']){
                E('no subscribe');
            }

            $res=$this->app->template_message->send([
                'touser' => $args['open_id'],
                'template_id' => $args['template_id'],
                'url' => $args['url'],
                'data' => $args['data'],
            ]);

            //{"errcode":0,"errmsg":"ok","msgid":1253912252621750272}

            if ($res['errmsg']=='ok'){
                $data['id'] = $res['msgid'];
                $data['openid'] = $args['open_id'];
                $data['description'] = $args['desc'];
                $data['create_date'] = time();

                D('WxMsg')->add($data);
                echo 'SendWeixinMsgJob finish! weixin msg_id=' . $res['msgid'] . '\n';
            }else{
                E($res['errmsg']);
            }

        } catch (\Exception $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw new \Exception($e);
        }

    }
}
