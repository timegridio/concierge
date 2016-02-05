<?php

namespace Timegridio\Concierge\Calendar;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Timegridio\Concierge\Exceptions\StrategyMethodNotRecognizedException;
use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;

class Calendar
{
    /**
     * Strategy Calendar.
     *
     * @var TimeslotCalendar|DateslotCalendar
     */
    protected $strategy = null;

    /**
     * Construct the class and load the strategy Calendar.
     *
     * @param string $strategyName
     * @param HasMany $vacancies    The entity relationship to Vacancies.
     * @param string $timezone
     *
     * @throws Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function __construct($strategyName, HasMany $vacancies, $timezone = null)
    {
        switch (strtolower($strategyName)) {
            case 'timeslot':
                $this->strategy = new TimeslotCalendar($vacancies, $timezone);
                break;
            case 'dateslot':
                $this->strategy = new DateslotCalendar($vacancies, $timezone);
                break;
            default:
                throw new StrategyNotRecognizedException();
        }
    }

    /**
     * Pass method call to the Calendar strategy.
     *
     * @param  string $name
     * @param  mixed $arguments
     *
     * @throws Timegridio\Concierge\Exceptions\StrategyMethodNotRecognizedException
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->strategy, $name)) {
            throw new StrategyMethodNotRecognizedException();
        }

        return $this->strategy->$name($arguments);
    }
}
