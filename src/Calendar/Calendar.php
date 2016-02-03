<?php

namespace Timegridio\Concierge\Calendar;

class Calendar
{
    protected $business;

    protected $datetime = null;

    protected $timezone = null;

    protected $vacancies = null;

    protected $find = null;

    public function business($business)
    {
        $this->business = $business;

        return $this;
    }

    public function timezone($timezone = null)
    {
        if ($timezone == null) {
            return $this->timezone = $this->business->timezone;
        }

        return $this->timezone = $timezone;
    }
}
