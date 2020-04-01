<?php


namespace QsSendMsg\Parser;


class JobDataParser
{
    public static function parse($jobData, $content, $nextJobList){
        $args=[
            'next_job_list'=>$nextJobList,
            'content'=>$content,
            'desc'=>$jobData['data']['desc']
        ];
        switch ($jobData['type']){
            case 'wx':
                $msgData=$jobData['data']['msgData'];
                if (!$msgData[$jobData['data']['contentField']]){
                    $msgData[$jobData['data']['contentField']]=['color'=>'#173177'];
                }
                if (!$content['wx']){
                    $job='';
                    break;
                }
                $msgData[$jobData['data']['contentField']]=array_merge($msgData[$jobData['data']['contentField']],['value'=>$content['wx']]);
                $job='QsSendMsg\\Job\\SendWxMsgJob';
                $args['open_id']=$jobData['data']['openid'];
                $args['template_id']=$jobData['data']['tplId'];
                $args['data']=$msgData;
                $args['url']=$jobData['data']['url'];
                break;
            case 'sms':
                $job='QsSendMsg\\Job\\SendSmsMsgJob';
                if (!$content['sms']){
                    $job='';
                    break;
                }
                $args['mobile']=$jobData['data']['mobile'];
                $args['content']=$content['sms'];
                break;
        }
        return [$job,$args];
    }
}