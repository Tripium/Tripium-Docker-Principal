<?php


namespace WilokeOptimization\StaticCache\Controllers;

use WP_Admin_Bar;

class AdminBarController
{
	private $postId;
	private $termId;
	private $websiteUrl = 'https://wilcityservice.com/product/wilcity-optimization/';

	public function __construct()
	{
		add_action('wp_ajax_wiloke_optimization_render_admin_bar', [$this, 'resolveAdminBarWhenCaching']);
		add_action('wp_ajax_nopriv_wiloke_optimization_render_admin_bar', [$this, 'resolveAdminBarWhenCaching']);
		add_action('wp_enqueue_scripts', [$this, 'renderAdminBarHelperJS']);
		add_action('admin_bar_menu', [$this, 'addPurgeCache'], 81);
		add_action('wp_ajax_wiloke_optimization_purge_cache', [$this, 'proxyPurgeCache']);
	}

	public function proxyPurgeCache()
	{
		if (!current_user_can('administrator')) {
			wp_send_json_error(['msg' => 'Huh!!']);
		}

		do_action('wiloke-optimization/purge/' . $_POST['target'], isset($_POST['post_id']) ? $_POST['post_id'] : 0);
		wp_send_json_success();
	}

	public function addPurgeCache()
	{
		global $wp_admin_bar;
		if (current_user_can('administrator')) {
			$parentId = 'wiloke_optimization_bar';
			$wp_admin_bar->add_menu([
				'id'    => $parentId,
				'title' => __('Wiloke Optimization', 'wiloke-optimization')
			]);

			$wp_admin_bar->add_node([
				'id'     => 'wiloke_optimization_purge_all_cache',
				'title'  => esc_html__('Purge All Cache', 'wiloke-optimization'),
				'href'   => $this->websiteUrl,
				'parent' => $parentId
			]);

			$wp_admin_bar->add_node([
				'id'     => 'wiloke_optimization_purge_static_pages',
				'title'  => esc_html__('Clear Static Pages', 'wiloke-optimization'),
				'href'   => $this->websiteUrl,
				'parent' => $parentId

			]);

			$wp_admin_bar->add_node([
				'id'     => 'wiloke_optimization_purge_static_page',
				'title'  => esc_html__('Clear This Static Page', 'wiloke-optimization'),
				'href'   => $this->websiteUrl,
				'parent' => $parentId

			]);

			$wp_admin_bar->add_node([
				'id'     => 'wiloke_optimization_purge_nginx_cache',
				'title'  => esc_html__('Purge Nginx Cache', 'wiloke-optimization'),
				'href'   => $this->websiteUrl,
				'parent' => $parentId
			]);

			$wp_admin_bar->add_node([
				'id'     => 'wiloke_optimization_purge_cloudflare_cache',
				'title'  => esc_html__('Purge CloudFlare Cache', 'wiloke-optimization'),
				'href'   => $this->websiteUrl,
				'parent' => $parentId
			]);
		}
	}

	public function renderAdminBarHelperJS(): bool
	{
		if (\WilokeThemeOptions::getOptionDetail('general_toggle_admin_bar') !== 'when_caching') {
			return false;
		}

		wp_enqueue_style('admin-bar');
		wp_enqueue_script(
			'wiloke-optimization-adminbar',
			plugin_dir_url(__FILE__) . 'assets/adminbar.js',
			[],
			WILOKE_OPTIMIZATION_VERSION,
			true
		);

		return true;
	}

	private function renderAdminBar()
	{
		global $wp_admin_bar;
		if (empty($wp_admin_bar)) {
			if (!class_exists('WP_Admin_Bar')) {
				/* Load the admin bar class code ready for instantiation */
				include ABSPATH . WPINC . '/class-wp-admin-bar.php';

				/* Instantiate the admin bar */

				/**
				 * Filters the admin bar class to instantiate.
				 *
				 * @param string $wp_admin_bar_class Admin bar class to use. Default 'WP_Admin_Bar'.
				 * @since 3.1.0
				 *
				 */
				$admin_bar_class = apply_filters('wp_admin_bar_class', 'WP_Admin_Bar');

				if (class_exists($admin_bar_class)) {
					$wp_admin_bar = new $admin_bar_class;
				} else {
					return false;
				}

				$wp_admin_bar->initialize();
				$wp_admin_bar->add_menus();
			}
		}

		/**
		 * Load all necessary admin bar items.
		 *
		 * This is the hook used to add, remove, or manipulate admin bar items.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference
		 * @since 3.1.0
		 *
		 */
		do_action_ref_array('admin_bar_menu', [&$wp_admin_bar]);

		/**
		 * Fires before the admin bar is rendered.
		 *
		 * @since 3.1.0
		 */
		do_action('wp_before_admin_bar_render');

		$this->addEditPostLink();
		$this->addEditTermLink();

		$wp_admin_bar->render();

		/**
		 * Fires after the admin bar is rendered.
		 *
		 * @since 3.1.0
		 */
		do_action('wp_after_admin_bar_render');
	}

	public function resolveAdminBarWhenCaching()
	{
		if (!is_user_logged_in()) {
			wp_send_json_error();
		}

		if (\WilokeThemeOptions::getOptionDetail('general_toggle_admin_bar') !== 'when_caching') {
			wp_send_json_error();
		}

		global $post;
		if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
			$this->postId = $_POST['post_id'];
		}

		if (isset($_POST['term_id']) && !empty($_POST['term_id'])) {
			$this->termId = $_POST['term_id'];
		}

		ob_start();
		$this->renderAdminBar();
		$content = ob_get_contents();
		ob_end_clean();
		wp_send_json_success(['html' => $content]);
	}

	private function addEditPostLink()
	{
		global $wp_admin_bar;
		if (!empty($this->postId)) {
			$wp_admin_bar->remove_node('edit');

			$args = [
				'id'     => 'edit',
				'title'  => sprintf('Edit %s', get_the_title($this->postId)),
				'href'   => get_edit_post_link($this->postId),
				'target' => '_blank'
			];

			$wp_admin_bar->add_node($args);
		}
	}

	private function addEditTermLink()
	{
		global $wp_admin_bar;
		if (!empty($this->termId)) {
			$wp_admin_bar->remove_node('edit');

			$args = [
				'id'     => 'edit',
				'title'  => sprintf('Edit %s', get_term($this->termId)->name),
				'href'   => get_edit_term_link($this->termId),
				'target' => '_blank'
			];

			$wp_admin_bar->add_node($args);
		}
	}
}
