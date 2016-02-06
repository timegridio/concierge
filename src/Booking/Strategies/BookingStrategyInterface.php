<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Timegridio\Concierge\Booking\Timetable;

interface BookingStrategyInterface
{
    public function __construct(Timetable $timetable);

    public function buildTimetable($vacancies, $starting = 'today', $days = 1);
}
