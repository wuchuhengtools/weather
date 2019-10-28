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
        $w->getWeather('深圳', 'base', 'array');
        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }
}
