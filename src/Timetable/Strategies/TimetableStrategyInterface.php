<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;

interface TimetableStrategyInterface
{
    public function __construct(Timetable $timetable);

    public function buildTimetable($vacancies, $starting = 'today', $days = 1);
}
