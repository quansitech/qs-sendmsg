# qs-sendmsg
统一发送消息队列

## 用法
### 1.安装依赖及执行迁移
```shell script
composer require quansitech/send-msg
php artisan migrate
```

### 2.在项目中新建一个消息内容转换器，需继承[BaseMsgTemplateParse]，然后加上要转换的所有方法 
```php
class MsgTemplateParse extends BaseMsgTemplateParse {
    
    static protected $_var_func = array(
        '项目名称' => 'parseProjectName',
    );
    
    static public function parseProjectName($args){
        $project_id = $args['project_id'];
        return D('Project')->where(['id'=>$project_id])->getField('title');
    }
}
```

### 3.构造实例
```php
//MsgTemplateParse为上一步所定义的类
$builder=\QsSendMsg\SendMsgJobBuilder::getInstance()->setTemplateParser(MsgTemplateParse::class);
```

### 4.添加发送的消息内容及对象（可加多条）
```php
/** @var $builder \QsSendMsg\SendMsgJobBuilder */
$builder->setMsgTpl('test_msg',['project_id'=>1])
    ->addSmsMsgJob('13100000000','发送短信')
    ->addWxMsgJob('[openid]','[消息模板id]','[消息内容对象]','[跳转的url]','发送微信消息','[内容填充字段（默认为"first"）]');
```
ps."test_msg"为数据库中'qs_msg_tpl'的数据 <br>
ps."消息内容对象"示例
```php
$msgData = [
    'first' => ['value' => 'test', 'color' => '#173177'],
    'keyword1' => ['value' => 'keyword1', 'color' => '#173177'],
    'keyword2' => ['value' => 'keyword2', 'color' => '#173177'],
    'remark' => ['value' => 'remark', 'color' => '#173177'],
];
```

### 5.分派消息队列
#### 5.1全部发送
```php
/** @var $builder \QsSendMsg\SendMsgJobBuilder 第4步的$builder*/
$builder->dispatchAll();
```

#### 5.2智能发送，（顺序发送，当一个发送成功时就不会发送后面的消息）
```php
/** @var $builder \QsSendMsg\SendMsgJobBuilder 第4步的$builder*/
$builder->dispatch();
```