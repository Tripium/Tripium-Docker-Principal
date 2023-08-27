<?php


namespace WooKit\Shared\Post;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Post\Query\IQueryPost;
use WooKit\SmartBar\Services\Post\PostSkeletonService;

trait TraitHandleGetDuplicate {
	public function getDuplicate( IQueryPost $oQueryPost, $showOnPage, $postID ): array {
		$aData     = [];
		$aResponse = ( new $oQueryPost() )->setRawArgs(
			[
				'status'        => 'active',
				'showOnPage'    => $showOnPage,
				'listPostNotIn' => $postID
			]
		)->parseArgs()->query( new PostSkeletonService(), 'id,title' );

		if ( ! empty( $aResponse['data']['items'] ) ) {
			foreach ( $aResponse['data']['items'] as $aDataCampaign ) {

				if ( method_exists( $this, 'getShowOnPageCampaign' ) ) {
					$postShowOnPages = $this->getShowOnPageCampaign( $postID );
					if ( $postShowOnPages == 'all' ) {
						if ( $showOnPage == 'all' ) {
							$aData['aListIDs'][]    = $aDataCampaign['id'];
							$aData['aListTitles'][] = $aDataCampaign['title'];
						}
					} else {
						if ( $showOnPage !== 'all' ) {
							$aData['aListIDs'][]    = $aDataCampaign['id'];
							$aData['aListTitles'][] = $aDataCampaign['title'];
						}
					}
				} else {
					$aData['aListIDs'][]    = $aDataCampaign['id'];
					$aData['aListTitles'][] = $aDataCampaign['title'];
				}
			}

			return MessageFactory::factory()->success( 'Passed', $aData );
		}

		return MessageFactory::factory()->error( 'not duplicate', 400 );
	}
}
