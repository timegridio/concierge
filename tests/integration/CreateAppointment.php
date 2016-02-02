<?php

use Laracasts\TestDummy\Factory;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Tests\Models\User;

trait CreateAppointment
{
    private function createAppointment($overrides = [])
    {
        return Factory::create(Appointment::class, $overrides);
    }

    private function makeAppointment(Business $business, User $issuer, Contact $contact, $override = [])
    {
        $appointment = Factory::build(Appointment::class, $override);
        $appointment->contact()->associate($contact);
        $appointment->issuer()->associate($issuer);
        $appointment->business()->associate($business);

        return $appointment;
    }
}
