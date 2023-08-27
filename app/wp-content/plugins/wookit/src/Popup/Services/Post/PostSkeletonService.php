<?php

namespace WooKit\Popup\Services\Post;

use WooKit\Insight\Clicks\Models\ClickStatisticModel;
use WooKit\Insight\Subscribers\Models\SubscriberStatisticModel;
use WooKit\Insight\Views\Models\ViewStatisticModel;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Post\Query\PostSkeleton;
use WooKit\Shared\Post\TraitGetListMailService;
use WooKit\Shared\Post\TraitHandleConversion;

class PostSkeletonService extends PostSkeleton
{
    use TraitHandleConversion, TraitGetListMailService;

    public function getConversion(): int
    {
        return $this->handlerConversion($this->getGoal(), [
            'getViews'       => $this->getViews(),
            'getClicks'      => $this->getClicks(),
            'getSubscribers' => $this->getSubscribers()
        ]);
    }

    public function getGoal(): string
    {
        $aConfig = $this->getConfig();

        return $aConfig['goal'] ?? '';
    }

    public function getViews(): int
    {
        $postID = (int)$this->oPost->ID;
        return ViewStatisticModel::getViewsWithPostID($postID);
    }

    public function getClicks(): int
    {
        $postID = (int)$this->oPost->ID;
        return ClickStatisticModel::getClicksWithPostID($postID);
    }

    public function getSubscribers(): int
    {
        $postID = (string)$this->oPost->ID;
        return SubscriberStatisticModel::countAllWithPostID($postID);
    }

    public function getShowOnPage(): array
    {
        $postID = (int)$this->oPost->ID;
        return get_post_meta($postID, AutoPrefix::namePrefix('showOnPage')) ?: [];
    }
}
