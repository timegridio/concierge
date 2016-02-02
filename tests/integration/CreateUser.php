<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Tests\Models\User;

trait CreateUser
{
    private function createUser($overrides = [])
    {
        # return factory(User::class)->create($overrides);
        return Factory::create('Timegridio\Tests\Models\User', $overrides);
    }

    private function makeUser($overrides = [])
    {
        # $user = factory(User::class)->make($overrides);
        $user = Factory::build('Timegridio\Tests\Models\User', $overrides);
        $user->email = 'guest@example.org';

        return $user;
    }
}
