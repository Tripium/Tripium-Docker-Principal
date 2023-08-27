<?php


namespace WooKit\Shared\Middleware\Middlewares;


interface IMiddleware {
	public function validation(array $aAdditional= []): array;
}
