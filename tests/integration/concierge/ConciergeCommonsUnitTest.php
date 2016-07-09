<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Vacancy\VacancyManager;

class ConciergeCommonsUnitTest extends TestCaseDB
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
            'strategy' => 'timeslot',
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
    public function it_cancels_an_appointment()
    {
        $appointment = $this->createAppointment([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'start_at'    => Carbon::parse('today +5 days'),
            'status'      => Appointment::STATUS_RESERVED,
            ]);

        $appointment = $this->concierge->business($this->business)->booking()->appointment($appointment->hash)->cancel();

        $this->assertEquals(Appointment::STATUS_CANCELED, $appointment->status);
    }

    /**
     * @test
     */
    public function it_confirms_an_appointment()
    {
        $appointment = $this->createAppointment([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'start_at'    => Carbon::parse('today +5 days'),
            'status'      => Appointment::STATUS_RESERVED,
            ]);

        $appointment = $this->concierge->business($this->business)->booking()->appointment($appointment->hash)->confirm();

        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->status);
    }

    /**
     * @test
     */
    public function it_serves_an_appointment()
    {
        $appointment = $this->createAppointment([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'start_at'    => Carbon::parse('today -2 days'),
            'status'      => Appointment::STATUS_RESERVED,
            ]);

        $appointment = $this->concierge->business($this->business)->booking()->appointment($appointment->hash)->serve();

        $this->assertEquals(Appointment::STATUS_SERVED, $appointment->status);
    }

    /**
     * @test
     */
    public function it_provides_access_to_vacancy_manager()
    {
        $vacancyManager = $this->concierge->business($this->business)->vacancies();

        $this->assertInstanceOf(VacancyManager::class, $vacancyManager);
    }

    /**
     * @test
     */
    public function it_provides_active_appointments()
    {
        $appointments = $this->concierge->business($this->business)->getActiveAppointments();

        $this->assertInstanceOf(Collection::class, $appointments);

        foreach ($appointments as $appointment) {
            $this->assertInstanceOf(Appointment::class, $appointment);
            $this->assertTrue($appointment->isActive());
        }
    }

    /**
     * @test
     */
    public function it_provides_unserved_appointments()
    {
        $appointments = $this->concierge->business($this->business)->getActiveAppointments();

        $this->assertInstanceOf(Collection::class, $appointments);

        foreach ($appointments as $appointment) {
            $this->assertInstanceOf(Appointment::class, $appointment);
            $this->assertTrue($appointment->isUnserved());
        }
    }

    /**
     * @test
     */
    public function it_provides_unarchived_appointments()
    {
        $appointments = $this->concierge->business($this->business)->getUnarchivedAppointments();

        $this->assertInstanceOf(Collection::class, $appointments);

        foreach ($appointments as $appointment) {
            $this->assertInstanceOf(Appointment::class, $appointment);
            $this->assertTrue($appointment->isUnserved());
        }
    }
}
