<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\ServiceType;

class ServiceTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateBusiness, CreateService;

    /**
     * @test
     */
    public function it_scopes_by_slug()
    {
        $business = $this->createBusiness();
        $service = $this->makeService();

        $business->services()->save($service);

        $services = Service::slug($service->slug);
        $count = $services->count();
        $service = $services->first();

        $this->assertInstanceOf(Service::class, $service);
        $this->assertEquals($count, 1);
    }

    /**
     * @test
     */
    public function it_belongs_to_a_nullable_service_type()
    {
        $service = $this->createService();

        $this->assertNull($service->type);
    }

    /**
     * @test
     */
    public function it_belongs_to_a_valid_service_type()
    {
        $service = $this->createService([
            'type_id' => 'factory:Timegridio\Concierge\Models\ServiceType',
            ]);

        $this->assertInstanceOf(ServiceType::class, $service->type);

        $this->assertInternalType('string', $service->typeName);
    }

    /**
     * @test
     */
    public function it_belongs_to_a_default_empty_string_service_type()
    {
        $service = $this->createService([
            'type_id' => null,
            ]);

        $this->assertInternalType('string', $service->typeName);

        $this->assertEquals('', $service->typeName);
    }
}
