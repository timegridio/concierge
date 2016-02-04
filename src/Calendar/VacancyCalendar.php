<?php

namespace Timegridio\Concierge\Calendar;

class VacancyCalendar extends Calendar
{
    protected $vacancies = null;

    protected $find = null;

    /////////////////////
    // Vacancy Queries //
    /////////////////////

    public function forServiceAndDateTime($serviceId, $datetime = null, $timezone = null)
    {
        $this->find = $this->filtered()->forDateTime($datetime)->forService($serviceId);

        return $this;
    }

    public function forDateTime($datetime = null, $timezone = null)
    {
        if ($datetime === null && $this->datetime !== null) {
            $datetime = $this->datetime;
        }

        $this->find = $this->filtered()->forDateTime($datetime);

        return $this;
    }

    public function forService($serviceId)
    {
        if ($serviceId === null && $this->service !== null) {
            $serviceId = $this->service;
        }

        $this->find = $this->filtered()->forService($serviceId);

        return $this;
    }

    public function find()
    {
        return $this->filtered()->first();
    }

    public function filtered()
    {
        if ($this->find === null) {
            $this->find = $this->business->vacancies();
        }

        return $this->find;
    }
}
