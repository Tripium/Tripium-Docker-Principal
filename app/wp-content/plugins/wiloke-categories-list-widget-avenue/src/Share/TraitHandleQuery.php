<?php

namespace WilokePostCategoriesAvenue\Share;

use stdClass;
use WP_Query;
use WP_User;

trait TraitHandleQuery
{
  public array     $aArgs           = [];
  protected string $regularPriceKey = '_regular_price';
  protected string $salePriceKey    = '_sale_price';
  protected array  $aDefineSizeImage
                                    = [
      'thumbnail' => 'small',
      'medium'    => 'medium',
      'full'      => 'large'
    ];

  public function commonParseArgs(array $aRawArgs)
  {
    if (isset($aRawArgs['filter']) &&
      in_array(
        $aRawArgs['filter'],
        ['all', 'filter', 'onSale', 'outOfStock', 'inStock', 'categories',
         'tags']
      )) {
      if (isset($aRawArgs['postNumber'])) {
        $this->aArgs['posts_per_page'] = (int)$aRawArgs['postNumber'];
      }
      if (isset($aRawArgs['limit'])) {
        $this->aArgs['posts_per_page'] = (int)$aRawArgs['limit'];
      }

      if (isset($aRawArgs['orderBy'])) {
        $this->aArgs['orderBy'] = $aRawArgs['orderBy'];
      }
      if (isset($aRawArgs['order'])) {
        $this->aArgs['order'] = $aRawArgs['order'];
      }
      if (isset($aRawArgs['categories']) && $aRawArgs['filter'] == 'filter') {
        $this->aArgs['category__in'] = array_map(function ($aCategory) {
          return (int)$aCategory['id'] ?? 0;
        }, $aRawArgs['categories']);
      }
      if ($aRawArgs['filter'] != 'filter') {
        switch ($aRawArgs['filter']) {
          case 'onSale':
            $this->aArgs['meta_query'] = [
              [
                'key'     => '_sale_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'numeric'
              ]
            ];
            break;
          case 'outOfStock':
            $this->aArgs['meta_query'] = [
              [
                'key'   => '_stock_status',
                'value' => 'outofstock'
              ]
            ];
            break;
          case 'inStock':
            $this->aArgs['meta_query'] = [
              [
                'key'     => '_stock_status',
                'value'   => 'outofstock',
                'compare' => '!='
              ]
            ];
            break;
          case 'categories':
            if (isset($aRawArgs['categories'])) {
              $aTermId = array_map(function ($aCategory) {
                return (int)$aCategory['id'] ?? 0;
              }, $aRawArgs['categories']);
              $this->aArgs['tax_query'] = [
                [
                  'taxonomy' => 'product_cat',
                  'field'    => 'term_id',
                  'terms'    => $aTermId,
                  'operator' => 'IN'
                ]
              ];
            }
            break;
          case 'tags':
            if (isset($aRawArgs['tags'])) {
              $aTermId = array_map(function ($aTags) {
                return (int)$aTags['id'] ?? 0;
              }, $aRawArgs['tags']);
              //product_tag
              $this->aArgs['tax_query'] = [
                [
                  'taxonomy' => 'product_tag',
                  'field'    => 'term_id',
                  'terms'    => $aTermId,
                  'operator' => 'IN'
                ]
              ];
            }
            break;
        }
      }

      if (isset($aRawArgs['listPostType'])) {
        $this->aArgs['post_type'] = $aRawArgs['listPostType'];
      }

      if (isset($aRawArgs['limitProducts'])) {
        $this->aArgs['limitProducts'] = $aRawArgs['limitProducts'];
      }
      $this->aArgs = wp_parse_args($this->aArgs, $this->defineArgs());
    } else {
      if (isset($aRawArgs['posts'])) {
        $this->aArgs['posts_per_page'] = count($aRawArgs['posts']);
        $this->aArgs['post__in'] = array_map(function ($aPost) {
          return (int)$aPost['id'] ?? 0;
        }, $aRawArgs['posts']);
      }

      if (isset($aRawArgs['products'])) {
        $this->aArgs['posts_per_page'] = count($aRawArgs['products']);
        $this->aArgs['post__in'] = array_map(function ($aPost) {
          return (int)$aPost['id'] ?? 0;
        }, $aRawArgs['products']);
      }
    }
    if (isset($aRawArgs['limitCategories'])) {
      $this->aArgs['posts_per_page'] = (int)$aRawArgs['limitCategories'];
    }
    if (isset($aRawArgs['listPostType'])) {
      $this->aArgs['post_type'] = $aRawArgs['listPostType'];
    }

    if (isset($aRawArgs['limitPosts'])) {
      $this->aArgs['limitPosts'] = $aRawArgs['limitPosts'];
    }
    if (isset($aRawArgs['orderBy'])) {
      $this->aArgs['orderBy'] = $aRawArgs['orderBy'];
    }
    if (isset($aRawArgs['order'])) {
      $this->aArgs['order'] = $aRawArgs['order'];
    }
    if (isset($aRawArgs['listPostType'])) {
      $this->aArgs['post_type'] = $aRawArgs['listPostType'];
    }

    if (isset($aRawArgs['limitProducts'])) {
      $this->aArgs['limitProducts'] = $aRawArgs['limitProducts'];
    }
    return $this;
  }

