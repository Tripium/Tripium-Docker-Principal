<?php

namespace MyshopKitDesignWizard\Shared\Project;
/**
 * Interface IQueryHandler
 */
interface IQueryHandler
{
	/**
	 * @param $aArg
	 * @return mixed
	 */
	public function setArgs($aArg);

	/**
	 * @param IPluckHandler $oOutput
	 *
	 * @return mixed
	 */
	public function query( IPluckHandler $oOutput);
}
