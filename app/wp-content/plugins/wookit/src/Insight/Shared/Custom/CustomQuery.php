<?php

namespace WooKit\Insight\Shared\Custom;



use WooKit\Insight\Shared\Query\QueryBuilder;
use WooKit\Insight\Shared\TraitCheckCustomDateInMonth;
use WooKit\Insight\Shared\TraitJoinPost;

class CustomQuery extends QueryBuilder
{
    use TraitCheckCustomDateInMonth;
    use TraitJoinPost;

    public function select(): CustomQuery
    {
        $this->setWhat()->setJoin()->setWhere();
        $this->groupBy = ($this->checkCustomDateInMonth($this->aAdditional['from'], $this->aAdditional['to'])) ? "date" : "month";
        return $this;
    }

    public function setWhat(): QueryBuilder
    {
        $select = $this->checkCustomDateInMonth($this->aAdditional['from'], $this->aAdditional['to']) ?
            "DATE(createdDate) as date" :
            "month(createdDate) as month";
        $this->aSelectWhat[] = $select;
        return $this;
    }

    public function setWhere(): QueryBuilder
    {
        global $wpdb;
        array_push($this->aWhere,
            "(DATE(createdDate) >= '" . $wpdb->_real_escape($this->aAdditional['from']) . "' AND DATE(createdDate) <= '" .
            $wpdb->_real_escape($this->aAdditional['to']) . "')"
        );
        return $this;
    }
}
