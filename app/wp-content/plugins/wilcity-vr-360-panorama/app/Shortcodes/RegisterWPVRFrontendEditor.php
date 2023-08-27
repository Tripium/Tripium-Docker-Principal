<?php


namespace WilcityVR\Shortcodes;


class RegisterWPVRFrontendEditor
{
	public function __construct()
	{
		add_shortcode('wilcity_wp_vr_frontend_editor', [$this, 'printFrontendShortcodes']);
	}

	public function printFrontendShortcodes()
	{

	}
}