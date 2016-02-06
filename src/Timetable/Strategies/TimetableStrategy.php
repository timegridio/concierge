<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Carbon\Carbon;
use Timegridio\Concierge\Timetable\Timetable;
use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;

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
