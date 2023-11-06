<?php

namespace QueryBuilder\Src;

use Closure;
use PDO;

class Builder
{
    protected $pdo;
    protected $table;
    protected $select = '*';
    protected $wheres = null;
    protected $values = [];
    protected $limit = null;
    protected $order_bys = null;
    protected $group_bys = null;
    protected $joins = [];


    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($columns)
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : implode(', ', func_get_args());
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        return $this->getWhere($column, $operator, $value, 'and');
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->getWhere($column, $operator, $value, 'or');
    }

    public function take(int $count)
    {
        $this->limit = $count;
        return $this;
    }

    public function groupBy($column){
        $this->group_bys[] = [
            'column' => $column
        ];
        return $this;
    }

    public function orderBy($column,$sort='asc'){
        $this->order_bys[] = [
            'column' => $column,
            'sort' => $sort,
        ];
        return $this;
    }

    public function join($table,Closure $callback) {
        return $this->getJoin($table,$callback,'inner');
    }

    public function leftJoin($table, $callback) {
        return $this->getJoin($table,$callback,'left');
    }

    public function rightJoin($table, $callback) {
        return $this->getJoin($table,$callback,'right');
    }

    public function toSql()
    {
       $sql = 'select ' . $this->select . ' from ' . $this->table;

        if(isset($this->joins) and count($this->joins)){
            foreach ($this->joins as $item){
                $sql .= ' '.$item. ' ';
            }
        }

        if(isset($this->wheres)) $sql .= ' where ' . ltrim(ltrim(ltrim($this->wheres),'and'),'or');

        if(isset($this->group_bys)){
            $sql .= ' group by ';
            foreach ($this->group_bys as $index => $item){
                if($index>0){
                    $sql .= ', ';
                }
                $sql .= $item['column'] . '';
            }
        }

        if(isset($this->order_bys)){
            $sql .= ' order by ';
            foreach ($this->order_bys as $index => $item){
                if($index>0){
                    $sql .= ', ';
                }
                $sql .= $item['column'] . ' ' . $item['sort'] . '';
            }
        }

        if(isset($this->limit)) $sql .= ' limit ' . $this->limit;

       return $sql;
    }

    public function toQuery(){
        $stmt = $this->pdo->prepare($this->toSql());
        $stmt->execute($this->values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getWhere($column, $operator_name, $value, $type = 'and')
    {
        $operator = is_null($value) ? '=' : $operator_name;
        $value = is_null($value) ? $operator_name : $value;

        if ($column instanceof Closure) {
            $subQueryBuilder = new Builder();
            $column($subQueryBuilder);
            $this->wheres .= ' ' . $type . ' (' . $subQueryBuilder->wheres . ') ';

            $this->values = array_merge($this->values, $subQueryBuilder->values);
        } else {
            if (strlen($this->wheres) > 0) {
                $this->wheres .= $type . ' ';
            }
            $this->wheres .= $column . ' ' . $operator . ' ? ';
            $this->values[] = $value;
        }

        return $this;
    }

    private function getJoin($table, $callback, $type = 'inner') {
        $join_builder = new Builder();
        $callback($join_builder);
        $join_condition = $join_builder->getJoinCondition();

        $where = ltrim(ltrim(ltrim($join_builder->getJoinWhere()),'and '),'or ');
        $this->joins[] = " $type join $table on $join_condition and $where";
        return $this;
    }

    public function on($first_column, $operator, $secound_column) {
        $this->join_condition = "$first_column $operator $secound_column";
        return $this;
    }

    private function getJoinCondition() {
        return $this->join_condition;
    }

    private function getJoinWhere() {
        $where=null;
        foreach(explode('?',$this->wheres) as $index=>$item){
            $where .= $item;
            if(isset($this->values) and isset($this->values[$index])){
                $where .= "'".$this->values[$index]."'" ?? null;
            }
        }

        return $where;
    }
}