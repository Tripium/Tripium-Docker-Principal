<?php

namespace WooKit\Insight\Shared\LastMonth;


use WooKit\Insight\Shared\Query\QueryBuilder;
use WooKit\Insight\Shared\TraitJoinPost;

class LastMonthQuery extends QueryBuilder
{
    use TraitJoinPost;
    public function select(): QueryBuilder
    {
        $this->setWhat()->setJoin()->setWhere();
        $this->groupBy = "weekNumber";
        return $this;
    }

    public function setWhat():QueryBuilder
    {
        $this->aSelectWhat[] = "Week(createdDate, 7) as weekNumber";
        $this->aSelectWhat[] = "Year(createdDate) as year";
        return $this;
    }

    public function setWhere():QueryBuilder
    {
        $this->aWhere[] = "(Year(createdDate) =  Year(CURDATE())";
        $this->aWhere[] = "Month(createdDate) = Month(CURDATE())-1)";
        return $this;
    }
}
