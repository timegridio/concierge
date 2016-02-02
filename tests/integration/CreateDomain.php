<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Domain;
use Timegridio\Tests\Models\User;

trait CreateDomain
{
    private function createDomain($overrides = [])
    {
        return Factory::create(Domain::class, $overrides);
    }

    private function createDomains($quantity = 2, $overrides = [])
    {
        return Factory::create(Domain::class, $quantity, $overrides);
    }

    private function makeDomain(User $owner, $overrides = [])
    {
        $domain = Factory::build(Domain::class, $overrides);
        $domain->save();
        $domain->owners()->attach($owner);

        return $domain;
    }
}
