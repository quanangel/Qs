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
        $this->option['alias'] = $this->parseAlias($alias);
        return $this;
    }

    public function select() {
        $sql = str_replace(
            ['%TABLE%', '%ALIAS%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->option['table'],
                $this->option['alias'],
                $this->parseDistinct($this->option['distinct'] ),
                $this->parseField($this->option['field'] ),
                $this->option['join'],
                $this->parseWhere($this->option['where']),
                $this->parseGroup($this->option['group']),
                $this->parseHaving($this->option['having']),
                $this->parseOrder($this->option['order']),
                $this->parseLimit($this->option['limit']),
                $this->parseUnion($this->option['union']),
                $this->parseLock($this->option['lock']),
                $this->parseComment($this->option['comment']),
                $this->parseForce($this->option['force']),
            ], $this->selectSql);
        $return =  $this->connect->query($sql)->fetchAll( \PDO::FETCH_ASSOC );
        $this->reset_option();
        return $return;
    }

    public function update($set =[]) {
        $sql = str_replace(
            ['%TABLE%', '%SET%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->option['table'],
                $this->parseSet($set),
                $this->option['join'],
                $this->parseWhere($this->option['where']),
                $this->parseOrder($this->option['order']),
                $this->parseLimit($this->option['limit']),
                $this->parseLock($this->option['lock']),
                $this->parseComment($this->option['comment']),
            ], $this->updateSql);
        $return = $this->connect->exec($sql);
        $this->reset_option();
        return $return;
    }

    public function insert($add, $replace = false) {
        $fields = array_keys($add);
        $values = array_values($add);

        $sql = str_replace(
            ['%INSERT%', '%TABLE%', '%FIELD%', '%DATA%', '%COMMENT%'],
            [
                $replace ? 'REPLACE' : 'INSERT',
                $this->option['table'],
                implode(' , ', $fields),
                implode(' , ', $values),
                $this->parseComment($this->option['comment']),
            ], $this->insertSql);
        $return = $this->connect->exec($sql);
        $this->reset_option();
        return $return;
    }

    public function delete(){
        'DELETE FROM %TABLE%%USING%%JOIN% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%';
        $sql = str_replace(
            ['%TABLE%', '%USING%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->option['table'],
                '',
                $this->option['join'],
                $this->parseWhere($this->option['where']),
                $this->parseOrder($this->option['order']),
                $this->parseLimit($this->option['limit']),
                $this->parseLock($this->option['lock']),
                $this->parseComment($this->option['comment']),
            ], $this->deleteSql);
        $return = $this->connect->exec($sql);
        $this->reset_option();
        return $return;
    }
    
    protected function reset_option(){
        foreach ($this->option as $k => $v) {
            $this->option[$k] = '';
        }
    }

}