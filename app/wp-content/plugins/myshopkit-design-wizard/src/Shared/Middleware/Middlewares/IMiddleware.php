<?php


namespace MyshopKitDesignWizard\Shared\Middleware\Middlewares;


interface IMiddleware {
	public function validation(array $aAdditional= []): array;
}