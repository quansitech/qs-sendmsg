<?php
namespace QsSendMsg\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
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
                    'qirui',//http://www.qirui.com/
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'qirui' => [
                    'api_key' => env('QIRUI_API_KEY'),
                    'api_secret' => env('QIRUI_API_SECRET'),
                    'sign_text' => env('QIRUI_SIGN')
                ],
                //...
            ],
        ];
        $this->_easySms=new EasySms($config);

        //注册启瑞云网关
        $this->_easySms->extend('qirui',function ($config){
            return new QiruiGateway($config);
        });

    }

    public function send($para){
        $mobile=$para['mobile'];
        $content=$para['content'];
        try {
            if (trim($content) && $mobile) {
                $res=$this->_easySms->send($mobile, [
                    'content' => $content,
                ]);

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
