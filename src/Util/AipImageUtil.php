<?php

namespace Shengfai\BaiduAi\Util;

/**
 * Image Util class
 */
class AipImageUtil
{

    /**
     * 获取图片信息
     *
     * @param string $content
     * @return array
     */
    public static function getImageInfo(string $content): array
    {
        $info = getimagesizefromstring($content);

        return array(
            'mime' => $info['mime'],
            'width' => $info[0],
            'height' => $info[1],
        );
    }
}
