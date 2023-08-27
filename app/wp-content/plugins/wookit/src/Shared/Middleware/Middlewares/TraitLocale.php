<?php


namespace WooKit\Shared\Middleware\Middlewares;


trait TraitLocale {
	public function getMiddlewareLocale( array $aAdditional ) {
		return $aAdditional['locale'] ?? 'en';
	}
}
