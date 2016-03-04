<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;

class ConciergeDateslotUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy, CreateAppointment;

    /**
     * Business Owner.
     *
     * @var App\Models\User
     */
    protected $owner = null;

    /**
     * Appointment Issuer.
     *
     * @var App\Models\User
     */
    protected $issuer = null;

    /**
     * Appointment Contact.
     *
     * @var Timegridio\Concierge\Models\Contact
     */
    protected $contact = null;

    /**
     * Business.
     *
     * @var Timegridio\Concierge\Models\Business
     */
    protected $business = null;

    /**
     * Serivce.
     *
     * @var Timegridio\Concierge\Models\Service
     */
    protected $service = null;

    /**
     * Business Vacancy.
     *
     * @var Timegridio\Concierge\Models\Vacancy
     */
    protected $vacancy = null;

    /**
     * Arrange Fixture.
     *
     * @return void
     */
    protected function arrangeFixture()
    {
        // Given there is...

        // ...a Business...
        $this->business = $this->createBusiness([
            'strategy' => 'dateslot',
            ]);

        // ...owned by user...
        $this->owner = $this->createUser();
        $this->business->owners()->save($this->owner);

        // ...and the Business provides a service...
        $this->service = $this->createService([
            'business_id' => $this->business->id,
            ]);

        // ...that has a published availability (Vacancy)...
        $date = Carbon::parse('today 00:00 '.$this->business->timezone);
        $startAt = Carbon::parse('today 09:00 '.$this->business->timezone)->timezone('UTC');
        $finishAt = Carbon::parse('today 18:00 '.$this->business->timezone)->timezone('UTC');

        $this->vacancy = $this->createVacancy([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'date'        => $date->toDateString(),
            'start_at'    => $startAt->toDateTimeString(),
            'finish_at'   => $finishAt->toDateTimeString(),
            'capacity'    => 2,
            ]);

        // ...and there is another user that may issue reservation requests...
        $this->issuer = $this->createUser();

        // ...under the addressbook Contact
        $this->contact = $this->createContact([
            'user_id' => $this->issuer->id,
            ]);
    }

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

    /**
     * @test
     * @expectedException Timegridio\Concierge\Exceptions\DuplicatedAppointmentException
     */
    public function it_rejects_a_duplicated_reservation()
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

        // Attempt a duplicated appointment reservation
        $this->concierge->business($this->business)->takeReservation($reservation);

        // The duplicated appointment is accessible for query
        $duplicatedAppointment = $this->concierge->appointment();

        $this->assertInstanceOf(Appointment::class, $duplicatedAppointment);
    }
}
