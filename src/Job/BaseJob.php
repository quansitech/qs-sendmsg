<?php


namespace QsSendMsg\Job;


use Qscmf\Lib\Tp3Resque\Resque;
use QsSendMsg\Parser\JobDataParser;
use QsSendMsg\SendMsgJobBuilder;
use Think\Log;

abstract class BaseJob
{
    public $error='';

    public $args;

    public function perform(){
        $res=$this->send($this->args);
        if ($res===false){
            if ($this->args['next_job_list']){
                SendMsgJobBuilder::nextJob($this->args['next_job_list'],$this->args['msg_content']);
            }
            throw new \Exception($this->error);
        }
        $class=get_called_class();
        echo "Job ${class} finish!\n";
    }

    abstract public function send($para);
}