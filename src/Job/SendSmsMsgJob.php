<?php
namespace QsSendMsg\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Think\Exception;

class SendSmsMsgJob extends BaseJob {
    
    public function perform(){
        $args = $this->args;
        //todo 重写发送短信方式
        try {
            $para['content'] = $args['content'];
            if (trim($para['content'])) {
                $para['mobile'] = $args['mobile'];
                \Think\Hook::listen('sendSmsOnce', $para);

                $return = $para['return'];

                if ($return['status'] == 1) {
                    D('SmsLog')->add([
                        'mobile'=>$para['mobile'],
                        'content'=>$para['content']
                    ]);
                } else {
                    E($return['err_msg']);

                }

                echo 'SendSmsJob finish!';
            }
        }catch (Exception $e){
            E($e->getMessage());
        }

    }
}
