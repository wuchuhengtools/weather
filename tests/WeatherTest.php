<?php

namespace Wuchuheng\Weather\Tests;

use Wuchuheng\Weather\Weather;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface;
use Mockery\Matcher\AnyArgs;
use Wuchuheng\Weather\Exceptions\InvalidArgumentException;
use Wuchuheng\Weather\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{
    /*
     * 测试getWeather $typ变量
     *
     */
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type value(base/all) foo');
        $w->getWeather('深圳', 'foo');
        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    /**
     * 测试getWeather $format变量
     *
     */
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response format: array');
        $w->getWeather('深圳', 'live', 'array');
        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    /**
     * 测试 getWeather
     *
     */
    public function testGetWeather()
    {
        $response = new response(200, [], '{"success": true}');
        $Client = \Mockery::mock(Client::class);
        $Client->allows()
            ->get(
                    'https://restapi.amap.com/v3/weather/weatherInfo',
                    [
                        'query' => [
                            'key'        => 'mock-key',
                            'city'       => '深圳',
                            'output'     => 'json',
                            'extensions' => 'base'
                        ]
                    ]
                )
            ->andReturn($response);
        $w = \Mockery::mock(Weather::class, ['mock-key'])
            ->makePartial();
        $w->allows()->getHttpClient()->andReturn($Client);
        // 断言json模拟请求
        $this->assertSame(['success' => true], $w->getWeather('深圳'));
        
        $Response = new Response(200, [], '<hello>content</hello>');
        $Client = \Mockery::mock(Client::class);
        $Client->allows()
            ->get(
                    'https://restapi.amap.com/v3/weather/weatherInfo',
                    [
                        'query' => [
                            'key'        => 'mock-key',
                            'city'       => '深圳',
                            'output'     => 'xml',
                            'extensions' => 'all'
                        ]
                    ]
                )
            ->andReturn($Response);
        $W = \Mockery::mock(Weather::class, ['mock-key'])
            ->makePartial();
        $W->allows()
            ->getHttpClient()
            ->andReturn($Client);
        // 断言模拟请求后的XML数据
        $this->assertSame('<hello>content</hello>', $W->getWeather('深圳', 'forcast', 'xml'));
    }

    /**
     * 测试getWeather http 请求异常
     *
     */
    public function testGetWeatherWithGuzzleRuntimeException()
    {
        // 定义一个模拟超时异常
        $Client = \Mockery::mock(Clent::class);
        $Client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));
        $W = \Mockery::mock(Weather::class, ['mock-key'])
            ->makePartial();
        $W->allows()
            ->getHttpClient()
            ->andReturn($Client);
        // 断言预期的结果
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $W->getWeather('深圳');
    }
    
    /**
     * to test the getHttpClient function of request object 
     *
     */
    public function testGetHttpClient()
    {
        $W = new Weather('mock-key');
        $Client = $W->getHttpClient();
        $this->assertInstanceOf(ClientInterface::class, $Client);
    }  

    /**
     * to check the guzleOption function and get the expection 
     *
     */
    public function testGuzzleOptions() 
    {
        $W = new Weather('mock-key');
        //断言默认参数
        $this->assertNull($W->getHttpClient()->getConfig('timeout'));
        $W->guzzleOptions(['timeout' => 5000]);
        // 断言设置的参数
        $this->assertSame(5000, $W->getHttpClient()->getConfig('timeout'));
    }
}
