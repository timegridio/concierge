<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Vacancy\VacancyManager;

class VacancyManagerUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateBusiness, CreateService;

    protected $business;

    protected $vacancy;

    public function setUp()
    {
        parent::setUp();

        $this->business = $this->createBusiness();

        $this->vacancy = new VacancyManager($this->business);
    }

    /**
     * @test
     */
    public function it_publishes_a_vacancy()
    {
        $timezone = $this->business->timezone;

        $date = Carbon::parse('today 00:00 '.$timezone)->toDateString();
        $startAt = Carbon::parse('today 08:00 '.$timezone);
        $finishAt = Carbon::parse('today 18:00 '.$timezone);
        $service = $this->createService();
        $capacity = 5;

        $vacancy = $this->vacancy->publish($date, $startAt, $finishAt, $service->id, $capacity);

        $this->assertInstanceOf(Vacancy::class, $vacancy);
        $this->assertEquals($vacancy->date, $date);
        $this->assertEquals($vacancy->start_at, $startAt);
        $this->assertEquals($vacancy->finish_at, $finishAt);
        $this->assertEquals($vacancy->capacity, $capacity);
        $this->assertEquals($vacancy->service->id, $service->id);
    }
}
