<?php


namespace WooKit\Popup\Controllers;


use WooKit\Popup\Services\PostMeta\TraitDefinePostMetaFields;
use WooKit\Shared\AutoPrefix;

trait TraitUpdatePostMeta
{
    use TraitDefinePostMetaFields;

    private function handleUpdatePostMeta($aParam, $postID): bool
    {
        $aPostMeta = $this->parsePostMetaData($aParam);
        if (!empty($aPostMeta)) {
            foreach ($aPostMeta as $keyPostMeta => $valuePostMeta) {
                if (array_key_exists($keyPostMeta, $this->defineConvertFieldsMeta())) {
                    if (method_exists($this, 'sanitize' . ucfirst($keyPostMeta))) {
                        $sanitizeValuePostMeta = call_user_func_array([$this, 'sanitize' . ucfirst($keyPostMeta)],
                            [$valuePostMeta]);
                        update_post_meta($postID, AutoPrefix::namePrefix($keyPostMeta), $sanitizeValuePostMeta);
                    }
                }
            }
        }
        return true;
    }

    private function sanitizeConfigs($valuePostMeta): string
    {
        return sanitize_text_field($valuePostMeta);
    }
}
