<?php


namespace WilcityHsBlog\Controllers;


class TokenController
{
	public function __construct()
	{
		add_action('wp_login', [$this, 'fetchHsBlogToken'], 10, 2);
	}

	/**
	 * @param $userLogin
	 * @param $oUser
	 */
	public function fetchHsBlogToken($userLogin, \WP_User $oUser)
	{

	}
}
