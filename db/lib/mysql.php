<?php 

namespace Qs\db\lib;

use Qs\db\lib\base;

use Qs\log\Log;

class mysql extends base {



    public function __construct($options) {
        parent::__construct($options);
    }

    public function field($string) {
        try {
            if ( !is_string($string) ) throw new exception("field语句的参数必须为字符串");
            $this->_alias['field'] = $string;
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function table($string) {
        try {
            if ( !is_string($string) ) throw new exception("table语句的参数必须为字符串");
            $this->_alias['table'] = $string;
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function where($where) {
        try {
            $this->_alias['where'] = '';
            if ( is_array($where) ) {
                foreach ($where as $k => $v) {
                    if ( is_array($v) ) {
                        $this->_alias['where'] .= " `$k`";
                        foreach ( $v as $vv ) {
                            $this->_alias['where'] .= " $vv";
                        }
                        $this->_alias['where'] .= " and";
                    } else {
                        $this->_alias['where'] .= " `$k` = '$v' and";
                    }
                }
                $this->_alias['where'] = rtrim($this->_alias['where'], 'and');
            } else if ( is_string($where) ) {
                $this->_alias['where'] = $where;
            } else {
                throw new exception('查询条件只能为数组或字符串');
            }
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function limit($limit, $num = '') {
        try {
            if ( is_numeric($limit) ) {
                if ( is_numeric($num) ) {
                    $this->_alias['limit'] = "$limit,$num";
                } else {
                    $this->_alias['limit'] = $limit;
                }
            } else {
                throw new exception('limit语句的参数必须为数字');
            }
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function order($string) {
        try {
            if ( !is_string($string) ) throw new exception("order语句的参数必须为字符串");
            $this->_alias['order'] = $string;
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function group($string) {
        try {
            if ( !is_string($string) ) throw new exception("group语句的参数必须为字符串");
            $this->_alias['group'] = $string;
        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
        return $this;
    }

    public function select() {
        try {

        } catch (\Exception $e) {
            Log::instance()->save($e->getMessage(),'db_mysql_error');
        }
    }

    public function update() {

    }

    public function add() {

    }

}