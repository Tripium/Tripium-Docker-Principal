<?php

namespace WooKit\Insight\Shared\ThisWeek;


use WooKit\Insight\Shared\Query\QueryBuilder;
use WooKit\Insight\Shared\TraitJoinPost;

class ThisWeekQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): QueryBuilder {
		$this->setWhat()->setWhere()->setJoin();
		$this->groupBy = "DATE(createdDate)";

		return $this;
	}

	public function setWhat(): QueryBuilder {
		$this->aSelectWhat[] = "DATE(createdDate) as date";

		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = "(YEARWEEK(createdDate, 7) = YEARWEEK(CURDATE(), 7))";

		return $this;
	}
}
