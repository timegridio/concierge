<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableStrategy
{
    /**
     * Timetable Strategy.
     *
     * @var TimetableTimeslotStrategy|TimetableDateslotStrategy
     */
    protected $strategy = null;

    /**
     * Construct Timetable Strategy class.
     *
     * @param string $strategyId
     *
     * @throws  Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function __construct($strategyId)
    {
        switch ($strategyId) {
            case 'timeslot':
                $this->strategy = new TimetableTimeslotStrategy(new Timetable());
                break;
            case 'dateslot':
                $this->strategy = new TimetableDateslotStrategy(new Timetable());
                break;
            default:
                throw new StrategyNotRecognizedException($strategyId);
        }
    }

    public function buildTimetable($vacancies, $starting = 'today', $days = 1)
    {
        return $this->strategy->buildTimetable($vacancies, $starting, $days);
    }
}
