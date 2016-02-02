<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\ServiceType;

trait CreateServiceType
{
    private function createServiceType($overrides = [])
    {
        return Factory::create(ServiceType::class, $overrides);
    }

    private function makeServiceType($overrides = [])
    {
        return Factory::build(ServiceType::class, $overrides);
    }
}
