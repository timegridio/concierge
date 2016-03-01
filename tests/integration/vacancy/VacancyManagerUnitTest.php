<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Vacancy\VacancyManager;
use Timegridio\Concierge\Vacancy\VacancyParser;

class VacancyManagerUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture, CreateUser, CreateBusiness, CreateService, CreateAppointment, CreateContact, CreateVacancy;

    protected $business;

    protected $vacancyManager;

    protected $vacancyParser;

    public function setUp()
    {
        parent::setUp();

        $this->business = $this->createBusiness();

        $this->vacancyManager = new VacancyManager($this->business);

        $this->vacancyParser = new VacancyParser();
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

        $vacancy = $this->vacancyManager->publish($date, $startAt, $finishAt, $service->id, $capacity);

        $this->assertInstanceOf(Vacancy::class, $vacancy);
        $this->assertEquals($vacancy->date, $date);
        $this->assertEquals($vacancy->start_at, $startAt);
        $this->assertEquals($vacancy->finish_at, $finishAt);
        $this->assertEquals($vacancy->capacity, $capacity);
        $this->assertEquals($vacancy->service->id, $service->id);
    }

    /**
     * @test
     */
    public function it_publishes_a_batch_vacancy_statement()
    {
        $this->arrangeFixture();

        $capacity = 1;

        $vacancyStatement = <<<EOD
{$this->service->slug}:{$capacity}
 mon,tue
  8-12,14-20
EOD;

        $publishedVacancies = $this->vacancyParser->parseStatements($vacancyStatement);

        $this->vacancyManager->updateBatch($this->business, $publishedVacancies);

        $vacancies = $this->business->vacancies()->get();

        foreach ($vacancies as $vacancy) {
            $this->assertInstanceOf(Vacancy::class, $vacancy);
            $this->assertEquals($vacancy->capacity, $capacity);
            $this->assertEquals($vacancy->service->id, $this->service->id);
        }
    }

    /**
     * @test
     */
    public function it_publishes_a_batch_vacancy_statement_with_unidentified_service()
    {
        $capacity = 1;

        $vacancyStatement = <<<EOD
unidentified:{$capacity}
 mon,tue
  8-12,14-20
EOD;

        $publishedVacancies = $this->vacancyParser->parseStatements($vacancyStatement);

        $this->vacancyManager->updateBatch($this->business, $publishedVacancies);

        $vacancies = $this->business->vacancies()->get();

        $this->assertCount(0, $vacancies);
    }

    /**
     * @test
     */
    public function it_generates_availability_array()
    {
        $this->arrangeFixture();

        $future = 10;
        $start = 'today';

        $availability = $this->vacancyManager->generateAvailability($this->business->vacancies, $start, $future);

        $this->assertInternalType('array', $availability);
        $this->assertCount($future, $availability);

        foreach ($availability as $date => $vacancies) {
            foreach ($availability as $date => $vacancies) {
                foreach ($vacancies as $vacancy) {
                    $this->assertInstanceOf(Vacancy::class, $vacancy);
                }
            }
        }
    }
}
