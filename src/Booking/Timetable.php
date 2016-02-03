<?php

namespace Timegridio\Concierge\Booking;

use Illuminate\Support\Arr;

class Timetable
{
    /**
     * Timetable matrix.
     *
     * @var array
     */
    protected $timetable = [];

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
    protected $future = 10;

    /**
     * Dimensions format of the timetable matrix.
     *
     * @var string
     */
    protected $dimensions = ':date:.:service:.:time:';

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

        $this->autoInit();

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

        $this->autoInit();

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

        $from = $this->from;
        $future = $this->future;

        return $this;
    }

    /**
     * Auto Initialize Timetable if ready.
     *
     * @return void
     */
    protected function autoInit()
    {
        if ($this->from !== null && $this->future !== null) {
            $this->init();
        }
    }

    /**
     * Get the Timetable matrix.
     *
     * @return array
     */
    public function get()
    {
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

        $keys = array_keys($segments);

        $keys = array_map(function ($value) { return ":$value:"; }, $keys);

        $values = array_values($segments);

        $translatedDimensions = str_replace($keys, $values, $this->dimensions);

        return $translatedDimensions;
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
        $this->dimensions = $dimensions;

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
}
