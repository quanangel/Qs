<?php 

namespace Qs\db\lib;

use Qs\db\lib\base;

use Qs\log\Log;

class mysql extends base {

    public function __construct($options) {
        parent::__construct($options);
    }

    public function field($field) {
        $this->option['field'] = $this->parseField($field);
        return $this;
    }

    public function table($table){
        $this->option['table'] = $this->parseTable($table);
        return $this;
    }

    public function where($where) {
        $this->option['where'] = $where;
        return $this;
    }

    public function limit($limit) {
        $this->option['limit'] = $limit;
        return $this;
    }

    public function order($order) {
        $this->option['order'] = $order;
        return $this;
    }

    public function group($group) {
        $this->option['group'] = $group;
        return $this;
    }

    public function join($table,$where,$join = 'INNER'){
        $this->option['join'] .= $this->parseJoin($table,$where,$join = 'INNER');
        return $this;
    }
    public function alias($alias) {
        $this->option['alias'] = $alias;
    }

    public function select() {
        $sql = str_replace(
            ['%TABLE%', '%ALIAS%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->parseTable($options['table']),
                $options['alias'],
                $this->parseDistinct($options['distinct']),
                $this->parseField($options['field']),
                $options['join'],
                $this->parseWhere($options['where']),
                $this->parseGroup($options['group']),
                $this->parseHaving($options['having']),
                $this->parseOrder($options['order']),
                $this->parseLimit($options['limit']),
                $this->parseUnion($options['union']),
                $this->parseLock($options['lock']),
                $this->parseComment($options['comment']),
                $this->parseForce($options['force']),
            ], $this->selectSql);
        return $sql;
    }

    public function update() {
    }

    public function add() {
    }

}