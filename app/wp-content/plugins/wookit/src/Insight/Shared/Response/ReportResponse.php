<?php


namespace WooKit\Insight\Shared\Response;


abstract class ReportResponse {
	protected array $aRawData    = [];
	protected array $aAdditional = [];

	public function setAdditional( array $aAdditional = [] ): ReportResponse {
		$this->aAdditional = $aAdditional;

		return $this;
	}

	public function setRawData( array $aRawData ): ReportResponse {
		$this->aRawData = $aRawData;

		return $this;
	}

	public abstract function parseData(): array;
}
