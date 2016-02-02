<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Tests\Models\User;

class VacancyTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateBusiness, CreateService, CreateVacancy, CreateContact, CreateAppointment;

    protected $business;

    /**
     * @covers  \Timegridio\Concierge\Models\Vacancy::isHoldingAnyFor
     * @test
     */
    public function it_verifies_a_vacancy_holds_appointment_for_a_user()
    {
        /* Setup Stubs */
        $issuer = $this->createUser();

        $business = $this->createBusiness();
        $business->owners()->save($issuer);

        $service = $this->makeService();
        $business->services()->save($service);

        $vacancy = $this->makeVacancy();
        $vacancy->service()->associate($service);
        $business->vacancies()->save($vacancy);

        $contact = $this->createContact();
        $contact->user()->associate($issuer);
        $contact->save();
        $business->contacts()->save($contact);

        $appointment = $this->makeAppointment($business, $issuer, $contact);
        $appointment->service()->associate($service);
        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        /* Perform Test */
        $this->assertTrue($vacancy->isHoldingAnyFor($issuer->id));
    }

    /**
     * @covers            \Timegridio\Concierge\Models\Vacancy::isHoldingAnyFor
     * @test
     */
    public function it_verifies_a_vacancy_doesnt_hold_appointment_for_a_user()
    {
        /* Setup Stubs */
        $issuer = $this->createUser();

        $business = $this->createBusiness();
        $business->owners()->save($issuer);

        $service = $this->makeService();
        $business->services()->save($service);

        $vacancy = $this->makeVacancy();
        $vacancy->service()->associate($service);
        $business->vacancies()->save($vacancy);

        $contact = $this->createContact();
        $business->contacts()->save($contact);

        $appointment = $this->makeAppointment($business, $issuer, $contact);
        $appointment->service()->associate($service);
        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        /* Perform Test */
        $this->assertFalse($vacancy->isHoldingAnyFor($issuer->id));
    }
}
