<?php

namespace Qs\log;

class Log{

    // 实例对像
    protected static $instance;

    // 默认日记目录
    private $_dirName = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
    // 默认日记文件名
    private $_fileName = '';
    // 默认后缀名
    private $_ext = '.txt';


    /**
     * Auth: Qs
     * Name: 初始化
     * Note: 
     * Time: 2018/4/4 16:51
     * @param   array   $options    传入的配置数组
     **/
    private function __construct($options = []) {
        if ( empty($this->_fileName) ) $this->_fileName = date('Ymd',time());
        $this->_dirName .= date('Ymd',time()) . DIRECTORY_SEPARATOR;
        // 如有传入参数则引用
        foreach ( $options as $name => $value ) { $this->$name = $value;}
    }

    /**
     * Auth: Qs
     * Name: 保存日记
     * Note: 
     * @param   string  $message    需要记录的内容
     * @param   string  $fileName   保存的文件名
     * @param   string  $dirName    保存的目录
     * Time: 2018/4/29 16:59 
     **/
    public function save($message = "", $fileName = '', $dirName = '') {
        // 获取目录地址
        $dirName = $dirName ? : $this->_dirName;
        // iconv转义中文
        $dir = iconv("UTF-8", "GBK", $dirName);
        // 如目录不存在则生成目录
        if ( !file_exists($dir) ) mkdir ($dir,0777,true);
        // 获取文件名
        $fileName = $fileName ? :  $this->_fileName . '_default';
        // 获取完整文件名
        $fileName = $dirName . $fileName . $this->_ext;
        // 写入
        file_put_contents($fileName,$message.PHP_EOL,FILE_APPEND);
    }

    /**
     * Auth: Qs
     * Name: 实例化
     * Note: 
     * Time: 2018/4/4 16:20 
     * @param   array   $options    传入的配置数组
     * @param   string  $fileName   配置文件名
     * @param   string  $ext        配置文件后缀名
     * @return  object
     **/
    public static function instance($options = [], $fileName = '', $ext = ''){
        if ( is_null(self::$instance) ) self::$instance = new static($options, $fileName, $ext);
        return self::$instance;
    }

}