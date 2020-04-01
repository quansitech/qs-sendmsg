<?php


namespace QsSendMsg\Job;


use Qscmf\Lib\Tp3Resque\Resque;
use QsSendMsg\Parser\JobDataParser;
use QsSendMsg\SendMsgJobBuilder;
use Think\Log;

class BaseJob
{
    public $args;
}