<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Vacancy\VacancyManager;
use Timegridio\Concierge\Vacancy\VacancyParser;
use Timegridio\Concierge\Vacancy\VacancyTemplateBuilder;

class VacancyManagerUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use ArrangeFixture;
    use CreateUser, CreateBusiness, CreateService, CreateAppointment, CreateContact, CreateVacancy, CreateHumanresource;

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
    public function it_unpublishes_all_vacancies()
    {
        $count = rand(2, 20);

        for ($i=0; $i < $count ; $i++) { 
            $this->createVacancy(['business_id' => $this->business->id]);
        }
        
        $vacancies = $this->business->fresh()->vacancies;

        $this->assertCount($count, $vacancies);

        $this->vacancyManager->unpublish();

        $vacancies = $this->business->fresh()->vacancies;

        $this->assertCount(0, $vacancies);
    }

    /**
     * @test
     */
    public function it_publishes_a_batch_vacancy_statement()
    {
        $this->business = $this->createBusiness();
        $this->service = $this->createService();

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
    public function regression_it_alters_only_owned_service_slug()
    {
        $this->business = $this->createBusiness();

        $serviceOne = $this->createService(['name' => 'support']); // Belongs to another business
        $serviceTwo = $this->createService(['name' => 'support', 'business_id' => $this->business->id]);

        $capacity = 1;

        $vacancyStatement = <<<EOD
{$serviceTwo->slug}:{$capacity}
 mon
  8-12
EOD;

        $publishedVacancies = $this->vacancyParser->parseStatements($vacancyStatement);

        $this->vacancyManager->updateBatch($this->business, $publishedVacancies);

        $vacancies = $this->business->vacancies()->get();

        $this->assertCount(1, $vacancies);
    }

    /**
     * @test
     */
    public function it_publishes_a_batch_vacancy_statement_for_a_humanresource()
    {
        $this->business = $this->createBusiness();

        $humanresource = $this->createHumanresource(['business_id' => $this->business->id]);

        $serviceOne = $this->createService(['name' => 'support']); // Belongs to another business
        $serviceTwo = $this->createService(['name' => 'support', 'business_id' => $this->business->id]);

        $vacancyStatement = <<<EOD
{$serviceTwo->slug}:{$humanresource->slug}
 mon
  8-12
EOD;

        $publishedVacancies = $this->vacancyParser->parseStatements($vacancyStatement);

        $this->vacancyManager->updateBatch($this->business, $publishedVacancies);

        $vacancies = $this->business->vacancies()->get();

        $this->assertCount(1, $vacancies);
        $this->assertEquals($humanresource->id, $vacancies->first()->humanresource->id);
    }

    /**
     * @test
     */
    public function it_generates_availability_array()
    {
        $this->arrangeFixture();

        $future = 10;
        $start = 'today';

        $availability = $this->vacancyManager->generateAvailability($start, $future);

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

    /**
     * @test
     */
    public function it_provides_a_template_builder()
    {
        $builder = $this->vacancyManager->builder();

        $this->assertInstanceOf(VacancyTemplateBuilder::class, $builder);
    }
}
