<?php

trait ArrangeFixture
{
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

        // a Business owned by Me (User)
        $this->owner = $this->createUser();

        $this->issuer = $this->createUser();

        $this->business = $this->createBusiness();
        $this->business->owners()->save($this->owner);

        // And the Business provides a Service
        $this->service = $this->makeService();
        $this->business->services()->save($this->service);

        $startAt = Carbon\Carbon::parse('today 09:00 '.$this->business->timezone)->timezone('UTC');
        $finishAt = Carbon\Carbon::parse('today 18:00 '.$this->business->timezone)->timezone('UTC');

        // And the Service has Vacancies to be reserved
        $this->vacancy = $this->makeVacancy([
            'start_at'  => $startAt->toDateTimeString(),
            'finish_at' => $finishAt->toDateTimeString(),
            'date'      => $startAt->timezone($this->business->timezone)->toDateString(),
            'capacity'  => 2,
            ]);
        $this->vacancy->service()->associate($this->service);
        $this->business->vacancies()->save($this->vacancy);

        // And a Contact that holds an Appointment for that Service
        $this->contact = $this->createContact();
    }
}
