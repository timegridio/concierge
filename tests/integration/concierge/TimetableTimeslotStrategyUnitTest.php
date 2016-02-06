<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Timetable\Strategies\TimetableStrategy;

class TimetableTimeslotStrategyUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy, CreateAppointment;

    /**
     * @test
     * @expectedException Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function it_rejects_unrecognized_strategies()
    {
        new TimetableStrategy('invalid');
    }

    /**
     * @test
     */
    public function it_generates_a_timeslot_timetable()
    {
        $this->arrangeFixture();

        $timetableStrategy = new TimetableStrategy('timeslot');

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $timetableStrategy->buildTimetable($vacancies);

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

        $timetableStrategy = new TimetableStrategy('timeslot');

        $timezone = $this->vacancy->business->timezone;

        $appointment = $this->createAppointment([
            'business_id' => $this->business->id,
            'contact_id'  => $this->contact->id,
            'service_id'  => $this->service->id,
            'vacancy_id'  => $this->vacancy->id,
            'status'      => 'C',
            'start_at'    => Carbon::parse("{$this->vacancy->date} 10:00:00 ".$timezone)->timezone('UTC'),
            'finish_at'   => Carbon::parse("{$this->vacancy->date} 10:30:00 ".$timezone)->timezone('UTC'),
            'duration'    => 30,
            'comments'    => 'test dont test',
            ]);

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $timetableStrategy->buildTimetable($vacancies);

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

        $timetableStrategy = new TimetableStrategy('timeslot');

        $timezone = $this->vacancy->business->timezone;

        $appointmentOne = $this->createAppointment([
            'business_id' => $this->business->id,
            'contact_id'  => $this->contact->id,
            'service_id'  => $this->service->id,
            'vacancy_id'  => $this->vacancy->id,
            'status'      => 'C',
            'start_at'    => Carbon::parse("{$this->vacancy->date} 10:00:00 ".$timezone)->timezone('UTC'),
            'finish_at'   => Carbon::parse("{$this->vacancy->date} 10:30:00 ".$timezone)->timezone('UTC'),
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
            'start_at'    => Carbon::parse("{$this->vacancy->date} 10:00:00 ".$this->business->timezone)->timezone('UTC'),
            'finish_at'   => Carbon::parse("{$this->vacancy->date} 10:30:00 ".$this->business->timezone)->timezone('UTC'),
            'duration'    => 30,
            'comments'    => 'test dont test',
            ]);

        $vacancies = $this->business->vacancies()->with('appointments')->with('service')->get();

        $timetable = $timetableStrategy->buildTimetable($vacancies);

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
