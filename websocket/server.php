<?php
// https://www.cnblogs.com/loveyoume/p/6076101.html
// https://github.com/zhenbianshu/websocket

namespace Qs\websocket;

error_reporting(E_ALL);
set_time_limit(0);// 设置超时时间为无限,防止超时

class server {

    // 默认日记目录
    private $_logDir = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
    // 默认日记名
    private $_logName = 'websocket_default.log';
    // socket监听口
    private $_listenNum  = 9;

    public function __construct(){
        // TODO: 初始化
        $this->_logDir .= date('Ymd',time()) . DIRECTORY_SEPARATOR;
    }


    protected function log( $message, $fileName = '', $dirName = '' ) {
        // 获取目录地址
        $dirName = $dirName ? : $this->_logDir;
        // iconv转义中文
        $dir = iconv("UTF-8", "GBK", $dirName);
        // 如目录不存在则生成目录
        if ( !file_exists($dir) ) mkdir ($dir,0777,true);
        // 获取文件名
        $fileName = $fileName ? : $this->_logName;
        // 生成完整路径
        $fileName = $dirName . $fileName;
        // 写入
        file_put_contents($fileName,$message.PHP_EOL,FILE_APPEND);
    }

}