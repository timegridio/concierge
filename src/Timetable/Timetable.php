<?php

namespace Timegridio\Concierge\Timetable;

use Illuminate\Support\Arr;

class Timetable
{
    /**
     * Timetable matrix.
     *
     * @var array
     */
    protected $timetable = null;

    /**
     * DateTime keyword for the begining of the timetable.
     *
     * @var string
     */
    protected $from = 'today';

    /**
     * Number of days to build the timetable forward.
     *
     * @var int
     */
    protected $future = 1;

    /**
     * Starting time for each day.
     *
     * @var string
     */
    protected $startAt = '09:00:00';

    /**
     * Finishing time for each day.
     *
     * @var string
     */
    protected $finishAt = '18:00:00';

    /**
     * Interval between slots in minutes.
     *
     * @var string
     */
    protected $interval = 30;

    /**
     * Services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Dimensions format of the timetable matrix.
     *
     * @var string
     */
    protected $dimensions = ['date', 'service', 'time'];

    /**
     * Setter for beginning date.
     *
     * @param  string $relative
     *
     * @return $this
     */
    public function from($relative)
    {
        $this->from = $relative;

        return $this;
    }

    /**
     * Setter for number of days forward.
     *
     * @param  int $days
     *
     * @return $this
     */
    public function future($days)
    {
        $this->future = $days;

        return $this;
    }

    /**
     * Setter for startAt time.
     *
     * @param  string $time
     *
     * @return $this
     */
    public function startAt($time)
    {
        $this->startAt = $time;

        return $this;
    }

    /**
     * Setter for finishAt time.
     *
     * @param  string $time
     *
     * @return $this
     */
    public function finishAt($time)
    {
        $this->finishAt = $time;

        return $this;
    }

    /**
     * Setter for interval.
     *
     * @param  int $minutes
     *
     * @return $this
     */
    public function interval($interval = 30)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Setter for services.
     *
     * @param  array $services
     *
     * @return $this
     */
    public function services($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Initialize Timetable.
     *
     * @return $this
     */
    public function init()
    {
        $this->timetable = [];

        $dimensions['service'] = $this->inflateServices();
        $dimensions['date'] = $this->inflateDates();
        $dimensions['time'] = $this->inflateTimes();

        foreach ($dimensions['service'] as $service) {
            foreach ($dimensions['date'] as $date) {
                foreach ($dimensions['time'] as $time) {
                    $this->capacity($date, $time, $service, 0);
                }
            }
        }

        return $this;
    }

    public function inflateServices()
    {
        return $this->services;
    }

    public function inflateDates()
    {
        $starting = $this->from;

        for ($i = 0; $i < $this->future; $i++) {
            $date = date('Y-m-d', strtotime("$starting +$i days"));
            $dates[$date] = $date;
        }

        return $dates;
    }

    public function inflateTimes()
    {
        $interval = $this->interval;

        $start = strtotime('today '.$this->startAt);
        $finish = strtotime('today '.$this->finishAt);

        $times = [];
        for ($i = $start; $i < $finish; $i += $interval * 60) {
            $time = date('H:i:s', $i);
            $times[$time] = $time;
        }

        return $times;
    }

    /**
     * Get the Timetable matrix.
     *
     * @return array
     */
    public function get()
    {
        if ($this->timetable === null) {
            $this->init();
        }

        return $this->timetable;
    }

    /**
     * Set the capacity for a slot.
     *
     * @param  string $date
     * @param  string $time
     * @param  string $service
     * @param  int $capacity
     *
     * @return int
     */
    public function capacity($date, $time, $service, $capacity = null)
    {
        $path = $this->dimensions(compact('date', 'service', 'time'));

        return $capacity === null
            ? $this->array_get($this->timetable, $path)
            : $this->array_set($this->timetable, $path, $capacity);
    }

    /**
     * Get a concrete Timetable path.
     *
     * @param  array $segments
     *
     * @return string
     */
    private function dimensions(array $segments)
    {
        $translatedDimensions = $this->dimensions;

        $this->array_substitute($translatedDimensions, $segments);

        return implode('.', $translatedDimensions);
    }

    /**
     * Setter for the dimensions format.
     *
     * @param  string $dimensions
     *
     * @return $this
     */
    public function format($dimensions)
    {
        if (is_array($dimensions)) {
            $this->dimensions = $dimensions;
        }

        if (is_string($dimensions)) {
            $this->dimensions = explode('.', $dimensions);
        }

        return $this;
    }

    /////////////
    // Helpers //
    /////////////

    /**
     * Helper method for Arr::set
     *
     * @param  array &$array
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return mixed
     */
    private function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }

    /**
     * Helper method for Arr::get
     *
     * @param  array &$array
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return mixed
     */
    private function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }

    private function array_substitute(&$array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            $array1[$key] = $array2[$value];
        }
    }
}
