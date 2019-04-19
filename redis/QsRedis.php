<?php
/**
 * @Author : Qs
 * @name   : Redis操作
 * @Note   : 
 * @Time   : 2019/4/19 1:47
 * @see    : PHP-Redis扩展项目地址 https://github.com/phpredis/phpredis
 * @see    : Redis命令参考地址 http://redisdoc.com/index.html
 **/
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
     * @param   string        $key      需获取的键名
     * @param   boolean|null  $prefix   是否需要前缀
     **/
    public function has($key, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        return $this->handler->exists($key) ? true : false;
    }

    /**
     * @Author : Qs
     * @Name   : 获取字符串类型的键值
     * @Note   : 
     * @Time   : 2019/4/18 17:53
     * @param   string        $key      需获取的键名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  string|array
     **/
    public function get($key, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        $value = $this->handler->get($key);
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
     * @param   string                  $key     设置的键名
     * @param   object|array|string     $value   设置的值
     * @param   array                   $option  参数 ['nx','xx','ex'=>3600,'px'=>360000],nx只在键不存在时,才对键进行设置操作、xx只在键已经存在时,才对键进行设置操作、ex保存秒数、px保存毫秒数
     * @param   boolean|null            $prefix  是否需要前缀
     * @return  boolean
     **/
    public function set($key, $value, $options = array(), $prefix = null) {
        if ( empty($options) ) $options['ex'] = $this->_config['EXPIRE'];
        $key = $this->getRealKey($key, $prefix);
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
     * @Name   : 获取列表类型的键值长度
     * @Note   : 
     * @Time   : 2019/4/19 15:27
     * @param   string        $key      需查询的键名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  integer
     **/
    public function llen($key, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        return $this->handler->lLen($key);      
    }

    /**
     * @Author : Qs
     * @Name   : 获取列表类型的键值
     * @Note   : 
     * @Time   : 2019/4/19 16:27
     * @param    string        $key      需要查询的键名
     * @param    integer       $start    查询范围的开始位置
     * @param    integer       $end      查询范围的结束位置
     * @param    boolean|null  $prefix   是否需要前缀
     * @return   array
     **/
    public function lrange($key, $start = 0, $end = -1, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        return $this->handler->lRange($key, $start, $end);
    }

    /**
     * @Author : Qs
     * @Name   : 将值添加入列表类型的键值
     * @Note   : 
     * @Time   : 2019/4/19 16:40
     * @param    string          $key      添加的键名
     * @param    string          $value    添加的值
     * @param    string          $to       值添加的位置，left为列头、right为列尾
     * @param    boolean|null    $prefix   是否需要前缀
     * @return   integer|false
     **/
    public function lpush($key, $value, $to = 'right', $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        $tmp = false;
        if ( 'left' == $to ) $tmp = $this->handler->lPush($key, $value);
        if ( 'right' == $to ) $tmp = $this->handler->rPush($key, $value);
        return $tmp;
    }

    /**
     * @Author : Qs
     * @Name   : 将列头或列尾的值弹出该列表
     * @Note   : 
     * @Time   : 2019/4/19 17:24
     * @param    string          $key      添加的键名
     * @param    string          $to       值添加的位置，left为列头、right为列尾
     * @param    boolean|null    $prefix   是否需要前缀
     * @return   integer|false
     **/
    public function lpop($key, $to = 'left', $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        $tmp = false;
        if ( 'left' == $to ) $tmp = $this->handler->lPop($key);
        if ( 'right' == $to ) $tmp = $this->handler->rPop($key);
        return $tmp;
    }

    /**
     * @Author : Qs
     * @Name   : 删除列表内某个值
     * @Note   : 
     * @Time   : 2019/4/19 17:39
     * @param    string          $key      添加的键名
     * @param    string          $value    值添加的位置，left为列头、right为列尾
     * @param    integer         $count    删除值的次数，值为正数方向则从头往尾，值为负数则为从尾往头，如果为0则全部该值都删除
     * @param    boolean|null    $prefix   是否需要前缀
     * @return   integer|false
     **/
    public function lrem($key, $value, $count = 0, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        return $this->handler->lRem($key, $value, $count);
    }

    /**
     * @Author : Qs
     * @Name   : 删除键值
     * @Note   : 
     * @Time   : 2019/4/18 18:10
     * @param   string        $key   需删除的键名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  boolean
     **/
    public function rm($key, $prefix = null) {
        $key = $this->getRealKey($key, $prefix);
        return $this->handler->del($key);
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
     * @param   string        $key      需查找的缓存名
     * @param   boolean|null  $prefix   是否需要前缀
     * @return  string
     **/
    public function getRealKey($key, $prefix = null){
        $prefix = is_null($prefix) ? $this->_config['PREFIX_STATUS'] : $prefix;
        $key = $prefix ? $this->_config['PREFIX'] . $key : $key;
        return $key;
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
