<?php

namespace Shengfai\BaiduAi;

use AipBase;

/**
 * Class Factory
 *
 * @method static \AipKg            kg(array $config)
 * @method static \AipOcr           ocr(array $config)
 * @method static \AipNlp           nlp(array $config)
 * @method static \AipFace          face(array $config)
 * @method static \AipSpeech        speech(array $config)
 * @method static \AipImageCensor   imageCensor(array $config)
 * @method static \AipImageSearch   imageSearch(array $config)
 * @method static \AipImageClassify imageClassify(array $config)
 * @method static \AipBodyAnalysis  bodyAnalysis(array $config)
 * @method static \AipContentCensor contentCensor(array $config)
 */
class Factory
{
    /**
     * @param string $name
     * @param array $config
     * @return AipBase
     */
    public static function make(string $name, array $config): AipBase
    {
        $application = 'Aip' . ucfirst($name);

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
