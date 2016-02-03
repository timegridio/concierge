<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;

class BookingTimeslotStrategyUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy;

    /**
     * @test
     */
    public function it_generates_a_timeslot_timetable()
    {
        $this->arrangeFixture();

        $bookingStrategy = new BookingStrategy('timeslot');

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $bookingStrategy->buildTimetable($vacancies);

        $timezone = $this->vacancy->business->timezone;

        $expected = [
            $this->vacancy->date => [
                $this->vacancy->service->slug => [
                    $this->vacancy->start_at->timezone($timezone)->toTimeString() => $this->vacancy->capacity,
                    ],
                ],
            ];

        $this->assertArraySubset($expected, $timetable);
    }
}