  private function defineArgs(): array
  {
    return [
      'posts_per_page' => 20,
      'paged'          => 1,
      'orderby'        => 'ID',
      'order'          => 'DESC',
      'status'         => 'publish',
      'post_status'    => 'publish'
    ];
  }

  public function handleSizeImageThumbnail($featuredImageId): array
  {
    $aImageSizes = [];
    foreach ($this->aDefineSizeImage as $imgSizeKey => $frontendKey) {
      $aImageSize = wp_get_attachment_image_src($featuredImageId, $imgSizeKey);
      if (!empty($aImageSize)) {
        $aImageSizes[$frontendKey] = [
          'src'    => $aImageSize[0],
          'width'  => $aImageSize[1],
          'height' => $aImageSize[2]
        ];
      }
    }
    return $aImageSizes;
  }

  public function query($type = 'post')
  {
    $aItems = [];
    $aData = [];
    if (in_array($type, ['post', 'product'])) {
      if ($type == 'product') {
        $this->aArgs['post_type'] = 'product';
      }
      //var_dump($this->aArgs);die();
      $oQuery = new WP_Query($this->aArgs);
      if ($oQuery->have_posts()) {
        while ($oQuery->have_posts()) {
          $oQuery->the_post();
          $postID = $oQuery->post->ID;
          switch ($type) {
            case 'product':
              $aFeaturedImage = [];
              $aGalleryImages = [];
              $aProductCat = [];
              $aDataProductCat = get_the_terms($postID, 'product_cat');
              $featuredImageId = get_post_meta($postID, '_thumbnail_id', true);
              $aGalleryImageId = !empty($galleryImageId = get_post_meta($postID,
                '_product_image_gallery',
                true))
                ? explode(',', $galleryImageId) : [];

              if (isset($featuredImageId)) {
                $aFeaturedImage
                  = $this->handleSizeImageThumbnail($featuredImageId);
              }

              if (!empty($aDataProductCat)) {
                foreach ($aDataProductCat as $aItem) {
                  $aProductCat[] = [
                    'id'    => $aItem->term_id,
                    'name'  => $aItem->name,
                    'slug'  => $aItem->slug,
                    'count' => $aItem->count,
                    'link'  => home_url('/product-category/' . $aItem->slug),
                  ];
                }
              }
              $oProduct = wc_get_product($postID);
              $ratingCount = $oProduct->get_rating_count() ?? 0;
              $reviewCount = $oProduct->get_review_count() ?? 0;
              $average = $oProduct->get_average_rating() ?? '0';

              if (isset($aGalleryImageId) && !empty($aGalleryImageId)) {
                foreach ($aGalleryImageId as $id) {
                  $aGalleryImages[] = $this->handleSizeImageThumbnail($id);
                }
              }

              $aHTMLPrice = [
                wc_price((float)get_post_meta($postID, $this->regularPriceKey,
                  true)),
                wc_price((float)get_post_meta($postID, $this->salePriceKey,
                  true)),
              ];

              $aAmountPrice = [
                (float)get_post_meta($postID, $this->regularPriceKey, true),
                (float)get_post_meta($postID, $this->salePriceKey, true),
              ];

              $inWishList = false;
              if (function_exists('YITH_WCWL')) {
                $inWishList = YITH_WCWL()->is_product_in_wishlist($postID);
              }
              $aItems[] = [
                'id'            => $postID,
                'title'         => $oQuery->post->post_title,
                'slug'          => $oQuery->post->post_name,
                'link'          => get_permalink($postID),
                'content'       => get_the_content($postID),
                'createDate'    => $oQuery->post->post_date,
                'categories'    => $aProductCat,
                'featuredImage' => $aFeaturedImage,
                'galleryImages' => $aGalleryImages,
                'outOfStock'    => ProductMetaWoocommerce::isProductOutOfStock($postID),
                'onSale'        => ProductMetaWoocommerce::isProductOnSale($postID),
                'sku'           => ProductMetaWoocommerce::getProductSKU($postID),
                'price'         => $aHTMLPrice,
                'amountPrice'   => $aAmountPrice,
                'inWishList'    => $inWishList,
                'reviews'       => [
                  'ratingCount' => $ratingCount,
                  'reviewCount' => $reviewCount,
                  'average'     => (float)$average,
                ]
              ];
              break;
            default:
              $aComments = [];
              $aCategories = [];
              $aPostComments = get_comments([
                'number'  => 10,
                'status'  => 'approve',
                'type'    => 'comment',
                'post_id' => $postID
              ]);
              $aRawCategories = get_the_category($postID);
              if (!empty($aPostComments)) {
                foreach ($aPostComments as $aPostComment) {
                  $aComments[] = [
                    'id'           => $aPostComment->comment_ID,
                    'content'      => $aPostComment->comment_content,
                    'createDate'   => $aPostComment->comment_date,
                    'modifiedDate' => $aPostComment->comment_date_gmt,
                    'authorName'   => $aPostComment->comment_author,
                    'authorIP'     => $aPostComment->comment_author_IP,
                    'authorUrl'    => $aPostComment->comment_author_url,
                    'authorEmail'  => $aPostComment->comment_author_email
                  ];
                }
              }
              if (!empty($aRawCategories)) {
                foreach ($aRawCategories as $aCategory) {
                  $aCategories[] = [
                    'id'    => $aCategory->term_id,
                    'name'  => $aCategory->name,
                    'count' => $aCategory->count,
                    'slug'  => $aCategory->slug,
                    'link'  => home_url('/category/' . $aCategory->slug),
                  ];
                }
              }
              $oUser = new WP_User($oQuery->post->post_author);
              $thumbnail_id = get_post_meta($postID, '_thumbnail_id', true);
              $aItems[] = [
                'id'           => (string)$postID,
                'content'      => get_the_content($postID),
                'title'        => get_the_title($postID),
                'slug'         => $oQuery->post->post_name,
                'excerpt'      => $oQuery->post->post_excerpt,
                'createDate'   => $oQuery->post->post_date,
                'modifiedDate' => $oQuery->post->post_modified,
                'link'         => $oQuery->post->guid,
                'comments'     => $aComments,
                'categories'   => $aCategories,
                'author'       => [
                  'id'             => $oUser->ID,
                  'authorName'     => $oUser->display_name,
                  'authorEmail'    => $oUser->user_email,
                  'authorNiceName' => $oUser->user_nicename,
                  'authorUrl'      => $oUser->user_url,
                  'roles'          => $oUser->roles,
                  'avatar'         => esc_url(get_avatar_url($oUser->ID)),
                ],
                'status'       => get_post_status($postID),
                'image'        => $this->handleSizeImageThumbnail($thumbnail_id)
              ];
              break;
          }
        }
      }
      $aData = [
        'items'    => $aItems,
        'maxPages' => $oQuery->max_num_pages,
        'limit'    => $this->aArgs['posts_per_page'] ?? 1,
      ];
      wp_reset_postdata();
    } else {
      $method = 'handleQuery' . ucfirst($type);
      if (method_exists($this, $method)) {
        return call_user_func_array([$this, $method], [$this->aArgs]);
      }
    }
    return $aData;
  }

