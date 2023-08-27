<?php


namespace WooKit\Shared\PostMeta;


use WooKit\Shared\AutoPrefix;

trait TraitShowOnPageHandler {
	private string $scheduleKey = 're_update_config';

	private function getScheduleKey() {
		$this->scheduleKey = AutoPrefix::namePrefix( $this->scheduleKey );

		return $this->scheduleKey;
	}

	private function clearSchedule( string $postID ): void {
		wp_clear_scheduled_hook( $this->getScheduleKey(), [ $postID ] );
	}

	private function setScheduleHook( string $postID ): void {
		settype( $postID, 'string' );

		wp_schedule_single_event( time() + 20, $this->getScheduleKey(), [ $postID ] );
	}

	private function reUpdateConfig( $postID, $expectedPostType ) {
		if ( get_post_type( $postID ) === AutoPrefix::namePrefix( $expectedPostType ) ) {
			$showOnMode = get_post_meta( $postID, AutoPrefix::namePrefix( 'showOnPageMode' ), true );
			$aConfig    = get_post_meta( $postID, AutoPrefix::namePrefix( 'config' ), true );
			if ( $showOnMode == 'all' ) {
				$aConfig['targeting']['showOnPage'] = 'all';
			} else {
				$aConfig['targeting']['showOnPage'] = get_post_meta( $postID, AutoPrefix::namePrefix( 'showOnPage' ) );
			}

			update_post_meta( $postID, AutoPrefix::namePrefix( 'config' ), $aConfig );
		}
	}

	private function maybeUpdateConfigAfterUpdatePost( $postID, $metaKey, $postTypeExpected ) {
		if ( $metaKey == AutoPrefix::namePrefix( 'showOnPage' ) &&
		     ( get_post_type( $postID ) == AutoPrefix::namePrefix( $postTypeExpected ) )
		) {
			$postID = (string) $postID;
			$this->clearSchedule( $postID );
			$this->setScheduleHook( $postID );
		}
	}
}
