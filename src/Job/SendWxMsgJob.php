<?php
namespace QsSendMsg\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EasyWeChat\Factory;
use GuzzleHttp\Exception\GuzzleException;
use QsSendMsg\SendMsgJobBuilder;

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

    /**
     * @param $para array 消息参数
     * [
     *      'open_id'=>'微信openid',
     *      'template_id'=>'微信模板消息ID',
     *      'url'=>'跳转地址',
     *      'data'=>'消息内容'
     * ]
     * @return array|\EasyWeChat\Kernel\Support\Collection|false|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function send($para)
    {
        try {
            $user = $this->app->user->get($para['open_id']);
            if (!$user['subscribe']){
//                if ($para['next_job_list']){
//                    SendMsgJobBuilder::nextJob($para['next_job_list'],$para['msg_content']);
//                }
                $this->error='需要关注公众号';
                return false;
            }

            $res=$this->app->template_message->send([
                'touser' => $para['open_id'],
                'template_id' => $para['template_id'],
                'url' => $para['url'],
                'data' => $para['data'],
            ]);

            //{"errcode":0,"errmsg":"ok","msgid":1253912252621750272}
            if ($res['errmsg']=='ok'){
                $data['id'] = $res['msgid'];
                $data['openid'] = $para['open_id'];
                $data['description'] = $para['desc'];
                $data['create_date'] = time();

                D('WxMsg')->add($data);
                return $res;
            }else{
                E($res['errmsg']);
            }

        } catch (\Exception $e) {
            $this->error=$e->getMessage();
            return false;
        } catch (GuzzleException $e) {
            $this->error=$e->getMessage();
            return false;
        }
    }
}
