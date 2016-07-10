<?php

namespace Timegridio\Concierge;

/**
 * This class is an adapted version of DateInterval, original work by Mochamad Gufron.
 * 
 * A simple class to convert time duration to a human readable string
 *
 * @class Duration
 *
 * @author Mochamad Gufron
 *
 * @link mgufron.com
 * @contact mgufronefendi@gmail.com
 */
class Duration
{
    /**
     * @var int timestamp interval
     */
    public $interval = 0;

    /**
     * @var int save interval when using format mode
     */
    private $tempInterval = 0;

    /**
     * @var string used format for forming the output
     */
    public $format = '';

    /**
     * @param int $interval timestamp interval
     * @param \DateTime $interval the first date
     */
    public function __construct($interval = 0)
    {
        if ($interval instanceof \DateTime) {
            $first_interval = strtotime($interval->format('Y-m-d H:i:s'));
            $last_interval = strtotime($interval->format('Y-m-d H:i:s'));
            $interval = $last_interval - $first_interval;
        }
        $this->interval = $interval;
    }

    /**
     * @param int $interval set current time interval
     *
     * @return self object
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @param string $format set current format
     *
     * @return self object
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param int $round_method rounding method after we reveal the readable 
     * interval. You can use PHP_ROUND_HALF_UP and PHP_ROUND_HALF_DOWN
     *
     * @return float in seconds
     */
    public function getSeconds($round_method = null)
    {
        $result = $this->interval / 1000;

        return $this->round($result, $round_method);
    }

    /**
     * @param int $round_method rounding method after we reveal the readable 
     * interval. You can use PHP_ROUND_HALF_UP and PHP_ROUND_HALF_DOWN
     *
     * @return float in minutes
     */
    public function getMinutes($round_method = null)
    {
        $result = $this->interval / (1000 * 60);

        return $this->round($result, $round_method);
    }

    /**
     * @param int $round_method rounding method after we reveal the readable 
     * interval. You can use PHP_ROUND_HALF_UP and PHP_ROUND_HALF_DOWN
     *
     * @return float in hours
     */
    public function getHours($round_method = null)
    {
        $result = $this->interval / (1000 * 60 * 60);

        return $this->round($result, $round_method);
    }

    /**
     * @param string $format rounding method after we reveal the readable 
     * interval. You can use PHP_ROUND_HALF_UP and PHP_ROUND_HALF_DOWN
     *
     * @return well formatted output
     */
    public function format($format = [])
    {
        $this->tempInterval = $this->interval;
        $hours = $this->getHours(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval % (1000 * 60 * 60);
        $minutes = $this->getMinutes(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval % (1000 * 60);
        $seconds = $this->getSeconds(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval;
        if (is_string($format)) {
            $result = strtr($format, [
            '{hours}'  => $hours,
            '{minutes}' => $minutes,
            '{seconds}' => $seconds,
        ]);
        } else {
            if ($seconds <= 0) {
                $format['{seconds}'] = '';
            }
            if ($minutes <= 0) {
                $format['{minutes}'] = '';
            }
            if ($hours <= 0) {
                $format['{hours}'] = '';
            }
            $format['{seconds}'] = strtr($format['{seconds}'], ['{seconds}' => $seconds]);
            $format['{minutes}'] = strtr($format['{minutes}'], ['{minutes}' => $minutes]);
            $format['{hours}'] = strtr($format['{hours}'], ['{hours}' => $hours]);
            $result = trim(strtr($format['template'], $format));
        }

        $result = $this->fixSingulars($result);

        return $result;
    }

    /**
     * @param int $round_method rounding method after we reveal the readable 
     * interval. You can use PHP_ROUND_HALF_UP and PHP_ROUND_HALF_DOWN
     *
     * @return float current number of the result. if it is using $round_method,
     * it will rounded up or down
     */
    private function round($result, $round_method = null)
    {
        if ($round_method === PHP_ROUND_HALF_UP) {
            $result = ceil($result);
        } elseif ($round_method === PHP_ROUND_HALF_DOWN) {
            $result = floor($result);
        }

        return $result;
    }

    /**
     * @return well formatted output
     */
    public function __toString()
    {
        return $this->format($this->format);
    }

    /**
     * Fix singulars of time units
     * 
     * @param  string $string
     * @return string  Formatted string with replaced plurals to singular
     */
    protected function fixSingulars($string)
    {
        $plurals = ['/^1 hours/', '/^1 minutes/', '/^1 seconds/'];
        $singulars = ['1 hour', '1 minute', '1 second'];

        return preg_replace($plurals, $singulars, $string);
    }
}
