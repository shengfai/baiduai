<?php

namespace Shengfai\BaiduAi\Util;

use Exception;

/**
 * Http Client
 */
class AipHttpClient
{

    /**
     * HttpClient
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $this->buildHeaders($headers);
        $this->connectTimeout = 60000;
        $this->socketTimeout = 60000;
        $this->conf = [];
    }

    /**
     * 连接超时
     *
     * @param integer $ms
     * @return void
     */
    public function setConnectionTimeoutInMillis(int $ms)
    {
        $this->connectTimeout = $ms;
    }

    /**
     * 响应超时
     *
     * @param integer $ms
     * @return void
     */
    public function setSocketTimeoutInMillis(int $ms)
    {
        $this->socketTimeout = $ms;
    }

    /**
     * 配置
     *
     * @param array $conf
     * @return void
     */
    public function setConf(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * 请求预处理
     *
     * @param resource $ch
     * @return void
     */
    public function prepare($ch)
    {
        foreach ($this->conf as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
    }

    /**
     * 发送Post请求
     *
     * @param  string $url
     * @param  mixed $data HTTP POST BODY
     * @param  array $param HTTP URL
     * @param  array $headers HTTP header
     * @return array
     */
    public function post(string $url, $data = [], array $params = [], array $headers = []): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        $this->prepare($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return [
            'code' => $code,
            'content' => $content,
        ];
    }

    /**
     * 发送多个Post请求
     *
     * @param  string $url
     * @param  mixed $datas HTTP POST BODY
     * @param  array $param HTTP URL
     * @param  array $headers HTTP header
     * @return array
     */
    public function multi_post(string $url, $datas = [], array $params = [], array $headers = []): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $chs = [];
        $result = [];
        $mh = curl_multi_init();
        foreach ($datas as $data) {
            $ch = curl_init();
            $chs[] = $ch;
            $this->prepare($ch);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
            curl_multi_add_handle($mh, $ch);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            usleep(100);
        } while ($running);

        foreach ($chs as $ch) {
            $content = curl_multi_getcontent($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result[] = [
                'code' => $code,
                'content' => $content,
            ];
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        return $result;
    }

    /**
     * 发送Get请求，获取数据
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return array
     */
    public function get(string $url, array $params = [], array $headers = []): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();

        $this->prepare($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return [
            'code' => $code,
            'content' => $content,
        ];
    }

    /**
     * 构造 header
     *
     * @param array $headers
     * @return array
     */
    private function buildHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $k => $v) {
            $result[] = sprintf('%s:%s', $k, $v);
        }

        return $result;
    }

    /**
     * 构造 url
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    private function buildUrl(string $url, array $params): string
    {
        if (!empty($params)) {
            $str = http_build_query($params);
            $url = $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        }

        return $url;
    }
}
