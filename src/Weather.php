<?php
/**
 * @author wuchuheng
 * @email  root@wuchuheng
 * @date   2019-11-02
 */

namespace Wuchuheng\Weather;

use GuzzleHttp\Client;
use Wuchuheng\Weather\Exceptions\InvalidArgumentException;
use Wuchuheng\Weather\Exceptions\HttpException;

class Weather
{
    protected $key;

    protected $_guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function guzzleOptions(array $options)
    {
        $this->_guzzleOptions = $options;
    }

    public function getHttpClient()
    {
        return new Client($this->_guzzleOptions);
    }

    /**
     *  获取天气.
     *
     *  @city   mix    城市名 / 高德地址位置 adcode，比如：“深圳” 或者（adcode：440300）
     *
     *  @var   string 返回内容类型：base: 返回实况天气 / all: 返回预报天气；
     *  @format string 返回的数据格式json|xml
     */
    public function getWeather($city, string $type = 'live', string $format = 'json')
    {
        $types = [
            'live' => 'base',
            'forcast' => 'all',
        ];
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';
        if (!in_array(strtolower($format), ['json', 'xml'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }
        if (!in_array($type, array_keys($types))) {
            throw new InvalidArgumentException('Invalid type value(base/all) '.$type);
        }
        $query = array_filter([
           'key' => $this->key,
           'city' => $city,
           'output' => $format,
           'extensions' => $types[$type],
        ]);

        try {
            $response = $this->getHttpClient()
                ->get($url, [
                    'query' => $query,
                ])
                ->getBody()
                ->getContents();

            return 'json' === $format ? json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
