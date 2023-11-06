<?php
namespace QueryBuilder\Src;

use Closure;
use PDO;
use QueryBuilder\Src\Builder;

class Getter extends Builder
{
    public function get()
    {
        return $this->toQuery();
    }

    public function find($id)
    {
        return $this->where('id',$id)->toQuery();
    }

    public function first(){
        return $this->take(1)->toQuery();
    }

    public function firstWhere($column,$value){
        return $this->where($column,$value)->first();
    }
}