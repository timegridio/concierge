<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Repository;

class RepositoryUnitTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateBusiness, CreateService;

    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new Repository();
    }

    /**
     * @test
     */
    public function it_finds_a_business_by_default_identifier()
    {
        $stubBusiness = $this->createBusiness([
            'slug' => 'test-a-business-slug',
            ]);

        $testBusiness = $this->repository->getBusiness($stubBusiness->slug);

        $this->assertEquals($testBusiness->id, $stubBusiness->id);
        $this->assertInstanceOf(Business::class, $testBusiness);
    }

    /**
     * @test
     */
    public function it_finds_a_business_service_by_default_identifier()
    {
        $business = $this->createBusiness([
            'slug' => 'test-a-business-slug',
            ]);

        $serviceOne = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service One',
            ]);

        $testService = $this->repository->getService($business, 'service-one');

        $this->assertEquals($business->id, $testService->business->id);
        $this->assertEquals($serviceOne->id, $testService->id);
        $this->assertInstanceOf(Service::class, $testService);
    }

    /**
     * @test
     */
    public function it_finds_a_business_service_by_default_identifier_among_many()
    {
        $business = $this->createBusiness([
            'slug' => 'test-a-business-slug',
            ]);

        $serviceOne = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service One',
            ]);

        $serviceTwo = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service Two',
            ]);

        $serviceThree = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service Three',
            ]);

        $testService = $this->repository->getService($business, 'service-two');

        $this->assertEquals($business->id, $testService->business->id);
        $this->assertEquals($serviceTwo->id, $testService->id);
        $this->assertInstanceOf(Service::class, $testService);
    }

    /**
     * @test
     */
    public function it_finds_a_business_service_by_id_among_many()
    {
        $business = $this->createBusiness([
            'slug' => 'test-a-business-slug',
            ]);

        $serviceOne = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service One',
            ]);

        $serviceTwo = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service Two',
            ]);

        $serviceThree = $this->createService([
            'business_id' => $business->id,
            'name'        => 'Service Three',
            ]);

        $testService = $this->repository->getService($business, $serviceOne->id, 'id');

        $this->assertEquals($business->id, $testService->business->id);
        $this->assertEquals($serviceOne->id, $testService->id);
        $this->assertInstanceOf(Service::class, $testService);
    }
}
