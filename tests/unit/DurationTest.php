<?php

use Timegridio\Concierge\Duration;

class IntervalTest extends TestCaseDB
{
    public function testSeconds()
    {
        // interval is in seconds
        $interval = 915817;
        $class = new Duration($interval);
        $seconds = $class->getSeconds();
        $this->assertEquals($interval / 1000, $seconds);
    }

    public function testMinutes()
    {
        $interval = 915817;
        $class = new Duration($interval);
        $minutes = $class->getMinutes();
        $this->assertEquals(($interval / 1000) / 60, $minutes);
    }

    public function testHours()
    {
        $interval = 915817;
        $class = new Duration($interval);
        $hours = $class->getHours();
        $this->assertEquals((($interval / 1000) / 60) / 60, $hours);
    }

    public function testSecondsRound()
    {
        $interval = 915817;
        $class = new Duration($interval);
        $seconds = $class->getSeconds(PHP_ROUND_HALF_UP);
        $this->assertEquals(ceil($interval / 1000), $seconds);
        $class = new Duration($interval);
        $seconds = $class->getSeconds(PHP_ROUND_HALF_DOWN);
        $this->assertEquals(floor(($interval / 1000)), $seconds);
    }

    public function testMinutesRound()
    {
        $interval = 915817;
        $class = new Duration($interval);
        $seconds = $class->getMinutes(PHP_ROUND_HALF_UP);
        $this->assertEquals(ceil(($interval / 1000) / 60), $seconds);
        $class = new Duration($interval);
        $seconds = $class->getMinutes(PHP_ROUND_HALF_DOWN);
        $this->assertEquals(floor(($interval / 1000) / 60), $seconds);
    }

    public function testHoursRound()
    {
        $interval = 9360015817;
        $class = new Duration($interval);
        $seconds = $class->getHours(PHP_ROUND_HALF_UP);
        $this->assertEquals(ceil((($interval / 1000) / 60) / 60), $seconds);
        $class = new Duration($interval);
        $seconds = $class->getHours(PHP_ROUND_HALF_DOWN);
        $this->assertEquals(floor((($interval / 1000) / 60) / 60), $seconds);
    }

    public function testFormatting()
    {
        $interval = 14400;
        $class = new Duration($interval);
        $format = '{hours} hours {minutes} minutes {seconds} seconds';
        $actual = $class->format($format);
        $hours = floor($interval / (1000 * 60 * 60));
        $left = $interval % (1000 * 60 * 60);
        $minutes = floor($left / (1000 * 60));
        $left = $interval % (1000 * 60);
        $seconds = floor($left / (1000));
        $result = strtr($format, [
            '{hours}'  => $hours,
            '{minutes}' => $minutes,
            '{seconds}' => $seconds,
        ]);
        $this->assertEquals($result, $actual);
    }

    public function testFormattingWithArrayOptions()
    {
        $interval = 14400 * 1000;
        $class = new Duration($interval);
        $format = [
            'template' => '{hours} {minutes} {seconds}',
            '{hours}'  => '{hours} hours',
            '{minutes}' => '{minutes} minutes',
            '{seconds}' => '{seconds} seconds',
        ];
        $actual = $class->format($format);
        $hours = floor($interval / (1000 * 60 * 60));
        $left = $interval % (1000 * 60 * 60);
        $minutes = floor($left / (1000 * 60));
        $left = $interval % (1000 * 60);
        $seconds = floor($left / (1000));
        $result = '4 hours';
        $this->assertEquals($result, $actual);
    }
}
