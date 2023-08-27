<?php

namespace WooKit\Insight\Shared\Yesterday;


use WooKit\Insight\Shared\Query\QueryBuilder;
use WooKit\Insight\Shared\TraitJoinPost;

class YesterdayQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): YesterdayQuery {
		$this->setWhat();
		$this->setWhere();
		$this->setJoin();

		return $this;
	}

	public function setWhat(): QueryBuilder {
		$this->aSelectWhat[] = "DATE(createdDate) as date";

		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = "(DATE(createdDate) = DATE(CURDATE() - INTERVAL 1 DAY))";

		return $this;
	}
}
