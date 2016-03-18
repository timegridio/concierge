<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Humanresource;

trait CreateHumanresource
{
    private function createHumanresource($overrides = [])
    {
        return Factory::create(Humanresource::class, $overrides);
    }

    private function makeHumanresource($override = [])
    {
        return Factory::build(Humanresource::class, $override);
    }
}
