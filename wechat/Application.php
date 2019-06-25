<?php

namespace Qs\wechat;

Class Application {

    private $config = [];

    /**
     * @Author : Qs
     * @Name   : 
     * @Note   : 
     * @Time   : 2019/06/25 14:12
     * @param    Array    $options
     **/
    public function __construct($type = 'Subscription',$options = []) {
        if ($options) foreach ($options as $k=>$v) {$this->config[$k]=$v;}
        $className = '';
        switch ($type) {
            case 'Subscription':
                $className = '\\Qs\\wechat\\Application\\Subscription';
                break;
        }
        return new $className($this->options);
    }
}