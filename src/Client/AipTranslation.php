<?php

namespace Shengfai\BaiduAi\Client;

use Shengfai\BaiduAi\Util\AipBase;

/**
 * 百度翻译
 */
class AipTranslation extends AipBase
{

    /**
     * 文本翻译接口
     *
     * @var string
     */
    public $texttransUrl = 'https://aip.baidubce.com/rpc/2.0/mt/texttrans/v1';

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
    protected function proccessResult(string $content): mixed
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
     * 文本翻译
     *
     * @param string $from
     * @param string $to
     * @param string $q
     * @param array $termIds
     * @return array
     */
    public function texttrans(string $from, string $to, string $q, array $termIds = []): array
    {
        $data = [];

        $data = json_encode([
            'from' => $from,
            'to' => $to,
            'q' => $q,
            'termIds' => $termIds,
        ]);

        return $this->request($this->texttransUrl, $data, []);
    }
}
