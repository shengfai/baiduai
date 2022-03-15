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
     * 格式化结果
     *
     * @param $content string
     * @return mixed
     */
    protected function proccessResult(string $content)
    {
        $obj = json_decode($content, true);

        if (!isset($obj['error_code'])) {
            return [
                'log_id' => $obj['log_id'],
                'result' => $obj['result']['trans_result']
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
        $data = [
            'from' => $from,
            'to' => $to,
            'q' => $q,
            'termIds' => implode(',', $termIds),
        ];

        return $this->request($this->texttransUrl, $data);
    }
}
