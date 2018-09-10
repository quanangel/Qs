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
        'prefix'    => '',
    ];
    
    // SQL表达式
    protected $exp = [
        'eq'    => '=',
        'neq'   => '<>',
        'gt'    => '>',
        'egt'   => '>=',
        'lt'    => '<',
        'elt'   => '<=',
        'like'   => 'LIKE',
        'notlike'   => 'NOT LIKE',
        'not like'   => 'NOT LIKE',
        'in'   => 'IN',
        'noin'   => 'NOT IN',
        'no in'   => 'NOT IN',
        'exp' => 'EXP',
        'between' => 'BETWEEN',
        'notbetween' => 'NOT BETWEEN',
        'not between' => 'NOT BETWEEN',
        'null' => 'NULL',
        'notnull' => 'NOT NULL',
        'not null' => 'NOT NULL',
    ];

    protected $option = [
        'table' => '',
        'alias' => '',
        'distinct' => '',
        'field' => '',
        'join' => '',
        'where' => '',
        'group' => '',
        'having' => '',
        'order' => '',
        'limit' => '',
        'union' => '',
        'lock' => '',
        'comment' => '',
        'force' => '',
    ];

    // SQL语句
    protected $selectSql    = 'SELECT%DISTINCT%%FIELD% FROM %TABLE%%ALIAS%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT%%LOCK%%COMMENT%';
    protected $insertSql    = '%INSERT% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%';
    protected $insertAllSql = '%INSERT% INTO %TABLE% (%FIELD%) %DATA% %COMMENT%';
    protected $updateSql    = 'UPDATE %TABLE% SET %SET% %JOIN% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%';
    protected $deleteSql    = 'DELETE FROM %TABLE% %USING% %JOIN% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%';
    protected $joinSql      = '%JOIN%';

    protected $aliasName = '';

    protected static $instance;
    protected $connect = null;

    public function __construct($options = []) {
        // 如有传入参数则引用
        foreach ( $options as $name => $value ) { $this->_config[$name] = $value;}
        if ( is_null($this->connect) && $this->_config['status'] == 1 ) {
            try {
                $this->connect = new \PDO($this->make_dsn(), $this->_config['user'], $this->_config['pwd']);
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

    // TODO:暂不使用，待有需要完善
    protected function parseDistinct($distinct = '') {return $distinct;}
    protected function parseUnion($union ='') {return $union;}
    protected function parseLock($lock='') {return $lock;}
    protected function parseComment($comment='') {return $comment;}

    //  生成别名
    protected function parseAlias($alias = '') {
        $alias = empty($alias) ? '' : '`'. $alias . '` ';
        return $alias;
    }

    // 生成输出字段
    protected function parseField($value = '') {
        if ('*' == $value || empty($value) ) {
            $value = '*';
        } elseif (is_array($value)) {
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = [];
            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    $array[] = $this->parseKey($v);
                } else if (!is_numeric($k)) {
                    $array[] = $this->parseKey($k) . ' AS ' . $this->parseKey($v);
                }
            }
            $value = ' ' . implode(',',$array);
        }
        return $value;
    }

    // 生成表名
    protected function parseTable($table = '') {
        if ( !is_string($table) ) return '';
        return '`' . $table . '` ';
    }

    // 生成强制使用索引语句
    protected function parseForce($index = '') {
        if ( empty($index) ) return '';
        return sprintf(" FORCE INDEX ( %s ) ", is_array($index) ? implode(',', $index) : $index);
    }

    // 生成查询语句
    protected function parseWhere($value,$exp = 'AND') {
        if ( empty($value) ) return '';
        if ( is_string($value) ) return $value;
        if ( is_array($value) ) {
            $array = [];
            foreach ($value as $k=>$v) {
                if ( is_array($v) ) {
                    $array[] = $this->parseKey($k) . $this->exp[array_shift($v)] . '"' . array_shift($v) . '"';
                } else {
                    $array[] = $this->parseKey($k) . '"' . $v . '"';
                }
            }
            $value = ' WHERE (' . implode($exp,$array) . ')';
        }
        return $value;
    }

    // 生成分组语句
    protected function parseGroup($group) {
        return !empty($group) ? ' GROUP BY ' . $this->parseKey($group) : '';
    }
    // 生成having语句
    protected function parseHaving($having = '') {
        return !empty($having) ? ' HAVING ' . $having : '';
    }

    // 生成排序语句
    protected function parseOrder($order = '') {
        if ( empty($order) || !is_string($order) ) return '';
        $order = explode(',',$order);
        $array = [];
        foreach ( $order as $value ) {
            $value = explode(' ',$value);
            $array[] = $this->parseKey(array_shift($value)) . ' ' . strtoupper(array_shift($value));
        }
        $order = ' ORDER BY' . implode(',',$array);
        return $order;
    }

    // 生成限制条数语句
    protected function parseLimit($limit= '') {
        if ( empty($limit) || !is_string($limit)) return '';
        $limit = explode(',',$limit);
        if ( count($limit) > 1 ) $limt = array_shift($limit) . ',' . array_shift($limit);
        return ' LIMIT ' . $limit;
    }

    protected function parseJoin($table,$where,$join = 'INNER'){
        if ( empty($join) || !is_array($table) ) return '';
        ' LEFT JOIN `zd_order` `b` ON `b`.`buy_id`=`a`.`user_id`';
        $str = ' ' . strtoupper($join) .' JOIN `' . key($table) . '` `' . array_shift($table) . '` ON ' . $where;

        return $str;
    }

    protected function parseKey($value){
        $array = explode('.',$value);
        if ( count($array) > 1 ) {
            $value = '';
            foreach ($array as $k => $v) {
                $array[$k] = '`' . $v . '`';
            }
            $value = implode('.',$array);
        } else {
            $value = '`' . $value . '`';
        }
        return $value;
    }

    

}