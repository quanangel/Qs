<?php

namespace Qs\db\lib;

use Qs\log\Log;

class base {

    protected $_config = [
        'status'    => 1,
        'sqlname'   => '',
        'host'      => '127.0.0.1',
        'dbname'    => '',
        'user'      => '',
        'pwd'       => '',
        'port'      => '3306',
        'charset'   => 'utf8',
    ];

    protected static $instance;
    protected $connect = null;

    public function __construct($options = []) {
        // 如有传入参数则引用
        foreach ( $options as $name => $value ) { $this->_config[$name] = $value;}
        if ( is_null($this->connect) && $this->_config['status'] == 1 ) {
            try {
                $this->connect = new \PDO($this->make_dsn, $this->_config['user'], $this->_config['pwd']);
            } catch (\Exception $e) {
                Log::instance()->save($e->getMessage(),'db_error');
            }
        }
    }

    public static function instance($options = []) {
        if ( is_null(self::$instance) ) self::$instance = new static($options);
        if (self::$instance->_config['status'] == 0 ) return false;
        return self::$instance;
    }

    protected function make_dsn() {
        $dsn =  $this->_config['sqlname'] . ":host=" . $this->_config['host'] . ';';
        $dsn .= "dbname=" . $this->_config['dbname'] . ';';
        $dsn .= "charset=" . $this->_config['charset'] . ';';
        $dsn .= "port=" . $this->_config['port'] . ';';
        return $dsn;
    }

}