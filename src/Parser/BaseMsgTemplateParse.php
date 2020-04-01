<?php

namespace QsSendMsg\Parser;

class BaseMsgTemplateParse{
    
    static protected $_var_func = array(
        '测试名称' => 'parseTestName',
    );
    
    static final public function parse($args, $content){
        while(preg_match('/\[#(.+?)#\]/i', $content, $matches)){
            $key = $matches[1];

            $callClass=get_called_class();
            $func = $callClass::$_var_func[$key];
            
//            if(!$func){
//                $func = self::$_var_order_process_func[$key];
//            }
            
            $parse_value = call_user_func(get_called_class().'::'.$func, $args);
            $content = str_replace('[#'.$key.'#]', $parse_value, $content);
            $matches = array();
        }
        return $content;
    }
    
    static public function parseTestName($args){
        $test = $args['test'];
        return $test;
    }
}

