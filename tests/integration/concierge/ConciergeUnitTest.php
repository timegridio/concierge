<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;

class ConciergeUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy, CreateAppointment;

    public function setUp()
    {
        parent::setUp();

        $this->arrangeFixture();

        $this->concierge = new Concierge();
    }

    /**
     * @test
     */
    public function it_takes_a_reservation()
    {
        $reservation = [
            'issuer'   => 1,
            'business' => $this->business,
            'contact'  => $this->contact,
            'service'  => $this->service,
            'date'     => $this->vacancy->start_at->timezone($this->business->timezone)->toDateString(),
            'time'     => $this->vacancy->start_at->timezone($this->business->timezone)->toTimeString(),
            'timezone' => $this->business->timezone,
            'comments' => 'test',
        ];

        $appointment = $this->concierge->business($this->business)->takeReservation($reservation);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertTrue($appointment->exists);
        $this->assertEquals(
                $appointment->start_at->timezone($this->business->timezone)->toDateTimeString(),
                $this->vacancy->start_at->timezone($this->business->timezone)->toDateTimeString()
                );
    }

    /**
     * @test
     */
    public function it_rejects_a_reservation_if_not_available()
    {
        $reservation = [
            'issuer'   => 1,
            'business' => $this->business,
            'contact'  => $this->contact,
            'service'  => $this->service,
            'date'     => $this->vacancy->start_at->timezone($this->business->timezone)->addDays(99)->toDateString(),
            'time'     => $this->vacancy->start_at->timezone($this->business->timezone)->toTimeString(),
            'timezone' => $this->business->timezone,
            'comments' => 'test',
        ];

        $appointment = $this->concierge->business($this->business)->takeReservation($reservation);

        $this->assertFalse($appointment);
    }
}
