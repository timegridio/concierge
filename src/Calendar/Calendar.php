<?php

namespace Timegridio\Concierge\Calendar;

class Calendar
{
    protected $strategy;

    public function __construct($strategyName, $vacancies, $timezone = null)
    {
        switch ($strategyName) {
            case 'timeslot':
                $this->strategy = new TimeslotCalendar($vacancies, $timezone);
                break;
            case 'dateslot':
                $this->strategy = new DateslotCalendar($vacancies, $timezone);
                break;
            default:
                // Throw exception
                break;
        }
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->strategy, $name))
        {
            return $this->strategy->$name($arguments);
        }

        // Throw exception
        return false;
    }
}
