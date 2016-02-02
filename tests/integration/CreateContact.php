<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Tests\Models\User;

trait CreateContact
{
    private function createContact($overrides = [])
    {
        return Factory::create(Contact::class, $overrides);
    }

    private function makeContact(User $user = null, $overrides = [])
    {
        $contact = Factory::build(Contact::class, $overrides);
        if ($user) {
            $contact->user()->associate($user);
        }

        return $contact;
    }
}
