<?php

namespace WooKit\Images\Controllers;

use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Message\MessageDefinition;
use WooKit\Shared\Upload\Base64Upload;
use WooKit\Shared\Upload\ImageURLUpload;
use WooKit\Shared\Upload\TraitHandleThumbnail;
use WooKit\Shared\Upload\WPUpload;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class ImageController
{
    use TraitHandleThumbnail;
    private array $aParameters
        = [
            'post_mime_type',
            'posts_per_page',
            'orderby',
            'order',
            'id',
            'paged',
            'userID'
        ];

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
        add_action('after_setup_theme', [$this, 'addImageSizes']);
    }

    public function addImageSizes()
    {
        add_image_size('5x5', 5, 5);
        add_image_size('thumbnail', get_option('thumbnail_size_w'), get_option('thumbnail_size_h'), false);
        add_image_size('medium', get_option('medium_size_w'), get_option('medium_size_h'), false);
        add_image_size('large', get_option('large_size_w'), get_option('large_size_h'), false);
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'images',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'uploadImage'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getImages'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'me/images',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getMeImages'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'images/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getImage'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function uploadImage(WP_REST_Request $oRequest)
    {
        return $this->updateImage($oRequest);
    }

    public function updateImage(WP_REST_Request $oRequest)
    {
        try {
            if (!is_user_logged_in()) {
                throw new Exception(MessageDefinition::youMustLogin(), 401);
            }
            $source = $oRequest->get_param('source');
            $id = $oRequest->get_param('id');

            switch ($source) {
                case 'base64':
                    $oUpload = new Base64Upload();
                    $aFileInfo = $oRequest->get_params();
                    $isSingular = true;
                    break;
                case 'stock':
                case 'self_hosted': // duplicate image
                    $oUpload = new ImageURLUpload();
                    $aFileInfo = $oRequest->get_params();
                    $isSingular = true;
                    break;
                default:
                    $oUpload = new WPUpload();
                    $aFileInfo = $oRequest->get_file_params();
                    $isSingular = isset($aFileInfo['tmp']);
                    break;
            }

            unset($aFileInfo['id']);
            unset($aFileInfo['source']);

            if (empty($aFileInfo)) {
                return MessageFactory::factory('rest')
                    ->error(
                        esc_html__('The file is required', 'wookit'),
                        422
                    );
            }

            $oUpload->isSingleUpload($isSingular)
                ->setFile($aFileInfo);

            if ($type = $oRequest->get_param('type')) {
                $oUpload->setType($type);
            }

            if (!empty($source)) {
                $oUpload->setImageSource($source);
            }

            if ($id) {
                $oUpload->setUpdateAttachmentId($id);
            }

            $aResponse = $oUpload->processUpload();

            if ($aResponse['status'] == 'error') {
                return MessageFactory::factory('rest')->error(
                    $aResponse['message'], $aResponse['code']
                );
            }

            if ($isSingular) {
                return MessageFactory::factory('rest')->success($aResponse['message'], [
                    'item' => $aResponse['data']['item']
                ]);
            }

            return MessageFactory::factory('rest')->success($aResponse['message'], [
                'items' => $aResponse['data']['items']
            ]);
        } catch (Exception $oException) {
            return MessageFactory::factory('rest')->error(
                $oException->getMessage(), $oException->getCode()
            );
        }
    }

    public function getImages(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')->error(MessageDefinition::youMustLogin(), 401);
        }
        $aArgs = $this->handleParamArgs($oRequest->get_params());
        $aResponse = $this->queryImages($aArgs);

        return MessageFactory::factory('rest')->success(esc_html__('There is featured image', 'wookit'),
            [
                'items'    => $aResponse['aImages'],
                'maxPages' => $aResponse['maxPages'],
                'maxPosts' => $aResponse['maxPosts'],
                'paged'    => $aResponse['paged']
            ]
        );
    }

    public function handleParamArgs($aParams): array
    {
        $aArgs = [];
        foreach ($this->aParameters as $keyArgs) {
            if (array_key_exists($keyArgs, $aParams)) {
                switch ($keyArgs) {
                    case 'id':
                        $aArgs['p'] = $aParams['id'];
                        break;
                    case 'userID':
                        $aArgs['author__in'] = $aParams['userID'];
                        break;
                    default:
                        $aArgs[$keyArgs] = $aParams[$keyArgs];
                }
            }
        }

        return $aArgs;
    }

    public function queryImages($aArgs): array
    {
        $aImages = [];
        $maxPages = 1;
        $maxPosts = 1;
        $aArgs = wp_parse_args($aArgs, [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image/jpeg,image/jpg,image/png',
            'post_status'    => 'inherit',
            'posts_per_page' => 20,
            'orderby'        => 'id',
            'order'          => 'desc',
            'paged'          => 1,
        ]);
        if (!empty($userID = get_current_user_id())) {
            $aArgs['author__in'] = $userID;
        }
        $oQuery = new WP_Query($aArgs);
        if ($oQuery->have_posts()) {
            while ($oQuery->have_posts()) {
                $oQuery->the_post();
                $aImages[] = $this->getImageInfo($oQuery->post);
            }
            $maxPages = $oQuery->max_num_pages;
            $maxPosts = $oQuery->found_posts;
        }
        wp_reset_postdata();

        return [
            'aImages'  => $aImages,
            'maxPages' => $maxPages,
            'maxPosts' => $maxPosts,
            'paged'    => (int)$aArgs['paged']
        ];
    }

    public function getImageInfo(WP_Post $oPost): array
    {
        $aThumbnails = $this->getThumbnailDefault($oPost->ID);
        $aImages = [
            'id'         => $oPost->ID,
            'label'      => $oPost->post_title,
            'url'        => $oPost->guid,
            'thumbnails' => $aThumbnails
        ];
        return $aImages;
    }

    public function getMeImages(WP_REST_Request $oRequest)
    {
        if (empty(get_current_user_id())) {
            return MessageFactory::factory('rest')
                ->error(esc_html__('You must be logged in before performing this function',
                    'wookit'), 401);
        }
        $oRequest->set_param('userID', get_current_user_id());
        $aArgs = $this->handleParamArgs($oRequest->get_params());
        $aResponse = $this->queryImages($aArgs);

        return MessageFactory::factory('rest')->success(esc_html__('There is featured image', 'wookit'),
            [
                'items'    => $aResponse['aImages'],
                'maxPages' => $aResponse['maxPages'],
                'maxPosts' => $aResponse['maxPosts'],
                'paged'    => $aResponse['paged']
            ]
        );
    }

    public function getImage(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')->error(MessageDefinition::youMustLogin(), 401);
        }
        $postID = (int)$oRequest->get_param('id');
        if (!wp_attachment_is('image', $postID)) {
            return MessageFactory::factory('rest')->error(esc_html__('Sorry,this image does not exists in database',
                'wookit'), 401);
        }

        return MessageFactory::factory('rest')->success(esc_html__('This image fetched successfully',
            'wookit'),
            [
                'item' => $this->getImageInfo(get_post($postID))
            ]
        );
    }
}
