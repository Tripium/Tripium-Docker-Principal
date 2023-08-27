<?php
namespace MyshopKitDesignWizard\Illuminate\Message;

use WP_REST_Response;

interface IResponseCreatior
{
	public function successCreatior($msg, $aAdditional = null): WP_REST_Response;

	public function errorCreatior($msg, $code, $aAdditional = null): WP_REST_Response;
}