  public function handleConvertPostTypeToTaxonomies(array $aPostTypes)
  {
    $aTaxonomies = [];
    foreach ($aPostTypes as $aPostType) {
      $aTaxonomies = array_merge(get_object_taxonomies($aPostType['id']),
        $aTaxonomies);
    }
    return count($aTaxonomies) > 1 ? $aTaxonomies : implode(',', $aTaxonomies);
  }

  public function handleQueryCatPosts($aArgs): array
  {
    $aData = [];
    $aDataPosts = [];
    if (isset($aArgs['posts_per_page'])) {
      $aArgs['number'] = $aArgs['posts_per_page'];
    }
    if (isset($aArgs['post_type']) && !empty($aArgs['post_type'])) {
      $aArgs['taxonomy']
        = $this->handleConvertPostTypeToTaxonomies($aArgs['post_type']);
      unset($aArgs['post_type']);
    } else {
      $aArgs['taxonomy'] = 'category';
    }
    $aCategories = get_terms($aArgs);
    $aArgs['number'] = 0;
    $aAllCategories = get_terms($aArgs);
    if (!empty($aCategories)) {
      foreach ($aCategories as $oCategory) {
        $oQuery = new WP_Query([
          'posts_per_page' => (int)$aArgs['limitPosts'],
          'tax_query'      => [
            [
              'taxonomy' => $oCategory->taxonomy,
              'field'    => 'term_id',
              'terms'    => $oCategory->term_id
            ]
          ],
          'orderby'        => $aArgs['orderBy'],
          'order'          => $aArgs['order'],
        ]);
        if (!empty($aPosts = $oQuery->get_posts())) {
          $aDataPosts = [];
          foreach ($aPosts as $aRawPost) {
            $featuredImageId = get_post_meta($aRawPost->ID, '_thumbnail_id',
              true);
            $aDataPosts[] = [
              'image' => $this->handleSizeImageThumbnail($featuredImageId),
              'id'    => $aRawPost->ID,
              'title' => $aRawPost->post_title,
            ];
          }
        }

        $aData[] = [
          'id'         => $oCategory->term_id,
          'label'      => $oCategory->name,
          'totalPosts' => $oCategory->count,
          'posts'      => $aDataPosts
        ];
      }
    }
    return [
      'items'    => $aData,
      'maxPages' => (!empty(count($aAllCategories)) &&
        !empty(count($aCategories))) ? ceil(count
        ($aAllCategories) / count($aCategories)) : 1,
      'limit'    => $aArgs['posts_per_page'] ?? 1,
    ];
  }

