<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Service;

trait CreateService
{
    private function createService($overrides = [])
    {
        return Factory::create(Service::class, $overrides);
    }

    private function makeService($overrides = [])
    {
        return Factory::build(Service::class, $overrides);
    }
}
