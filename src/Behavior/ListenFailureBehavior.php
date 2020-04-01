<?php


namespace QsSendMsg\Behavior;


use Qscmf\Lib\Tp3Resque\Resque\Event;
use QsSendMsg\SendMsgJobBuilder;
use Think\Behavior;

class ListenFailureBehavior extends Behavior
{

    /**
     * @inheritDoc
     */
    public function run(&$params)
    {
        Event::listen('onFailure', function ($args){
            $args=$args['job']->payload['args'];
            foreach ($args as $arg) {
                if ($arg['next_job_list']){
                    SendMsgJobBuilder::nextJob($arg['next_job_list']);
                }
            }
        });
    }
}