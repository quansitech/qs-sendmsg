<?php


namespace QsSendMsg\Job\SmsGateway;


use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

class QiruiGateway extends Gateway
{
    use HasHttpRequest;

    const SENDURL = 'http://api.qirui.com:7891/mt';

    const ERR_MSG = [
        9002=>'未知命令',
        9012=>'短信消息内容错误',
        9013=>'目标地址错误',
        9014=>'短信内容太长',
        9015=>'路由错误',
        9016=>'没有下发网关',
        9017=>'定时时间错误',
        9018=>'有效时间错误',
        9019=>'无法拆分或者拆分错误',
        9020=>'号码段错误',
        9021=>'消息编号错误，这个和 PacketIndex 参数有关',
        9022=>'用户不能发长短信(EsmClass 错误)',
        9023=>'ProtocolID 错误',
        9024=>'结构错误，一般是指长短信',
        9025=>'短信编码错误',
        9026=>'内容不是长短信',
        9027=>'签名不对',
        9028=>'目标网关不支持长短信',
        9029=>'路由拦截',
        9030=>'目标地址(手机号)太多',
        9031=>'目标地址(手机号)太少',
        9032=>'发送速度太快',
        9101=>'验证失败，一般和用户名/密码/IP 地址相关',
        9102=>'没有填写用户名',
        9103=>'名字没找到',
        9104=>'IP 地址不对',
        9105=>'超过最大连接数，就是 tcp 连接数，http 也是一样的',
        9106=>'协议版本错误',
        9107=>'帐号无效，比如过期/禁用',
        9401=>'计费错误',
        9402=>'非法内容',
        9403=>'黑名单',
        9404=>'丢弃',
        9405=>'Api 帐号丢失',
        9406=>'配置拒绝，就是帐号设置了拒绝标记',
        9407=>'帐号没有生成时间,这个属于非法帐号',
        9408=>'消息超时，超过短信或帐号或系统设置的生存时间',
        9409=>'由约束规则拒绝',
        9410=>'状态报告超时',
        9411=>'状态报告',
        9412=>'帐号无效',
        9413=>'重发拦截',
        9414=>'转发时丢弃，比如该通道已经废弃',
        9415=>'人工审核失败',
        9416=>'可能是诈骗信息',
        9417=>'不匹配模板',
        9418=>'拒绝审核（审核功能可能关闭）',
        9419=>'超过该手机号码的日发送次数限制',
        9501=>'非法目标地址，即手机号',
        9502=>'消息无法投入队列',
        9601=>'上行路由失败',
        9602=>'超过最大重试',
        9701=>'通知失败',
        9702=>'处理配置错误',
        9801=>'投递地址错',
        9802=>'无法连接到服务器',
        9803=>'投递发送数据失败',
        9804=>'投递接收结果失败',
        9902=>'网关无此能力',
        9903=>'二进制数据太长了；如网关没有特别说明，一般不能超过 140，',
        9904=>'网关不支持 EsmClass 字段，或等同字段',
        9905=>'网关不支持 ProtocolID 字段，或等同字段',
        9906=>'网关不支持 UDHI 字段，或等同字段',
        9907=>'网关支持 Letter 字段发送，但短信记录没有 letter',
        9908=>'网关不存在',
        9909=>'网关没有应答',
        9910=>'网关不支持该短信编码',
        9911=>'区域错误',
    ];


    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        // 发送请求
        $requestData = array(
            'un' => $config->get('api_key'),
            'pw' => $config->get('api_secret'),
            'sm' => $message->getContent($this),
            'da' => $to->getUniversalNumber(),
            'rd' => 1,
            'dc' => 15,
            'rf' => 2,
            'tf' => 3,
        );
        $url = self::SENDURL . '?' . http_build_query($requestData);
        $result = $this->request('get',$url);
        if (false == $result['success']){
            throw new GatewayErrorException(self::ERR_MSG[$result['r']],$result['r'],$result);
        }
        return $result;
    }
}