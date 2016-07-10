<?php

namespace Timegridio\Concierge\Calendar;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property Illuminate\Database\Eloquent\Relations\HasMany $vacancies
 * @property Timegridio\Concierge\Models\Service $service
 * @property int $duration
 * @property string $date
 * @property string $time
 * @property string $timezone
 */
abstract class BaseCalendar
{
    protected $vacancies;

    protected $service = null;

    protected $duration = null;

    protected $date = null;

    protected $time = null;

    protected $timezone = 'UTC';

    /**
     * @param Illuminate\Database\Eloquent\Relations\HasMany $vacancies
     * @param string $timezone
     */
    public function __construct(HasMany $vacancies, $timezone = 'UTC')
    {
        $this->vacancies = $vacancies;

        $this->timezone($timezone);
    }

    public function timezone($timezone = null)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function forService($service = null)
    {
        $this->service = $service;

        return $this;
    }

    public function forDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function atTime($time, $timezone = null)
    {
        $this->time = $time;

        if ($timezone !== null) {
            $this->timezone = $timezone;
        }

        return $this;
    }

    public function withDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getUTCDateTime()
    {
        return Carbon::parse("{$this->date} {$this->time} {$this->timezone}")->timezone('UTC');
    }

    final protected function date()
    {
        return Carbon::parse($this->date);
    }

    abstract public function find();

    abstract protected function prepare();
}
