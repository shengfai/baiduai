<?php

namespace Shengfai\BaiduAi\Util;

class SampleSigner
{

    const BCE_AUTH_VERSION = "bce-auth-v1";
    const BCE_PREFIX = 'x-bce-';

    /**
     * 不指定headersToSign情况下，默认签名http头，包括：
     *    1.host
     *    2.content-length
     *    3.content-type
     *    4.content-md5
     *
     * @var array
     */
    public static $defaultHeadersToSign;

    /**
     * @return void
     */
    public static function  __init()
    {
        SampleSigner::$defaultHeadersToSign = [
            'host',
            'content-length',
            'content-type',
            'content-md5',
        ];
    }

    /**
     * 签名
     *
     * @param array $credentials
     * @param string $httpMethod
     * @param string $path
     * @param array $headers
     * @param array $params
     * @param array $options
     * @return string
     */
    public static function sign(array $credentials, string $httpMethod, string $path, array $headers, array $params, array $options = []): string
    {

        //设定签名有效时间
        if (!isset($options[AipSignOption::EXPIRATION_IN_SECONDS])) {
            //默认值1800秒
            $expirationInSeconds = AipSignOption::DEFAULT_EXPIRATION_IN_SECONDS;
        } else {
            $expirationInSeconds = $options[AipSignOption::EXPIRATION_IN_SECONDS];
        }

        //解析ak sk
        $accessKeyId = $credentials['ak'];
        $secretAccessKey = $credentials['sk'];

        //设定时间戳，注意：如果自行指定时间戳需要为UTC时间
        if (!isset($options[AipSignOption::TIMESTAMP])) {
            //默认值当前时间
            $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        } else {
            $timestamp = $options[AipSignOption::TIMESTAMP];
        }

        //生成authString
        $authString = SampleSigner::BCE_AUTH_VERSION . '/' . $accessKeyId . '/' . $timestamp . '/' . $expirationInSeconds;

        //使用sk和authString生成signKey
        $signingKey = hash_hmac('sha256', $authString, $secretAccessKey);

        //生成标准化URI
        $canonicalURI = AipHttpUtil::getCanonicalURIPath($path);

        //生成标准化QueryString
        $canonicalQueryString = AipHttpUtil::getCanonicalQueryString($params);

        //填充headersToSign，也就是指明哪些header参与签名
        $headersToSign = null;
        if (isset($options[AipSignOption::HEADERS_TO_SIGN])) {
            $headersToSign = $options[AipSignOption::HEADERS_TO_SIGN];
        }

        //生成标准化header
        $canonicalHeader = AipHttpUtil::getCanonicalHeaders(
            SampleSigner::getHeadersToSign($headers, $headersToSign)
        );

        //整理headersToSign，以';'号连接
        $signedHeaders = '';
        if ($headersToSign !== null) {
            $signedHeaders = strtolower(
                trim(implode(";", $headersToSign))
            );
        }

        //组成标准请求串
        $canonicalRequest = "$httpMethod\n$canonicalURI\n" . "$canonicalQueryString\n$canonicalHeader";

        //使用signKey和标准请求串完成签名
        $signature = hash_hmac('sha256', $canonicalRequest, $signingKey);

        //组成最终签名串
        $authorizationHeader = "$authString/$signedHeaders/$signature";

        return $authorizationHeader;
    }

    /**
     * 根据headsToSign过滤应该参与签名的header
     *
     * @param  array $headers
     * @param  array $headersToSign
     * @return array
     */
    public static function getHeadersToSign(array $headers, array $headersToSign): array
    {
        $arr = [];
        foreach ($headersToSign as $value) {
            $arr[] = strtolower(trim($value));
        }

        //value被trim后为空串的header不参与签名
        $result = [];
        foreach ($headers as $key => $value) {
            if (trim($value) !== '') {
                $key = strtolower(trim($key));
                if (in_array($key, $arr)) {
                    $result[$key] = $value;
                }
            }
        }

        //返回需要参与签名的header
        return $result;
    }

    /**
     * 检查header是不是默认参加签名的：
     * 1.是host、content-type、content-md5、content-length之一
     * 2.以x-bce开头
     *
     * @param string $header
     * @return integer
     */
    public static function isDefaultHeaderToSign(string $header): int
    {
        $header = strtolower(trim($header));

        if (in_array($header, SampleSigner::$defaultHeadersToSign)) {
            return true;
        }

        return substr_compare($header, SampleSigner::BCE_PREFIX, 0, strlen(SampleSigner::BCE_PREFIX)) == 0;
    }
}
