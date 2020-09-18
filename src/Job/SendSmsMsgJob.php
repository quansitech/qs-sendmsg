<?php
namespace QsSendMsg\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\Gateways\Gateway;
use QsSendMsg\Job\SmsGateway\QiruiGateway;
use Think\Exception;
use Think\Log;

class SendSmsMsgJob extends BaseJob {
    private $_easySms;

    public function __construct()
    {
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
            ],
        ];
        //可添加其它easysms自带的gateway
        if (C('SEND_MSG_SMS_GATEWAY',null,false)){
            foreach (C('SEND_MSG_SMS_GATEWAY') as $gateway=>$item) {
                $config['default']['gateways'][]=$gateway;
                $config['gateways'][$gateway]=$item;
            }
        }else{
            //默认使用启瑞云
            $config['default']['gateways']=['qirui'];
            $config['gateways']['qirui']=[
                'api_key' => env('QIRUI_API_KEY'),
                'api_secret' => env('QIRUI_API_SECRET'),
                'sign_text' => env('QIRUI_SIGN')
            ];

        }

        $this->_easySms=new EasySms($config);

        //注册启瑞云网关
        $this->_easySms->extend('qirui',function ($config){
            return new QiruiGateway($config);
        });

    }

    /**
     * 直接发送短信消息
     * @param $para array 消息参数
     * [
     *      'mobile'=>'手机号',
     *      'content'=>'短信内容',
     *      ... //其它easysms参数
     * ]
     * @return array|false
     */
    public function send($para){
        unset($para['next_job_list']);
        unset($para['msg_content']);
        unset($para['desc']);

        $mobile=$para['mobile'];
        $content=$para['content'];
        try {
            if (trim($content) && $mobile) {
                unset($para['mobile']);
                if (is_string($content)){
                    $para['content']=function (Gateway $gateway) use ($content){
                        return $gateway->getConfig()->get('sign_text').$content;
                    };
                }
                $res = $this->_easySms->send($mobile, $para);
                D('SmsLog')->add([
                    'mobile' => $mobile,
                    'content' => $content
                ]);
            }
            return $res;
        }catch (NoGatewayAvailableException $exception){
            $this->error=$exception->getLastException();
            if ($this->error){
                $this->error=$this->error->getMessage();
            }
            return false;
        }catch (\Exception $e){
            $this->error=$e->getMessage();
            return false;
        }
    }
}
