<?php

namespace MyshopKitDesignWizard\Sidebar\Controller;

use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class SidebarController
{
	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
	}

	public function registerRouters()
	{
		register_rest_route(MYSHOPKIT_DW_REST, 'sidebars',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getItemsSidebars'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function getItemsSidebars(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$oCountProjectsTrash = wp_count_posts(AutoPrefix::namePrefix('my_projects'));
			$aProjects = get_posts([
				'post_type'      => AutoPrefix::namePrefix('my_projects'),
				'posts_per_page' => -1,
				'post_status'    => ['publish', 'draft'],
				'post_parent'    => 0

			]);
			$oQueryImage = new WP_Query([
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg,image/jpg,image/png',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'author__in'     => get_current_user_id()
			]);
			$countImage = $oQueryImage->found_posts;

			wp_reset_postdata();
			return MessageFactory::factory('rest')->success('Passed', [
				[
					'heading'  => 'Projects',
					'total'    => count($aProjects),
					'endpoint' => 'me/projects',
				],
				[
					'heading'  => 'Photos',
					'total'    => $countImage,
					'endpoint' => 'me/images'
				],
				[
					'heading'  => 'My Trash',
					'total'    => (int)$oCountProjectsTrash->trash,
					'endpoint' => 'me/trash'
				]
			]);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}
}