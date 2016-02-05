<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Vacancy;

class DateslotCalendarTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateContact, CreateBusiness, CreateService, CreateVacancy;

    public function setUp()
    {
        parent::setUp();

        $this->arrangeScenario();
    }

    /**
     * @test
     */
    public function it_sets_a_service()
    {
        $this->calendar->forService($this->service->id);
    }

    /**
     * @test
     */
    public function it_sets_a_timezone()
    {
        $this->calendar->timezone($this->business->timezone);
    }

    /**
     * @test
     */
    public function it_finds_all_vacancies_for_a_service_dateslot()
    {
        $vacancies = $this->calendar
                          ->forService($this->vacancy->service->id)
                          ->find();

        $this->assertInstanceOf(Collection::class, $vacancies);
        $this->assertNotCount(0, $vacancies);

        foreach ($vacancies as $vacancy) {
            $this->assertEquals($vacancy->business->id, $this->business->id);
            $this->assertEquals($vacancy->service->id, $this->service->id);
        }
    }

    /**
     * @test
     */
    public function it_finds_free_dateslot_vacancy_for_a_service_and_date()
    {
        $this->vacancy = $this->makeVacancy([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'date'        => Carbon::parse('2016-01-02 00:00 '.$this->business->timezone)->toDateString(),
            'start_at'    => Carbon::parse('2016-01-02 09:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'finish_at'   => Carbon::parse('2016-01-02 09:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'capacity'    => 5,
            ]);
        $this->vacancy->save();

        $vacancies = $this->calendar
                          ->forService($this->vacancy->service->id)
                          ->forDate('2016-01-02')
                          ->find();

        $this->assertInstanceOf(Collection::class, $vacancies);
        $this->assertNotCount(0, $vacancies);

        foreach ($vacancies as $vacancy) {
            $this->assertEquals($vacancy->business->id, $this->business->id);
            $this->assertEquals($vacancy->service->id, $this->service->id);
        }
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
        $this->business = $this->createBusiness([
            'strategy' => 'dateslot',
            ]);

        $this->service = $this->createService([
            'business_id' => $this->business->id,
            ]);

        $this->contact = $this->createContact();

        $this->vacancy = $this->createVacancy([
            'business_id' => $this->business->id,
            'service_id'  => $this->service->id,
            'date'        => Carbon::parse('today 00:00 '.$this->business->timezone)->toDateString(),
            'start_at'    => Carbon::parse('today 09:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'finish_at'   => Carbon::parse('today 18:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'capacity'    => 1,
            ]);

        $this->createVacancy([
            'date'        => Carbon::parse('tomorrow 00:00 '.$this->business->timezone)->toDateString(),
            'start_at'    => Carbon::parse('tomorrow 09:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'finish_at'   => Carbon::parse('tomorrow 18:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'capacity'    => 2,
            ]);

        $this->createVacancy([
            'date'        => Carbon::parse('tomorrow +1 day 00:00 '.$this->business->timezone)->toDateString(),
            'start_at'    => Carbon::parse('tomorrow +1 day 09:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'finish_at'   => Carbon::parse('tomorrow +1 day 18:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'capacity'    => 4,
            ]);

        $this->createVacancy([
            'date'        => Carbon::parse('tomorrow +2 day 00:00 '.$this->business->timezone)->toDateString(),
            'start_at'    => Carbon::parse('tomorrow +2 day 10:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'finish_at'   => Carbon::parse('tomorrow +2 day 20:00 '.$this->business->timezone)->timezone('UTC')->toDateTimeString(),
            'capacity'    => 4,
            ]);

        $this->calendar = new Calendar('dateslot', $this->business->vacancies(), $this->business->timezone);
    }
}
