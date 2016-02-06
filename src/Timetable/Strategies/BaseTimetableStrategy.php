<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;

class BaseTimetableStrategy
{
    protected $timetable;

    protected function initTimetable($starting, $days)
    {
        $this->timetable
             ->format('date.service.time')
             ->from($starting)
             ->future($days)
             ->init();
    }
}
