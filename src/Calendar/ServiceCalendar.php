<?php

namespace Timegridio\Concierge\Calendar;

class ServiceCalendar extends Calendar
{
    private $service = null;

    public function forService($serviceId)
    {
        if ($serviceId === null && $this->service !== null) {
            $serviceId = $this->service;
        }

        return $this;
    }
}
