<?php

namespace WooKit\Insight\Shared\ThisMonth;

use WooKit\Insight\Shared\Query\QueryBuilder;
use WooKit\Insight\Shared\TraitJoinPost;

class ThisMonthQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): QueryBuilder {
		$this->setWhat()->setJoin()->setWhere();
		$this->groupBy = "weekNumber";

		return $this;
	}

	/**
	 * Tham khao https://www.w3resource.com/mysql/date-and-time-functions/mysql-week-function.php
	 * Có date('W', strtotime('1/1/2021')) = 1 => Tức là tuần đầu tiên của năm được đánh số là 1.
	 * Mysql Week(createdDate) sẽ có tuần đầu tiên của năm được đánh số là 0. Cần chuyển qua mode 7 để khớp với PHP
	 */
	public function setWhat(): QueryBuilder {
		$this->aSelectWhat[] = "Week(createdDate, 7) as weekNumber";
		$this->aSelectWhat[] = "Year(createdDate) as year";
		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = "(Year(createdDate) =  Year(CURDATE()) AND Month(createdDate) = Month(CURDATE()))";
		return $this;
	}
}
