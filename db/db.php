<?php

namespace Qs\db;

class db {

    protected static $_config = [
        'status'    => 1,
        'sqlname'   => '',
        'host'      => '127.0.0.1',
        'dbname'    => '',
        'user'      => '',
        'pwd'       => '',
        'port'      => '3306',
        'charset'   => 'utf8',
        'prefix'    => '',
    ];

    private static $_className = '';
    protected static $_instance = null;

    public static function init($options = []) {
        // 如有传入参数则引用
        foreach ( $options as $name => $value ) { self::$_config[$name] = $value;}
        if (self::$_config['status'] == 0 ) return false;
        switch (self::$_config['sqlname']) {
            case 'mysql': 
                self::$_className = '\\Qs\\db\\lib\\mysql';
                break;
            default: break;
        }
        if ( is_null(self::$_instance) && self::$_className != '' ) self::$_instance = self::$_className::instance(self::$_config);
        return self::$_instance;
    }
}