  public function handleQueryCatProducts($aArgs): array
  {
    $aData = [];
    $aDataPosts = [];
    if (isset($aArgs['posts_per_page'])) {
      $aArgs['number'] = $aArgs['posts_per_page'];
    }
    $aArgs['taxonomy'] = 'product_cat';
    $aCategories = get_terms($aArgs);
    $aArgs['number'] = 0;
    $aAllCategories = get_terms($aArgs);
    if (!empty($aCategories)) {
      foreach ($aCategories as $oCategory) {
        $oQuery = new WP_Query([
          'posts_per_page' => (int)$aArgs['limitProducts'],
          'tax_query'      => [
            [
              'taxonomy' => $oCategory->taxonomy,
              'field'    => 'term_id',
              'terms'    => $oCategory->term_id
            ]
          ],
          'orderby'        => $aArgs['orderBy'],
          'order'          => $aArgs['order'],
        ]);
        if (!empty($aPosts = $oQuery->get_posts())) {
          $aDataPosts = [];
          foreach ($aPosts as $aRawPost) {
            $featuredImageId = get_post_meta($aRawPost->ID, '_thumbnail_id',
              true);
            $aDataPosts[] = [
              'image' => $this->handleSizeImageThumbnail($featuredImageId),
              'id'    => $aRawPost->ID,
              'title' => $aRawPost->post_title,
            ];
          }
        }

        $aData[] = [
          'id'            => $oCategory->term_id,
          'label'         => $oCategory->name,
          'totalProducts' => $oCategory->count,
          'products'      => $aDataPosts
        ];
      }
    }
    return [
      'items'    => $aData,
      'maxPages' => (!empty(count($aAllCategories)) &&
        !empty(count($aCategories))) ? ceil(count
        ($aAllCategories) / count($aCategories)) : 1,
      'limit'    => $aArgs['posts_per_page'] ?? 1,
    ];
  }

}