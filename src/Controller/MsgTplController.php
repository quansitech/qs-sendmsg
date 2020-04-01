<?php


namespace QsSendMsg\Controller;


use Gy_Library\GyListController;
use Qscmf\Builder\BaseBuilder;
use Qscmf\Builder\FormBuilder;
use Qscmf\Builder\ListBuilder;
use Qscmf\Lib\DBCont;
use QsSendMsg\Model\MsgTplModel;
use Think\View;

class MsgTplController extends GyListController{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model=new MsgTplModel();
    }

    public function index(){
        $data_list = $this->model->select();

        $builder = new ListBuilder();

        $builder = $builder->setMetaTitle('消息模板列表');

        $builder
            ->setNIDByNode()
            ->setCheckBox(false)
            ->addTableColumn('name', '模板名称', '', '', false)
            ->addTableColumn('title', '标题', '', '', false)
            ->addTableColumn('right_button', '操作', 'btn')
            ->setTableDataList($data_list)
            ->addRightButton('edit')
            ->display();
    }

    public function edit($id){
        if (IS_POST) {
            C('TOKEN_ON',false);
            $data = I('post.');
            $model = $this->model;

            $ent = $model->getOne($data['id']);
            if(!$ent){
                $this->error('不存在模板配置');
            }
            $ent=array_merge($ent,$data);

            if($model->createSave($ent) === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('修改模板配置id:' . $data['id']);
                $this->success('修改成功', U('index'));
            }
        } else {
            $tpl_ent = $this->model->getOne($id);
            $var_list = explode(',', $tpl_ent['options']);

            $module_ent = D('Node')->where(['name' => MODULE_NAME, 'level' => DBCont::LEVEL_MODULE, 'status' => DBCont::NORMAL_STATUS])->find();
            $controller_ent = D('Node')->where(['name' => CONTROLLER_NAME, 'level' => DBCont::LEVEL_CONTROLLER, 'status' => DBCont::NORMAL_STATUS, 'pid' => $module_ent['id']])->find();
            $action_ent = D('Node')->where(['name' => 'index', 'level' => DBCont::LEVEL_ACTION, 'status' => DBCont::NORMAL_STATUS, 'pid' => $controller_ent['id']])->find();

            $view=new View();
            $view->assign('var_list', $var_list);
            $view->assign('info', $tpl_ent);
            $view->assign('menu_list',$this->menu_list);
            $view->assign('nid',$action_ent['id']);
            $view->assign('meta_title','编辑消息模板');
            $view->display(__DIR__.'/../View/edit.html');
        }
    }

    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = $this->model;
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增消息模板id:' . $r);

                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            $builder = new FormBuilder();


            $builder->setMetaTitle('新增内容')
                ->setNIDByNode()
                ->setPostUrl(U('add'))
                ->addFormItem('title', 'text', '标题','', '')
                ->addFormItem('name', 'text', '模板名称','', '')
                ->addFormItem('sms_content', 'textarea', '短信通知内容','', '')
                ->addFormItem('wx_content', 'textarea', '微信通知内容','', '')
                ->addFormItem('options', 'text', '变量(,隔开)','', '')
                ->display();
        }
    }
}
