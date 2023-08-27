<?php


namespace WooKit\Shared\Post\Query;


use WooKit\Shared\AutoPrefix;
use WP_Post;

class PostSkeleton
{
    protected array   $aPluck
        = [
            'id',
            'title',
            'date',
            'config',
            'status',
            'views',
            'clicks',
            'subscribers',
            'conversion',
            'goal',
            'showOnPage',
            'listMailService'
        ];
    protected WP_Post $oPost;

    public function checkMethodExists($pluck): bool
    {
        $method = 'get' . ucfirst($pluck);

        return method_exists($this, $method);
    }

    public function getTitle(): string
    {
        return $this->oPost->post_title;
    }

    public function getStatus(): string
    {
        return ($this->oPost->post_status == 'publish') ? 'active' : 'deactive';
    }

    public function getDate():string
    {
        return (string) strtotime($this->oPost->post_date);
    }

    public function getConfig(): array
    {
        $aPostMeta = get_post_meta($this->oPost->ID, AutoPrefix::namePrefix('config'), true);

        return is_array($aPostMeta) ? $aPostMeta : [];
    }

    public function getId(): string
    {
        return (string)$this->oPost->ID;
    }

    public function setPost(WP_Post $oPost): PostSkeleton
    {
        $this->oPost = $oPost;

        return $this;
    }

    public function getPostData($pluck, array $aAdditionalInfo = []): array
    {
        $aData = [];

        if (empty($pluck)) {
            $aPluck = $this->aPluck;
        } else {
            $aPluck = $this->sanitizePluck($pluck);
        }

        foreach ($aPluck as $pluck) {
            $method = 'get' . ucfirst($pluck);
            if (method_exists($this, $method)) {
                $aData[$pluck] = call_user_func_array([$this, $method], [$aAdditionalInfo]);
            }
        }

        return $aData;
    }

    private function sanitizePluck($rawPluck): array
    {
        $aPluck = is_array($rawPluck) ? $rawPluck : explode(',', $rawPluck);

        return array_map(function ($pluck) {
            return trim($pluck);
        }, $aPluck);
    }
}
