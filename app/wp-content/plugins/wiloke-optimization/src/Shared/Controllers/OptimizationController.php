<?php


namespace WilokeOptimization\Shared\Controllers;


use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeOptimization\Cloudflare\Models\LogModel;

class OptimizationController
{
	protected $slug       = 'wiloke-optimization';
	protected $aDashboardEndpoints
	                      = [
			'get-profile',
			'reviews',
			'messages',
			'notifications',
			'billings',
			'favorites',
			'listings',
		];

	public function __construct()
	{
		add_action('admin_menu', [$this, 'registerMenus']);
		add_action('admin_enqueue_scripts', [$this, 'adminScripts']);
		add_action('wp_enqueue_scripts', [$this, 'frontendScripts']);
	}

	protected function isOptimizationArea(): bool
	{
		if (!current_user_can('administrator')) {
			return false;
		}

		if (!isset($_GET['page']) || $_GET['page'] !== $this->slug) {
			return false;
		}

		return true;
	}

	public function frontendScripts()
	{
		if (function_exists('wilcityIsWebview')) {
			global $wiloke;

			wp_enqueue_script(
				'wiloke-optimization',
				get_template_directory_uri() . '/assets/production/js/Optimization.min.js',
				[],
				$wiloke->version,
				false
			);
		}
	}

	public function adminScripts($hook)
	{
		if (strpos($hook, $this->slug) !== false) {
			$plugin = get_plugin_data(__FILE__);
			wp_enqueue_style(
				'wiloke-optimization',
				plugin_dir_url(__FILE__) . 'settings-page.css', null,
				$plugin['Version']
			);
		}
	}

	public function getAdminPage(): string
	{
		return add_query_arg(['page' => $this->slug], admin_url('admin.php'));
	}

	public function registerMenus()
	{
		add_menu_page(
			'Wiloke Optimization',
			'Wiloke Optimization',
			'administrator',
			$this->slug,
			[$this, 'renderOptimizationSettings'],
			'dashicons-performance',
			10
		);
	}

	public function renderOptimizationSettings()
	{
		do_action('wiloke-optimization/src/Shared/Controllers/OptimizationController/renderOptimizationSettings');
	}

	protected function getWilcityDashboardUrl()
	{
		if (!class_exists('\Wiloke')) {
			return false;
		}

		$dashboardUrl = GetWilokeSubmission::getDashboardUrl('dashboard_page', true);
		$aDashboardUrls[] = GetWilokeSubmission::getDashboardUrl('dashboard_page', 'dashboard');
		$aDashboardUrls[] = $dashboardUrl . '#/';
		$aDashboardUrls[] = $dashboardUrl . '#/*';

		foreach ($this->aDashboardEndpoints as $endpoint) {
			$aDashboardUrls[] = $dashboardUrl . '#/' . $endpoint;
		}

		return $aDashboardUrls;
	}
}
