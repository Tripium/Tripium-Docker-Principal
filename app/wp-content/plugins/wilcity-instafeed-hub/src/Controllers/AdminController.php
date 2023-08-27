<?php


namespace WilokeInstagramFeedhub\Controllers;


use WilokeInstagramFeedhub\Helpers\Option;

class AdminController {
	private $menuSlug = 'instafeedhub-settings';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'registerMenus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
	}

	public function registerMenus() {
		if (defined('IFH_NAMESPACE')) {
			return false;
		}

		add_menu_page(
			esc_html__( 'InstafeedHub', 'instafeedhub-wp' ),
			esc_html__( 'InstafeedHub', 'instafeedhub-wp' ),
			'administrator',
			$this->menuSlug,
			[ $this, 'renderInstafeedHubSettings' ],
			'dashicons-instagram'
		);
	}

	public function enqueueScripts() {
		if (defined('IFH_NAMESPACE')) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) || ! isset( $_GET['page'] ) ||
			$_GET['page'] != $this->menuSlug ) {
			return false;
		}

		wp_enqueue_style( 'semantic-ui', WILCITY_INSTAFEED_HUB_URL . 'assets/semantic-ui/semantic.min.css', [] );
	}

	private function saveTokens() {
		if ( ! current_user_can( 'administrator' ) ) {
			return false;
		}

		if ( ! isset( $_POST['instafeed_hub_token'] ) ) {
			return false;
		}

		$aTokens = [];
		foreach ( $_POST['instafeed_hub_token'] as $key => $val ) {
			$aTokens[ sanitize_text_field( $key ) ] = trim( sanitize_text_field( $val ) );
		}

		Option::saveSiteTokens( $aTokens );

		return true;
	}

	public function renderInstafeedHubSettings() {
		$this->saveTokens();
		$aTokens = Option::getSiteTokens();
		?>
		<div style="margin-top: 20px; max-width: 1200px;">
			<h1 class="ui dividing header">InstafeedHub Settings</h1>
			<form class="ui form" method="POST"
			      action="<?php echo add_query_arg( [ 'page' => $this->menuSlug ], admin_url( 'admin.php' ) ); ?>">
				<div class="field">
					<label for="access-token">Access Token</label>
					<textarea rows="3" id="access-token" name="instafeed_hub_token[accessToken]"><?php echo
						esc_textarea( $aTokens['accessToken'] ); ?></textarea>
				</div>
				<div class="field">
					<label for="refresh-access-token">Refresh Token</label>
					<textarea rows="3" id="access-token"
					          name="instafeed_hub_token[refreshToken]"><?php echo esc_textarea(
							$aTokens['refreshToken'] ); ?></textarea>
				</div>
				<input type="hidden" name="page" value="<?php echo $this->menuSlug; ?>">
				<button class="ui button green" type="submit">Submit</button>
			</form>
		</div>
		<?php
	}

	public function saveInstaIds( $postId, $post ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		if ( preg_match_all( '/"instaId":([\d]+)/', $post->post_content, $aMatches ) ) {
			if ( isset( $aMatches[1] ) ) {
				update_post_meta( $postId, 'instafeedhub_ids', $aMatches[1] );
			}
		}
	}
}
