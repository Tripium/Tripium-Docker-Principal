<?php


namespace WooKit\Shared\Post;


use Exception;

trait TraitHandleConversion
{
    public function handlerConversion(string $goal, array $aData): int
    {
        $campaign='click';
        $response = 0;
        $aConfig = include WOOKIT_PATH . 'src/Shared/Configs/Conversion.php';
        foreach ($aConfig as $key => $aValue) {
            if (in_array($goal, $aValue)) {
                $campaign= $key;
                break;
            }
        }
        $method = 'getConversionWith' . ucfirst($campaign);
        if (method_exists($this, $method)) {
            $response = call_user_func_array([$this, $method], [$aData]);
        }
        return $response;
    }

    public function getConversionWithSubscriber($aData): int
    {
        if (empty($aData['getSubscribers']) || empty($aData['getViews'])) {
            $result = 0;
        } else {
            $result = round(($aData['getSubscribers'] / $aData['getViews']), 2) * 100;
        }
        return $result;
    }

    public function getConversionWithClick($aData): int
    {
        if (empty($aData['getViews']) || empty($aData['getClicks'])) {
            $result = 0;
        } else {
            $result = round(($aData['getClicks'] / $aData['getViews']), 2) * 100;
        }
        return $result;
    }
}
