<?php

namespace Timegridio\Concierge\Calendar;

class VacancyCalendar extends Calendar
{
    protected $vacancies = null;

    protected $find = null;

    /////////////////////
    // Vacancy Queries //
    /////////////////////

    public function forServiceAndDateTime($service, $datetime = null, $timezone = null)
    {
        $this->find = $this->filtered()->forDateTime($datetime)->forService($service->id);

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

    public function forService($service)
    {
        if ($service === null && $this->service !== null) {
            $service = $this->service;
        }

        $this->find = $this->filtered()->forService($service->id);

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
