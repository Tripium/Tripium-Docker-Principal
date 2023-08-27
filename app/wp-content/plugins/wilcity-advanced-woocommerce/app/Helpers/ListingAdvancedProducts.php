<?php
namespace WilcityAdvancedProducts\Helpers;

use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\ListingProduct\AbstractListingProduct;
use WilokeListingTools\Framework\Helpers\ListingProduct\InterfaceListingProduct;
use WilokeListingTools\Framework\Helpers\ProductSkeleton;
use WilokeListingTools\Framework\Helpers\WPML;

class ListingAdvancedProducts extends AbstractListingProduct implements InterfaceListingProduct
{
    public function getProductMode(): string
    {
        $this->mode = GetSettings::getPostMeta($this->postID, 'my_advanced_product_mode');

        if ($this->mode === 'inherit') {
            $this->mode = \WilokeThemeOptions::getOptionDetail('advanced_woo_get_product_mode');
        }

        return empty($this->mode) ? 'specify_products' : $this->mode;
    }

    public function getMyProductCats(): array
    {
        $catIds = GetSettings::getPostMeta($this->postID, 'my_advanced_product_cats');
        if (empty($catIds) || !is_array($catIds)) {
            return [];
        }

        return array_filter($catIds, function ($catID) {
            return term_exists($catID, 'product_cat');
        });
    }

    public function getSpecifyProducts(): array
    {
        $aProducts = get_post_meta($this->postID, 'wilcity_my_advanced_products', true);

        if (empty($aProducts)) {
            return [];
        }

        return array_filter($aProducts, function ($productID) {
            return get_post_status($productID) === 'publish';
        });
    }

    public function getProducts($aAtts = []): array
    {
    	WPML::cookieCurrentLanguage();
        $aOriginalArgs = $this->buildGeneralQuery();

        $aResponse     = [
          'productType' => $this->mode
        ];

        $aPluck = $aAtts['pluck'] ?? [];
        unset($aAtts['pluck']);
        if (!empty($this->aCatIds)) {
            foreach ($this->aCatIds as $catID) {
                $aArgs                = $aOriginalArgs;
                $aArgs['tax_query'][] = [
                  'taxonomy' => 'product_cat',
                  'field'    => 'term_id',
                  'terms'    => $catID
                ];
	            $aArgs = WPML::addFilterLanguagePostArgs($aArgs);
                $query = new \WP_Query($aArgs);
                $oTerm = get_term($catID, 'product_cat');

                $aProducts = [];
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $aProducts[] = App::get('ProductSkeleton')
                                          ->setProductId($query->post->ID)
                                          ->get($aPluck, $aAtts)
                        ;
                    }
                }

                if (!empty($aProducts)) {
                    $aResponse['items'][] = [
                      'vueID'       => uniqid('vue_id'),
                      'heading'     => $oTerm->name,
                      'description' => $oTerm->description,
                      'products'    => $aProducts
                    ];
                }
            }
        } else {
	        $aOriginalArgs = WPML::addFilterLanguagePostArgs($aOriginalArgs);
            $query = new \WP_Query($aOriginalArgs);
            if ($query->have_posts()) {
                $aProducts = [];
                while ($query->have_posts()) {
                    $query->the_post();
                    $aProducts[] = App::get('ProductSkeleton')->setProductId($query->post->ID)->get($aPluck, $aAtts);
                }
                $aResponse['items'][] = [
                  'vueID'    => uniqid('vue_id'),
                  'products' => $aProducts
                ];
            }
        }

        return $aResponse;
    }
}
