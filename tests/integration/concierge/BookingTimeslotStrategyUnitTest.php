<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;

class BookingTimeslotStrategyUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy, CreateAppointment;

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

    /**
     * @test
     */
    public function it_generates_a_timeslot_timetable_and_discounts_one_booked_appointment()
    {
        $this->arrangeFixture();

        $bookingStrategy = new BookingStrategy('timeslot');

        $timezone = $this->vacancy->business->timezone;

        $appointment = $this->createAppointment([
            'business_id' => $this->business->id,
            'contact_id'  => $this->contact->id,
            'service_id'  => $this->service->id,
            'vacancy_id'  => $this->vacancy->id,
            'status'      => 'C',
            'start_at'    => Carbon\Carbon::parse("{$this->vacancy->date} 10:00:00 ".$timezone)->timezone('UTC'),
            'finish_at'   => Carbon\Carbon::parse("{$this->vacancy->date} 10:30:00 ".$timezone)->timezone('UTC'),
            'duration'    => 30,
            'comments'    => 'test dont test',
            ]);

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $bookingStrategy->buildTimetable($vacancies);

        $expected = [
            $this->vacancy->date => [
                $appointment->service->slug => [
                    $appointment->start_at->timezone($timezone)->toTimeString() => $this->vacancy->capacity - 1,
                    ],
                ],
            ];

        $this->assertArraySubset($expected, $timetable);
    }

    /**
     * @test
     */
    public function it_generates_a_timeslot_timetable_and_discounts_two_booked_appointments()
    {
        $this->arrangeFixture();

        $bookingStrategy = new BookingStrategy('timeslot');

        $timezone = $this->vacancy->business->timezone;

        $appointmentOne = $this->createAppointment([
            'business_id' => $this->business->id,
            'contact_id'  => $this->contact->id,
            'service_id'  => $this->service->id,
            'vacancy_id'  => $this->vacancy->id,
            'status'      => 'C',
            'start_at'    => Carbon\Carbon::parse("{$this->vacancy->date} 10:00:00 ".$timezone)->timezone('UTC'),
            'finish_at'   => Carbon\Carbon::parse("{$this->vacancy->date} 10:30:00 ".$timezone)->timezone('UTC'),
            'duration'    => 30,
            'comments'    => 'test dont test',
            ]);

        $contactTwo = $this->createContact();

        $appointmentTwo = $this->createAppointment([
            'business_id' => $this->business->id,
            'contact_id'  => $contactTwo->id,
            'service_id'  => $this->service->id,
            'vacancy_id'  => $this->vacancy->id,
            'status'      => 'C',
            'start_at'    => Carbon\Carbon::parse("{$this->vacancy->date} 10:00:00 ".$this->business->timezone)->timezone('UTC'),
            'finish_at'   => Carbon\Carbon::parse("{$this->vacancy->date} 10:30:00 ".$this->business->timezone)->timezone('UTC'),
            'duration'    => 30,
            'comments'    => 'test dont test',
            ]);

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $bookingStrategy->buildTimetable($vacancies);

        $expected = [
            $this->vacancy->date => [
                $appointmentOne->service->slug => [
                    $appointmentOne->start_at->timezone($timezone)->toTimeString() => $this->vacancy->capacity - 2,
                    ],
                ],
            ];

        $this->assertArraySubset($expected, $timetable);
    }
}
