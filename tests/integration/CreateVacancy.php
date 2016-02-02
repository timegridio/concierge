<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Vacancy;

trait CreateVacancy
{
    private function createVacancy($overrides = [])
    {
        return Factory::create(Vacancy::class, $overrides);
    }

    private function makeVacancy($override = [])
    {
        return Factory::build(Vacancy::class, $override);
    }
}
