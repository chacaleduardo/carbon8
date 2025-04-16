<?

require_once("../inc/php/functions.php");

class Builder
{
    protected $select;
    protected $from;
    protected $join;
    protected $where;
    protected $subWhere;
    protected $whereNotExists;
    protected $whereNotIn;
    protected $whereIn;
    protected $union;
    protected $groupBy;
    protected $orderBy;

    public $query;
    protected $result;
    protected $firstCondition;
    protected $toString;
    protected $whereGroup;

    protected $fragment;

    public function get()
    {   
        $this->updateQuery();

        if($this->toString)
        {
            return $this->getQuery();
        }

        $this->result = d::b()->query($this->query) or die('getTipo: ' . mysqli_error(d::b()));

        return $this->result;
    }

    public function toString()
    {
        $this->toString = true;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function updateQuery()
    {
        if(!$this->firstCondition)
        {
            $this->firstCondition = $this->getFirstCondition();
        }

        $this->query = $this->select." ".
                        $this->from." ".
                        $this->join." ".
                        $this->firstCondition." ".
                        $this->where." ".
                        $this->whereIn." ".
                        $this->whereNotIn." ".
                        $this->whereNotExists." ".
                        $this->union." ".
                        $this->groupBy." ".
                        $this->orderBy;

        if($this->fragment)
        {
            $this->query = "($this->query)";
        }

        return $this;
    }

    public function getFirstCondition()
    {
        if(strpos($this->where, 'WHERE '))
        {
            $where = $this->where;
            $this->where = null;

            return $where;
        }

        if(strpos($this->whereNotIn, 'WHERE '))
        {
            $where = $this->whereNotIn;
            $this->whereNotIn = null;

            return $where;
        }

        if(strpos($this->whereNotExists, 'WHERE '))
        {
            $where = $this->whereNotExists;
            $this->whereNotExists = null;

            return $where;
        }
    }

    public function union($union)
    {
        $this->updateQuery();

        if(!$this->union)
        {
            $this->union = "";
        }

        $this->union .= " UNION ".$union->query;

        return $this;
    }

    public function select($select)
    {
        if(!$this->select)
        {
            $this->select = 'SELECT ';
        }

        $this->select .= $select;

        $this->updateQuery();

        return $this;
    }

    public function where($where, $orwhere = false)
    {
        $whereOrAnd = 'AND';

        if($orwhere)
        {
            $whereOrAnd = 'OR';
        }

        if(!$this->where && !$this->firstCondition)
        {
            $this->where = '';
            $whereOrAnd = 'WHERE ';

            if($this->fragment)
            {
                $whereOrAnd = '';
            }
        }

        // if($this->whereGroup)
        // {
        //     if(!$this->subWhere)
        //     {
        //         $whereOrAnd .= "(";
        //     }

        //     $this->subWhere .= " $whereOrAnd $where";

        //     $this->subWhereFormat();

        //     return $this;
        // }

        if(is_callable($where))
        {
            $this->whereGroup = (new Builder())->setFragment(true);
            $where($this->whereGroup);

            $where = $this->whereGroup->getQuery();
            $this->where .= " $whereOrAnd $where";

            return $this;
        }

        $this->where .= " $whereOrAnd $where";

        $this->updateQuery();

        return $this;
    }

    public function orWhere($orWhere)
    {
        $this->where($orWhere, true);
    }

    public function whereIn($column, $whereIn)
    {
        $whereOrAnd = 'AND';

        if(!$this->$whereIn && !$this->firstCondition)
        {
            $this->$whereIn = '';
            $whereOrAnd = 'WHERE ';
        }

        $this->$whereIn .= " $whereOrAnd $column IN($whereIn)";

        $this->updateQuery();

        return $this;
    }

    public function whereNotIn($column, $whereNotIn)
    {
        $whereOrAnd = 'AND';

        if(!$this->whereNotIn && !$this->firstCondition)
        {
            $this->whereNotIn = '';
            $whereOrAnd = 'WHERE ';
        }

        $this->whereNotIn .= " $whereOrAnd $column NOT IN($whereNotIn)";

        $this->updateQuery();

        return $this;
    }

    public function whereNotExists($query)
    {
        $whereOrAnd = 'WHERE ';

        if($this->whereExists || $this->where)
        {
            $whereOrAnd = 'AND ';
        }
        
        $this->updateQuery();

        $this->whereExists = "$whereOrAnd NOT EXISTS($query)";

        return $this;
    }

    public function from($from)
    {
        if(!$this->from)
        {
            $this->from = 'FROM ';
        }

        $this->from .= $from;

        $this->updateQuery();

        return $this;
    }

    public function join($table, $on = null, $leftOrRight = null)
    {
        $this->join .= "$leftOrRight JOIN $table ON($on) ";

        $this->updateQuery();

        return $this;
    }

    public function leftJoin($table, $on = null)
    {
        return $this->join($table, $on, 'LEFT');
    }

    public function innerJoin($table, $alias = 't')
    {
        if(!$this->join)
        {
            $this->join = ''    ;
        }

        $this->join .= " INNER JOIN $table $alias ";

        $this->updateQuery();

        return $this;
    }

    public function groupBy($groupBy)
    {
        if(!$this->groupBy)
        {
            $this->groupBy = "GROUP BY ";
        }

        $this->groupBy .= $groupBy;

        $this->updateQuery();

        return $this;
    }

    public function orderBy($column, $order = 'ASC')
    {
        if(!$this->orderBy)
        {
            $this->orderBy = "ORDER BY ";
        }

        $this->orderBy .= "$column $order";

        $this->updateQuery();

        return $this;
    }

    public function subWhereFormat()
    {
        $this->subWhere = str_replace([")", " )"], '', $this->subWhere);
        $this->subWhere = substr_replace($this->subWhere, ')', strlen($this->subWhere), 0);
    }

    public function setFragment($value)
    {
        $this->fragment = $value;

        return $this;
    }
}