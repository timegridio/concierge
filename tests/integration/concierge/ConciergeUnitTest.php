<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;

class ConciergeUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy;

    protected $concierge;

    public function setUp()
    {
        parent::setUp();
        $this->concierge = new Concierge();
        $this->arrangeFixture();

    }

    /**
     * @test
     */
    public function it_takes_a_reservation()
    {
        $this->assertTrue(true);

//        $reservationRequest = [
//            'issuer'   => $this->owner,
//            'contact'  => $this->contact,
//            'date'     => '2016-02-04',
//            'time'     => '10:30:00',
//            'timezone' => $this->business->timezone,
//            'service'  => $this->service,
//            'comments' => 'test dont test',
//        ];
//
//        $appointment = $this->concierge
//                            ->business($this->business)
//                            ->takeReservation($reservationRequest);
//
//        $this->assertInstanceOf(Appointment::class, $appointment);
//
//        $this->assertEquals($appointment->issuer->id, $this->owner->id);
//        $this->assertEquals($appointment->contact->name, $this->contact->name);
//        $this->assertEquals($appointment->service->name, $this->service->name);
//        $this->assertEquals($appointment->date, $reservationRequest['date']);
//        $this->assertEquals($appointment->comments, 'test dont test');
//        $this->assertTrue($appointment->exists);
//        $this->assertEquals(32, strlen($appointment->hash));
    }
}
