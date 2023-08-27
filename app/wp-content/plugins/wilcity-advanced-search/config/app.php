<?php

use WilokeListingTools\Framework\Helpers\TermSetting;

$aTaxonomies = TermSetting::getListingTaxonomies();
//var_export($aTaxonomies);die;
$aSearchResultsOptions = [
  'geocoder' => 'Geocoder',
  'listing'  => 'Default Search'
];

foreach ($aTaxonomies as $taxonomy => $aTaxonomy) {
    if (isset($aTaxonomy['label'])) {
        $aSearchResultsOptions[$taxonomy] = $aTaxonomy['label'];
    } else {
        $aSearchResultsOptions[$taxonomy] = $aTaxonomy['labels']['singular_name'];
    }
}

return [
  'themeoptions' => [
    [
      'title'            => 'Advanced Search settings',
      'id'               => 'complex_search_target',
      'subsection'       => true,
      'icon'             => 'dashicons dashicons-admin-generic',
      'customizer_width' => '500px',
      'fields'           => [
          [
            'id'          => 'complex_search_target',
            'type'        => 'sorter',
            'title'       => 'Complex and Top Search Target',
            'description' => '<div><p><i>Specifying type of results and its order when searching a keyword on Top Search Field at Search V2 page and Complex Search Field on Hero Search Form.</i></p><p><i>Default Search: Searching by Listing Title, Listing Content, Website address, etc...</i></p></div>',
            'options'     => [
              'enabled'  => $aSearchResultsOptions,
              'disabled' => []
            ]
          ],
          [
            'id'          => 'number_of_listings',
            'type'        => 'text',
            'title'       => 'Number of listings',
            'description' => 'Maximum listings will be returned',
            'default'     => 6
          ],
          [
            'id'          => 'number_of_taxonomies',
            'type'        => 'text',
            'title'       => 'Number of terms',
            'description' => 'Maximum terms will be returned (Listing Location / Category / Tag is term)',
            'default'     => 5
          ],
          [
            'id'      => 'number_of_geocoder',
            'type'    => 'text',
            'title'   => 'Number of geocoder',
            'default' => 2
          ],
          [
            'id'          => 'default_search_search_by',
            'type'        => 'sorter',
            'title'       => 'Default Search Search By',
            'description' => '<strong>Default Search</strong> / <strong>wp_search (Hero Search Form Item)</strong> is the default Search Feature of WordPress. As the default, It will search Listing by Title and Content, You can now expand its query.',
            'options'     => [
              'enabled'  => [
                'post_title'      => 'Title',
                'post_content'    => 'Content',
                'wilcity_phone'   => 'Phone Number',
                'wilcity_website' => 'Website address',
                'wilcity_email'   => 'Email address',
                'wilcity_tagline' => 'Tagline',
                'listing_tags'    => 'Listing Tags'
              ],
              'disabled' => []
            ]
          ]
        ]
    ]
  ]
];
