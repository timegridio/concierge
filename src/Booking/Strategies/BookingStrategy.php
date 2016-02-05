<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\Timetable;
use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;

class BookingStrategy
{
    /**
     * Booking Strategy.
     *
     * @var BookingTimeslotStrategy|BookingDateslotStrategy
     */
    protected $strategy = null;

    /**
     * Construct Booking Strategy class.
     *
     * @param string $strategyId
     *
     * @throws  Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function __construct($strategyId)
    {
        switch ($strategyId) {
            case 'timeslot':
                $this->strategy = new BookingTimeslotStrategy(new Timetable());
                break;
            case 'dateslot':
                $this->strategy = new BookingDateslotStrategy(new Timetable());
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
