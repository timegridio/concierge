<?php

use Timegridio\Concierge\Calendar\Calendar;

class CalendarTest extends TestCaseDB
{
    use CreateBusiness;

    /**
     * @test
     * @expectedException Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function it_rejects_an_unknown_strategy()
    {
        $business = $this->createBusiness();

        new Calendar('unknown-strategy', $business->vacancies());
    }

    /**
     * @test
     * @expectedException Timegridio\Concierge\Exceptions\StrategyMethodNotRecognizedException
     */
    public function it_rejects_an_unknown_method()
    {
        $business = $this->createBusiness();

        $calendar = new Calendar('dateslot', $business->vacancies());

        $calendar->anUnknownMethod();
    }
}
