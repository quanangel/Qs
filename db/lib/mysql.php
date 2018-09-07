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
        return $this;
    }

    public function select() {
        $sql = str_replace(
            ['%TABLE%', '%ALIAS%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->parseTable($option['table']),
                $option['alias'],
                $this->parseDistinct($option['distinct']),
                $this->parseField($option['field']),
                $option['join'],
                $this->parseWhere($option['where']),
                $this->parseGroup($option['group']),
                $this->parseHaving($option['having']),
                $this->parseOrder($option['order']),
                $this->parseLimit($option['limit']),
                $this->parseUnion($option['union']),
                $this->parseLock($option['lock']),
                $this->parseComment($option['comment']),
                $this->parseForce($option['force']),
            ], $this->selectSql);
        return $sql;
    }

    public function update() {
    }

    public function add() {
    }

}