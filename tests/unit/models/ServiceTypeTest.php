<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\ServiceType;

class ServiceTypeTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateService;

    /**
     * @test
     */
    public function it_has_services()
    {
        $serviceType = new ServiceType;

        $this->assertInstanceOf(HasMany::class, $serviceType->services());
    }

    /**
     * @test
     */
    public function it_belongs_to_businesses()
    {
        $serviceType = new ServiceType;

        $this->assertInstanceOf(BelongsTo::class, $serviceType->business());
    }
}
