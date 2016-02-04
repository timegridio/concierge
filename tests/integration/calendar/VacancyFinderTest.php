<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Calendar\VacancyCalendar;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Test\Models\User;

class VacancyCalendarTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateContact, CreateBusiness, CreateService, CreateAppointment, CreateVacancy;

    protected $user;

    protected $business;

    protected $contact;

    protected $service;

    protected $vacancy;

    protected $finder;

    public function setUp()
    {
        parent::setUp();

        $this->arrangeScenario();
    }

    /**
     * @test
     */
    public function it_finds_a_vacancy_slot_for_a_date_and_time_when_available()
    {
        $vacancyCalendar = $this->finder
                          ->forServiceAndDateTime($this->vacancy->service, $this->vacancy->start_at);

        $this->assertEquals(1, count($vacancyCalendar->find()));
    }

    /**
     * @test
     */
    public function it_doesnt_find_a_vacancy_slot_for_a_date_and_time_when_not_available()
    {
        $vacancyCalendar = $this->finder
                              ->forServiceAndDateTime($this->vacancy->service, $this->vacancy->start_at->addDays(1));

                              #dd($vacancyCalendar->find());

        $this->assertEquals(0, count($vacancyCalendar->find()));
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

        $this->finder = new VacancyCalendar();
        $this->finder->business($this->business);
    }
}
