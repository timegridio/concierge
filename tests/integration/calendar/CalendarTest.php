<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\VacancyManager;
use Timegridio\Test\Models\User;

class CalendarTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateContact, CreateBusiness, CreateService, CreateAppointment, CreateVacancy;

    protected $user;

    protected $business;

    protected $contact;

    protected $service;

    protected $vacancy;

    protected $calendar;

    public function setUp()
    {
        parent::setUp();

        $this->arrangeScenario();
    }

    /**
     * @test
     */
    public function it_has_the_business_timezone()
    {
        $this->assertEquals($this->business->timezone, $this->calendar->timezone());
    }

    /**
     * @test
     */
    public function it_sets_a_timezone()
    {
        $timezone = 'Australia/Brisbane';

        $this->assertEquals($timezone, $this->calendar->timezone($timezone));
    }

    /////////////
    // HELPERS //
    /////////////

    /**
     * Arrange a fixture for testing.
     *
     * @return void
     */
    protected function arrangeScenario()
    {
        $this->user = $this->createUser();

        $this->business = $this->createBusiness();

        $this->service = $this->createService([
            'business_id' => $this->business->id,
            ]);

        $this->contact = $this->createContact();

        $this->vacancy = $this->makeVacancy();
        $this->vacancy->business()->associate($this->business);
        $this->vacancy->service()->associate($this->service);
        $this->business->vacancies()->save($this->vacancy);

        $this->calendar = new Calendar();
        $this->calendar->business($this->business);
    }
}
