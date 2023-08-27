<?php


namespace WooKit\Popup\Controllers;


use WooKit\Shared\AutoPrefix;
use WooKit\Shared\PostMeta\TraitShowOnPageHandler;


class PopupRegistration {
	private string $scheduleKey = 're_update_config';
	use TraitShowOnPageHandler;

	public function __construct() {
		add_action( 'cmb2_admin_init', [ $this, 'registerBox' ] );
		add_action( 'init', [ $this, 'registerPopup' ] );
		add_action( 'update_post_meta', [ $this, 'maybeUpdateConfigAfterUpdatePopup' ], 10, 4 );
		add_action( 'add_post_metadata', [ $this, 'maybeUpdateConfigAfterUpdatePopup' ], 10, 4 );
		add_action( $this->getScheduleKey(), [ $this, 'reUpdatePopupConfig' ] );
	}

	public function maybeUpdateConfigAfterUpdatePopup( $metaID, $postID, $metaKey, $metaValue ) {
		$this->maybeUpdateConfigAfterUpdatePost( $postID, $metaKey, 'popup' );
	}

	public function reUpdatePopupConfig( $postID ) {
		$this->reUpdateConfig( $postID, 'popup' );
	}

	public function registerBox() {
		$aConfig = include WOOKIT_PATH . 'src/Popup/Configs/PostMeta.php';
		foreach ( $aConfig as $aSection ) {
			$aFields = $aSection['fields'];
			unset( $aSection['fields'] );
			$oCmb = new_cmb2_box( $aSection );
			foreach ( $aFields as $aField ) {
				$aField['id'] = AutoPrefix::namePrefix( $aField['id'] );
				$oCmb->add_field( $aField );
			}
		}
	}

	public function registerPopup() {
		register_post_type( AutoPrefix::namePrefix( 'popup' ),
			include WOOKIT_PATH . 'src/Popup/Configs/PostType.php' );
	}
}
