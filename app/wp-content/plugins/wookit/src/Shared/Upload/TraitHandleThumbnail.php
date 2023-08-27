<?php

namespace WooKit\Shared\Upload;

trait TraitHandleThumbnail
{
    public array $aDefaultSizeIMG
        = [
            '5x5',
            'thumbnail',
            'medium',
            'large',
        ];

    public function getThumbnailDefault($attachmentId): array
    {
        $aThumbnails = [];
        $fullSizePath = get_attached_file($attachmentId);
        $baseDir = str_replace(basename($fullSizePath), '', get_the_guid($attachmentId));
        $aDataIMG = wp_get_attachment_metadata($attachmentId);
        $aThumbnails['full'] = [
            'id'     => $attachmentId,
            'url'    => get_the_guid($attachmentId),
            'width'  => $aDataIMG['width'],
            'height' => $aDataIMG['height'],
        ];
        foreach ($this->aDefaultSizeIMG as $key) {
            if (array_key_exists($key, $aDataIMG['sizes'])) {
                $aItem = $aDataIMG['sizes'][$key];
                $aThumbnails[$key] = [
                    'id'     => $attachmentId,
                    'url'    => $baseDir . $aItem['file'],
                    'width'  => $aItem['width'],
                    'height' => $aItem['height'],
                ];
            } else {
                $aThumbnails[$key] = $aThumbnails['full'];
            }
        }
        return $aThumbnails;
    }
}