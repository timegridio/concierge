<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class VacancyTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateBusiness, CreateService, CreateVacancy, CreateContact, CreateAppointment;

    protected $business;

    protected $contact;

    protected $vacancy;

    protected $issuer;

    protected $service;

    protected $appointment;

    /**
     * @covers  Timegridio\Concierge\Models\Vacancy::scopeFuture
     * @test
     */
    public function it_scopes_future()
    {
        $this->arrangeFixture();

        $this->vacancy = $this->createVacancy([
            'business_id'  => $this->business->id,
            'date'         => Carbon::parse('tomorrow')->toDateString(),
            'start_at'     => Carbon::parse('tomorrow'),
            ]);

        $this->vacancy = $this->createVacancy([
            'business_id'  => $this->business->id,
            'date'         => Carbon::parse('tomorrow +1 day')->toDateString(),
            'start_at'     => Carbon::parse('tomorrow +1 day'),
            ]);

        $vacancies = Vacancy::future()->get();

        /* Perform Test */
        $this->assertCount(2, $vacancies);
    }

    /**
     * @covers  Timegridio\Concierge\Models\Vacancy::isHoldingAnyFor
     * @test
     */
    public function it_verifies_a_vacancy_holds_appointment_for_a_user()
    {
        $this->arrangeFixture();

        $this->contact = $this->createContact();
        $this->contact->user()->associate($this->issuer);
        $this->contact->save();
        $this->business->contacts()->save($this->contact);

        $this->appointment = $this->makeAppointment($this->business, $this->issuer, $this->contact);
        $this->appointment->service()->associate($this->service);
        $this->appointment->vacancy()->associate($this->vacancy);
        $this->appointment->save();

        /* Perform Test */
        $this->assertTrue($this->vacancy->isHoldingAnyFor($this->issuer->id));
    }

    /**
     * @covers  Timegridio\Concierge\Models\Vacancy::isHoldingAnyFor
     * @test
     */
    public function it_verifies_a_vacancy_doesnt_hold_appointment_for_a_user()
    {
        $this->arrangeFixture();

        $this->contact = $this->createContact();
        $this->business->contacts()->save($this->contact);

        $this->appointment = $this->makeAppointment($this->business, $this->issuer, $this->contact);
        $this->appointment->service()->associate($this->service);
        $this->appointment->vacancy()->associate($this->vacancy);
        $this->appointment->save();

        /* Perform Test */
        $this->assertFalse($this->vacancy->isHoldingAnyFor($this->issuer->id));
    }

    protected function arrangeFixture()
    {
        /* Setup Stubs */
        $this->issuer = $this->createUser();

        $this->business = $this->createBusiness();
        $this->business->owners()->save($this->issuer);

        $this->service = $this->makeService();
        $this->business->services()->save($this->service);

        $this->vacancy = $this->makeVacancy();
        $this->vacancy->service()->associate($this->service);
        $this->business->vacancies()->save($this->vacancy);
    }
}
