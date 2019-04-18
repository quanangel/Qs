<?php
/*
 * File Name:QsRedis.php
 * Auth:Qs
 * Name:Redis数据缓存
 * Note:
 * Time:2017/10/18 16:32
 */
namespace Qs\redis;

Class QsRedis {
    protected $handler = null;
    protected $_config = array(
        'HOST'       => '127.0.0.1',    // 地址
        'PORT'       => 6379,           // 端口
        'PASSWORD'   => '123456',       // 验证密码
        'SELECT'     => 0,              // 选择的表
        'TIMEOUT'    => 0,              //
        'EXPIRE'     => 3600,           // 有效时间
        'PERSISTENT' => False,          // 
        'PREFIX'     => 'now_',         // 前缀
        'PREFIX_STATUS' => True,         // 是否使用前缀
    );
    public function __construct($_config = []) {
        if (!empty($_config)) $this->_config = array_merge($this->_config, $_config); // 更新配置

        $this->handler = new \Redis();
        $this->handler->connect($this->_config['HOST'],$this->_config['PORT'],$this->_config['TIMEOUT']);

        if ( !empty( $this->_config['PASSWORD'] ) ) $this->handler->auth($this->_config['PASSWORD']);

        if ( !empty( $this->_config['SELECT'] ) ) $this->handler->select($this->_config['SELECT']);
    }

    /**
     * @Author : Qs
     * @Name   : 判断是否存在该字符串类型的键
     * @Note   : 
     * @Time   : 2019/4/18 17:53
     * @param   string        $name     需获取的键名
     * @param   boolean|null  $prefix   是否需要前缀
     **/
    public function has($name, $prefix = null) {
        $name = $this->getRealKey($name, $prefix);
        return $this->handler->exists($name) ? true : false;
    }

    /**
     * @Author : Qs
     * @Name   : 获取字符串类型的键值
     * @Note   : 
     * @Time   : 2019/4/18 17:53
     * @param   string        $name     需获取的键名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  string|array
     **/
    public function get($name, $prefix = null) {
        $name = $this->getRealKey($name, $prefix);
        $value = $this->handler->get($name);
        if ( is_null($value) ) return false;
        $jsonData = json_decode($value, true);
        // 判断$jsonData是否完全等于NULL，是：直接返回$value的值，否：返回JSON格式化的数组$jsonData
        return (null === $jsonData) ? $value : $jsonData;
    }

    /**
     * @Author : Qs
     * @Name   : 设置字符串类型的键值
     * @Note   : 
     * @Time   : 2017/10/18 23:49
     * @param   string                  $name    设置的键名
     * @param   object|array|string     $value   设置的值
     * @param   array                   $option  参数 ['nx','xx','ex'=>3600,'px'=>360000],nx只在键不存在时,才对键进行设置操作、xx只在键已经存在时,才对键进行设置操作、ex保存秒数、px保存毫秒数
     * @param   boolean|null            $prefix  是否需要前缀
     * @return  boolean
     **/
    public function set($name, $value, $options = array(), $prefix = null) {
        if ( empty($options) ) $options['ex'] = $this->_config['EXPIRE'];
        $key = $this->getRealKey($name, $prefix);
        $value = ( is_object($value) || is_array($value) ) ? json_encode($value, true) : $value;
        if ( is_int($value) ) {
            $result = $this->handler->setEx($key, ( iset($options['ex']) ? $options['ex'] : $this->_config['EXPIRE'] ), $value);
        } else {
            $result = $this->handler->set($key, $value, $options);
        }
        return $result;
    }

    /**
     * @Author : Qs
     * @Name   : 删除键值
     * @Note   : 
     * @Time   : 2019/4/18 18:10
     * @param   string  $name   需删除的键名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  boolean
     **/
    public function rm($name, $prefix = null) {
        $name = $this->getRealKey($name, $prefix);
        return $this->handler->del($name);
    }

    /**
     * @Author : Qs
     * @Name   : 清除数据
     * @Note   : 
     * @Time   : 2019/4/18 18:18
     * @param   string|integer|null  $select    库名:all时为全库清除
     **/
    public function clear($select = null) {
        if ( !is_null($select) && $select == 'all') $this->handler->select($select);
        // 全部清楚
        if ( $select == 'all') return $this->handler->flushAll();
        return $this->handler->flushDB();
    }

    /**
     * @Author : Qs
     * @Name   : 获取实际存入的键名
     * @Note   : 
     * @Time   : 2019/4/18 17:01
     * @param   string        $name     需查找的缓存名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  string
     **/
    public function getRealKey($name, $prefix = null){
        $prefix = is_null($prefix) ? $this->_config['PREFIX_STATUS'] : $prefix;
        $name = $prefix ? $this->_config['PREFIX'] . $name : $name;
        return $name;
    }

    /**
     * @Author : Qs
     * @Name   : 返回句柄对象，可执行其它redis方法
     * @Note   : 
     * @Time   : 2017/10/19 0:57
     * @return  object
     **/
    public function handler(){
        return $this->handler;
    }

}
