<?php


namespace QsSendMsg;


use Illuminate\Support\Str;
use Qscmf\Lib\DBCont;
use Qscmf\Lib\Tp3Resque\Resque;
use QsSendMsg\Job\SendSmsMsgJob;
use QsSendMsg\Parser\JobDataParser;

class SendMsgJobBuilder
{
    private static $_jobBuilder;

    private $_queue=[
        'wx'=>'wx',
        'sms'=>'sms'
    ];

    private $_config=[];

    private $_msgTplName;
    private $_msgTplArgs;
    private $_jobDataList=[];
    private $_templateParser='QsSendMsg\\Parser\\BaseMsgTemplateParse';

    public function addQueue($param, $job, $description, $queue = 'default')
    {
        $job_id = Resque::enqueue($queue, $job, $param, true);

        $data['id'] = $job_id;
        $data['job'] = $job;
        $data['args'] = Str::limit(json_encode($param),1900);
        $data['description'] = $description;
        $data['status'] = DBCont::JOB_STATUS_WAITING;
        $data['create_date'] = time();
        $data['queue'] = $queue;

        D('Queue')->add($data);
    }

    private function _getContent(){
        if (!$this->_msgTplName){
            E('无模板名称');
        }
        $sms_content=D('MsgTpl')->where(['name'=>$this->_msgTplName])->getField('sms_content');
        $wx_content=D('MsgTpl')->where(['name'=>$this->_msgTplName])->getField('wx_content');
        $sms_content = call_user_func($this->_templateParser .'::parse', $this->_msgTplArgs,$sms_content);
        $wx_content = call_user_func($this->_templateParser .'::parse', $this->_msgTplArgs,$wx_content);
        return ['sms'=>$sms_content,'wx'=>$wx_content];
    }

    /**
     * @param $mobile
     * @param string $desc
     * @param array $data_options
     * @return $this
     */
    public function addSmsMsgJob($mobile,$desc='',$data_options=[]){
        $this->_jobDataList[]=[
            'type'=>'sms',
            'data'=>[
                'mobile'=>$mobile,
                'data_options'=>$data_options,
                'desc'=>$desc
            ]
        ];
        return $this;
    }

    public function addWxMsgJob($openid, $tplId, $msgData,$url, $desc='', $contentField='first'){
        $this->_jobDataList[]=[
            'type'=>'wx',
            'data'=>[
                'openid'=>$openid,
                'tplId'=>$tplId,
                'msgData'=>$msgData,
                'url'=>$url,
                'desc'=>$desc,
                'contentField'=>$contentField
            ]
        ];
        return $this;
    }

    //当一个发送成功时便不往下执行
    public function dispatch(){
        $content=$this->_getContent();
        $length=count($this->_jobDataList);
        for ($i=0;$i<$length;$i++) {
            $jobData = array_shift($this->_jobDataList);
            list($job, $args) = JobDataParser::parse($jobData, $content, $this->_jobDataList);
            if ($job){
                $this->addQueue($args, $job, $args['desc'], $this->_queue[$jobData['type']]);
                break;
            }
        }
        $this->_init();
    }

    //全部发送
    public function dispatchAll(){
        $content=$this->_getContent();
        foreach ($this->_jobDataList as $jobData) {
            list($job,$args)=JobDataParser::parse($jobData,$content,[]);
            if ($job) {
                $this->addQueue($args, $job, $args['desc'], $this->_queue[$jobData['type']]);
            }
        }
        $this->_init();
    }

    /**
     * @param array $queue
     * @return SendMsgJobBuilder
     */
    public function setQueue($queue)
    {
        $this->_queue = $queue;
        return $this;
    }

    private function _init(){
        $this->_jobDataList=[];
        $this->_msgTplArgs=[];
        $this->_msgTplName='';
    }

    public static function getInstance($config=[]){
        if (!self::$_jobBuilder){
            self::$_jobBuilder=new self();
        }
        if ($config){
            self::$_jobBuilder->setConfig($config);
        }
        return self::$_jobBuilder;
    }

    private function __construct()
    {
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @param mixed $msgTplName
     * @param $msgTplArgs
     * @return SendMsgJobBuilder
     */
    public function setMsgTpl($msgTplName,$msgTplArgs)
    {
        $this->_msgTplName = $msgTplName;
        $this->_msgTplArgs = $msgTplArgs;
        return $this;
    }

    public static function nextJob($next_job_list,$content){
        do {
            if (!$next_job_list){
                return;
            }
            $jobData = array_shift($next_job_list);
            list($job, $args) = JobDataParser::parse($jobData, $content, $next_job_list);
        }while(!$job);
        self::getInstance()->addQueue($args, $job, $args['desc'], $jobData['type']);
    }

    /**
     * @param string $templateParser
     * @return SendMsgJobBuilder
     */
    public function setTemplateParser($templateParser)
    {
        $this->_templateParser = $templateParser;
        return $this;
    }
}