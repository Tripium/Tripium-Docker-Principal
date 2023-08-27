<?php


namespace WooKit\Shared\Post\Query;


use WooKit\Shared\AutoPrefix;

trait TraitShowOnPage
{
    private function handleShowOnPage($showOnPage): array
    {
        $aMetaQuery = [];
        if (isset($showOnPage) && !empty($showOnPage)) {
            if ($showOnPage == 'all') {
                $aMetaQuery = [
                    [
                        'key'     => AutoPrefix::namePrefix('showOnPageMode'),
                        'value'   => 'all',
                        'compare' => '='
                    ]
                ];
            } else {
                $aShowOnPage = is_array($showOnPage) ?$showOnPage: explode(',', $showOnPage);
                $aValueShowOnPage = array_map(function ($value) {
                    return trim($value);
                }, $aShowOnPage);
                $aMetaQuery = [
                    'relation' => 'AND',
                    [
                        'key'     => AutoPrefix::namePrefix('showOnPageMode'),
                        'value'   => 'specified_pages',
                        'compare' => '='
                    ],
                    [
                        'key'     => AutoPrefix::namePrefix('showOnPage'),
                        'value'   => $aValueShowOnPage,
                        'compare' => 'IN'
                    ]
                ];
            }
        }
        return $aMetaQuery;
    }
}
