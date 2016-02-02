<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Tests\Models\User;

trait CreateBusiness
{
    private function createBusiness($overrides = [])
    {
        return Factory::create(Business::class, $overrides);
    }

    private function createBusinesses($quantity = 2, $overrides = [])
    {
        return Factory::create(Business::class, $quantity, $overrides);
    }

    private function makeBusiness(User $owner, $overrides = [])
    {
        $business = Factory::build(Business::class, $overrides);
        $business->save();
        $business->owners()->attach($owner);

        return $business;
    }
}
