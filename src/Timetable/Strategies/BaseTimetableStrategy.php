<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;

abstract class BaseTimetableStrategy
{
    protected $timetable;

    abstract protected function initTimetable($starting, $days);
}
