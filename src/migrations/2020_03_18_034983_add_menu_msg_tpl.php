<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenuMsgTpl extends Migration
{
    private $_upMenuData=[
        '信息模板'=>[
            [
                'name'=>'index',
                'title'=>'模板管理',
                'controller'=>'MsgTpl'
            ],
        ]
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $menuGenerate=new \Qscmf\Utils\MigrationHelper\MenuGenerate();
        $menuGenerate->insertAll($this->_upMenuData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $menuGenerate=new \Qscmf\Utils\MigrationHelper\MenuGenerate();
        $menuGenerate->insertAllRollback($this->_upMenuData);
    }
}
