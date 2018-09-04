<?php

namespace Qs\db;

class db {

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

    private $_className = '';
    protected $_instance = null;

    public function __construct($options = [] ){
        // 如有传入参数则引用
        foreach ( $options as $name => $value ) { $this->_config[$name] = $value;}
        if ($this->_config['status'] == 0 ) return false;
        switch ($this->_config['sqlname']) {
            case 'mysql': 
                $this->_className = '\\Qs\\db\\lib\\mysql';
                break;
            default: break;
        }
        if ( is_null($this->_instance) && $this->_className != '' ) $this->_instance = $this->_className::instance($this->_config);
        return $this->_instance;
    }

    public static function instance($options = []) {
        if ( is_null(self::$_instance) ) self::$_instance = new static($options);
        if (self::$_instance->_config['status'] == 0 ) return false;
        return self::$_instance;
    }

}