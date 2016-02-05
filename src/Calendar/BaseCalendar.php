<?php

namespace Timegridio\Concierge\Calendar;

use Carbon\Carbon;

abstract class BaseCalendar
{
    protected $vacancies = [];

    protected $service = null;

    protected $duration = null;

    protected $date = null;

    protected $time = null;

    protected $timezone = 'UTC';

    public function __construct($vacancies, $timezone = 'UTC')
    {
        $this->vacancies = $vacancies;

        $this->timezone = $timezone;
    }

    public function timezone($timezone = null)
    {
        if ($timezone === null) {
            return $this->timezone;
        }

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

//    protected function find()
//    {
//        $this->prepare();
//
//        $results = $this->vacancies->get();
//
//        $fromDatetime = $this->getUTCDateTime();
//
//        if ($this->duration !== null) {
//            $toDatetime = $fromDatetime->addMinutes($this->duration);
//
//            $results = $results->reject(function ($vacancy) use ($fromDatetime, $toDatetime) {
//                return !$vacancy->hasRoomBetween($fromDatetime, $toDatetime);
//            });
//        }
//
//        return $results;
//    }
//
//    protected function prepare()
//    {
//        if ($this->service !== null) {
//            $this->vacancies->forService($this->service);
//        }
//
//        if ($this->date !== null &&  $this->time !== null) {
//            $this->vacancies->forDateTime($this->getUTCDateTime());
//        }
//
//        return $this;
//    }
}
