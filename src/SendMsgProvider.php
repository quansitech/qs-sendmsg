<?php


namespace QsSendMsg;


use Bootstrap\LaravelProvider;
use Bootstrap\Provider;
use Bootstrap\RegisterContainer;
use QsSendMsg\Controller\MsgTplController;
use Think\Hook;

class SendMsgProvider implements Provider,LaravelProvider
{
    public function register()
    {
        //注册Controller
        RegisterContainer::registerController('admin','MsgTpl',MsgTplController::class);
    }

    public function registerLara(){
        //注册迁移文件
        RegisterContainer::registerMigration([
            __DIR__.'/migrations/',
        ]);
    }
}