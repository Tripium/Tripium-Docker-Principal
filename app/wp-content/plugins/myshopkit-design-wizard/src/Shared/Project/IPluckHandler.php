<?php


namespace MyshopKitDesignWizard\Shared\Project;


interface IPluckHandler {
	public function setId( int $id ): IPluckHandler;

	public function setPluck( $aArgs ): IPluckHandler;

	public function setAdditionalArgs( array $aArgs ): IPluckHandler;

	public function addAdditionalArgs( array $aArgs ): IPluckHandler;

	public function get(): array;
}
