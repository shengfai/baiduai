<?php

namespace Shengfai\BaiduAi\Client;

use Shengfai\BaiduAi\Util\AipBase;

/**
 * 黄反识别
 */
class AipImageCensor extends AipBase
{

    /**
     * antiporn api url
     *
     * @var string
     */
    private $antiPornUrl = 'https://aip.baidubce.com/rest/2.0/antiporn/v1/detect';

    /**
     * antiporn gif api url
     *
     * @var string
     */
    private $antiPornGifUrl = 'https://aip.baidubce.com/rest/2.0/antiporn/v1/detect_gif';

    /**
     * antiterror api url
     *
     * @var string
     */
    private $antiTerrorUrl = 'https://aip.baidubce.com/rest/2.0/antiterror/v1/detect';

    /**
     * @var string
     */
    private $faceAuditUrl = 'https://aip.baidubce.com/rest/2.0/solution/v1/face_audit';

    /**
     * @var string
     */
    private $imageCensorCombUrl = 'https://aip.baidubce.com/api/v1/solution/direct/img_censor';

    /**
     * @var string
     */
    private $imageCensorUserDefinedUrl = 'https://aip.baidubce.com/rest/2.0/solution/v1/img_censor/v2/user_defined';

    /**
     * @var string
     */
    private $antiSpamUrl = 'https://aip.baidubce.com/rest/2.0/antispam/v2/spam';

    /**
     * @var string
     */
    private $textCensorUserDefinedUrl = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined';

    /**
     * @param  string $image 图像读取
     * @return array
     */
    public function antiPorn(string $image): array
    {
        $data = [];
        $data['image'] = base64_encode($image);

        return $this->request($this->antiPornUrl, $data);
    }

    /**
     * @param  string $image 图像读取
     * @return array
     */
    public function multi_antiporn(array $images): array
    {
        $data = [];
        foreach ($images as $image) {
            $data[] = [
                'image' => base64_encode($image),
            ];
        }

        return $this->multi_request($this->antiPornUrl, $data);
    }

    /**
     * @param  string $image 图像读取
     * @return array
     */
    public function antiPornGif(string $image): array
    {
        $data = [];
        $data['image'] = base64_encode($image);

        return $this->request($this->antiPornGifUrl, $data);
    }

    /**
     * @param  string $image 图像读取
     * @return array
     */
    public function antiTerror(string $image): array
    {
        $data = [];
        $data['image'] = base64_encode($image);

        return $this->request($this->antiTerrorUrl, $data);
    }

    /**
     * @param mixed $images 图像读取
     * @param string $configId
     * @return array
     */
    public function faceAudit($images, string $configId = ''): array
    {
        // 非数组则处理为数组
        if (!is_array($images)) {
            $images = [
                $images,
            ];
        }

        $data = [
            'configId' => $configId,
        ];

        $isUrl = substr(trim($images[0]), 0, 4) === 'http';
        if (!$isUrl) {
            $arr = [];
            foreach ($images as $image) {
                $arr[] = base64_encode($image);
            }

            $data['images'] = implode(',', $arr);
        } else {
            $urls = [];
            foreach ($images as $url) {
                $urls[] = urlencode($url);
            }

            $data['imgUrls'] = implode(',', $urls);
        }

        return $this->request($this->faceAuditUrl, $data);
    }

    /**
     * @param  string $image 图像读取
     * @param string $scenes
     * @param array $options
     * @return array
     */
    public function imageCensorComb(string $image, string $scenes = 'antiporn', array $options = []): array
    {

        $scenes = !is_array($scenes) ? explode(',', $scenes) : $scenes;

        $data = [
            'scenes' => $scenes,
        ];

        $isUrl = substr(trim($image), 0, 4) === 'http';
        if (!$isUrl) {
            $data['image'] = base64_encode($image);
        } else {
            $data['imgUrl'] = $image;
        }

        $data = array_merge($data, $options);

        return $this->request($this->imageCensorCombUrl, json_encode($data), [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * @param  string $image 图像
     * @return array
     */
    public function imageCensorUserDefined(string $image): array
    {
        $data = [];

        $isUrl = substr(trim($image), 0, 4) === 'http';
        if (!$isUrl) {
            $data['image'] = base64_encode($image);
        } else {
            $data['imgUrl'] = $image;
        }

        return $this->request($this->imageCensorUserDefinedUrl, $data);
    }

    /**
     * @param  string $text
     * @return array
     */
    public function textCensorUserDefined(string $text): array
    {
        $data = [];
        $data['text'] = $text;

        return $this->request($this->textCensorUserDefinedUrl, $data);
    }

    /**
     * @param  string $content
     * @return array
     */
    public function antiSpam(string $content, array $options = []): array
    {
        $data = [];
        $data['content'] = $content;
        $data = array_merge($data, $options);

        return $this->request($this->antiSpamUrl, $data);
    }
}
