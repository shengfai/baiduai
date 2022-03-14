<?php

namespace Shengfai\BaiduAi\Client;

use Shengfai\BaiduAi\Util\AipBase;

/**
 * 百度语音
 */
class AipSpeech extends AipBase
{

    /**
     * url
     *
     * @var string
     */
    public $asrUrl = 'http://vop.baidu.com/server_api';

    /**
     * url
     *
     * @var string
     */
    public $ttsUrl = 'http://tsn.baidu.com/text2audio';

    /**
     * 判断认证是否有权限
     *
     * @param  array   $authObj
     * @return boolean
     */
    protected function isPermission($authObj): bool
    {
        return true;
    }

    /**
     * 处理请求参数
     *
     * @param string $url
     * @param array $params
     * @param array $data
     * @param array $headers
     * @return void
     */
    protected function proccessRequest(string $url, array &$params, array &$data, array $headers): void
    {
        $token = isset($params['access_token']) ? $params['access_token'] : '';

        if (empty($data['cuid'])) {
            $data['cuid'] = md5($token);
        }

        if ($url === $this->asrUrl) {
            $data['token'] = $token;
            $data = json_encode($data);
        } else {
            $data['tok'] = $token;
        }

        unset($params['access_token']);
    }

    /**
     * 格式化结果
     *
     * @param $content string
     * @return mixed
     */
    protected function proccessResult(string $content)
    {
        $obj = json_decode($content, true);

        if ($obj === null) {
            $obj = [
                '__json_decode_error' => $content
            ];
        }

        return $obj;
    }

    /**
     * @param  string $speech
     * @param  string $format
     * @param  int $rate
     * @param  array $options
     * @return array
     */
    public function asr(string $speech, string $format, int $rate, array $options = []): array
    {
        $data = [];

        if (!empty($speech)) {
            $data['speech'] = base64_encode($speech);
            $data['len'] = strlen($speech);
        }

        $data['format'] = $format;
        $data['rate'] = $rate;
        $data['channel'] = 1;

        $data = array_merge($data, $options);

        return $this->request($this->asrUrl, $data, []);
    }

    /**
     * @param  string $text
     * @param  string $lang
     * @param  int $ctp
     * @param  array $options
     * @return array
     */
    public function synthesis(string $text, string $lang = 'zh', int $ctp = 1, array $options = []): array
    {
        $data = [];
        $data['tex'] = $text;
        $data['lan'] = $lang;
        $data['ctp'] = $ctp;

        $data = array_merge($data, $options);

        $result = $this->request($this->ttsUrl, $data, []);

        if (isset($result['__json_decode_error'])) {
            return $result['__json_decode_error'];
        }

        return $result;
    }
}
