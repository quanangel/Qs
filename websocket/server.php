<?php
// https://www.cnblogs.com/loveyoume/p/6076101.html
// https://github.com/zhenbianshu/websocket

namespace Qs\websocket;

error_reporting(E_ALL);
set_time_limit(0);// 设置超时时间为无限,防止超时

class server {
    // 实例化
    private $handler = null;
    // 配置
    private $config = [
        'ip'                => '127.0.0.1',
        'port'              => '8899',
        'listen_backlog'    => 9,
        'log_dir'           => __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
        'log_name'          => 'websocket_default',
        'log_ext'           => '.txt',
    ];
    private $master = null;

    // TODO: 初始化
    public function __construct($config = []){
        if ( !empty($config) ) foreach ($config as $k => $v) { $this->config[$k] = $v; }
    }

    private function create($domain = AF_INET, $type = SOCK_STREAM, $protocol = SOL_TCP) {
        try {
            if ( function_exists('socket_create') ) $this->master = socket_create($domain, $type, $protocol);
        } catch (\Exception $e) {
            $This->log($e->getMessage(), 'websocket_error.log');
            exit('have error');
        }
        return $this->master;
    }

    // 实例化 2019-4-22 16：57
    public static function instance($config = []) {
        if (is_null(self::$handler)) self::$handler = new static($config);
        return self::$handler;
    }


    /**
     * @Author : Qs
     * @Name   : 日记保存
     * @Note   : 
     * @Time   : 2019/4/22 17:04
     * @param    string    $message    日记内容
     * @param    string    $fileName   日记名
     * @param    string    $dirName    日记目录地址
     **/
    protected function log( $message, $fileName = '', $dirName = '' ) {
        // 获取目录地址
        $dirName = $dirName ? : $this->config['log_dir'];
        // iconv转义中文
        $dir = iconv("UTF-8", "GBK", $dirName);
        // 如目录不存在则生成目录
        if ( !file_exists($dir) ) mkdir ($dir,0777,true);
        // 获取文件名
        $fileName = $fileName ? : $this->config['log_name'];
        // 生成完整路径
        $fileName = $dirName . $fileName . $this->config['log_ext'];
        // 写入
        file_put_contents($fileName,$message.PHP_EOL,FILE_APPEND);
    }

}