<?php

use Timegridio\Concierge\Vacancy\VacancyParser;
use Timegridio\Concierge\Vacancy\VacancyTemplateBuilder;

class VacancyTemplateBuilderTest extends TestCaseDB
{
    use CreateBusiness, CreateService;

    private $vacancyTemplateBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->vacancyTemplateBuilder = new VacancyTemplateBuilder();
    }

    /**
     * @test
     */
    public function it_builds_a_default_template()
    {
        $business = $this->createBusiness();

        $startAt = $business->pref('start_at', '09:00');
        $finishAt = $business->pref('finish_at', '18:00');

        $service = $this->createService(['business_id' => $business->id]);

        $template = $this->vacancyTemplateBuilder->getTemplate($business, $business->services()->first());

        $this->assertInternalType('string', $template);
        $this->assertContains($service->slug, $template);
        $this->assertContains('mon, tue, wed, thu, fri, sat', $template);
        $this->assertContains($startAt, $template);
        $this->assertContains($finishAt, $template);
    }

    /**
     * @test
     */
    public function it_builds_a_valid_parseable_template()
    {
        $business = $this->createBusiness();

        $startAt = $business->pref('start_at', '09:00');
        $finishAt = $business->pref('finish_at', '18:00');

        $service = $this->createService(['business_id' => $business->id]);

        $template = $this->vacancyTemplateBuilder->getTemplate($business, $business->services()->first());

        $vacancyParser = new VacancyParser();

        $parsedStatements = $vacancyParser->parseStatements($template);

        $this->assertCount(6, $parsedStatements);
    }
}
