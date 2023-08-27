<?php

namespace WilcityAdvancedProducts\Controllers;

use WilokeListingTools\Framework\Helpers\App;

class ListingAdvancedProductSkeleton
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/AbstractSkeleton/pluck/advanced_simple_product_single_selection',
          [$this, 'getAdvancedProduct'],
          10,
          2
        );

        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/AbstractSkeleton/pluck/advanced_simple_product_multiple_selection',
          [$this, 'getAdvancedProduct'],
          10,
          2
        );

        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/AbstractSkeleton/pluck/my_advanced_products',
          [$this, 'getAdvancedProduct'],
          10,
          2
        );
    }

    /**
     * @param $response
     * @param $aInfo ['pluck' => $pluck, 'post' => $this->post, 'postID' => $this->postID, 'isFocus' =>
     *               $isFocus, 'atts' => $this->aAtts]
     *
     * @return mixed
     */
    public function getAdvancedProduct($response, $aInfo)
    {
        $aAtts = $aInfo['atts']['myProductAtts'] ?? [];

        return App::get('ListingAdvancedProducts')->setPostID($aInfo['postID'])->getProducts($aAtts);
    }
}
