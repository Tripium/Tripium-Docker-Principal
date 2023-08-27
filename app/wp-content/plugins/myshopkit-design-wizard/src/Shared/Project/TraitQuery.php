<?php

namespace MyshopKitDesignWizard\Shared\Project;

/**
 * Class AbstractQuery
 */
trait TraitQuery {
	/**
	 * @var
	 */
	private $oQueryHandler;
	/**
	 * @var
	 */
	private $oPluckHandler;

	/**
	 * @param IQueryHandler $oQueryHandler
	 *
	 * @return $this
	 */
	public function setQueryHandler( IQueryHandler $oQueryHandler ) {
		$this->oQueryHandler = $oQueryHandler;

		return $this;
	}

	/**
	 * @param IPluckHandler $oPluckHandler
	 *
	 * @return $this
	 */
	public function setPluckHandler( IPluckHandler $oPluckHandler ) {
		$this->oPluckHandler = $oPluckHandler;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function get() {
		return $this->oQueryHandler->query( $this->oPluckHandler );
	}
}


