<?php

namespace Shengfai\BaiduAi;

use Shengfai\BaiduAi\Util\AipBase;

/**
 * Class Factory
 *
 * @method static \Shengfai\BaiduAi\Client\AipKg            kg(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipOcr           ocr(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipNlp           nlp(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipFace          face(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipSpeech        speech(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipTranslation   translation(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipImageCensor   imageCensor(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipImageSearch   imageSearch(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipImageClassify imageClassify(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipBodyAnalysis  bodyAnalysis(array $config)
 * @method static \Shengfai\BaiduAi\Client\AipContentCensor contentCensor(array $config)
 */
class Factory
{
    /**
     * @param string $name
     * @param array $config
     */
    public static function make(string $name, array $config)
    {
        $application = '\Shengfai\BaiduAi\Client\Aip' . ucfirst($name);

        $client = new $application($config['appid'], $config['access_key'], $config['access_secret']);
        $client->setConnectionTimeoutInMillis($config['timeout']);
        $client->setSocketTimeoutInMillis($config['timeout']);

        return $client;
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
