<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;

/*******************************************************************************
 * Concierge Service Layer
 *     High level booking manager
 ******************************************************************************/
class Concierge
{
    protected $userId;

    protected $business;

    protected $contact;

    protected $service;

    protected $datetime;

    protected $timezone;

    protected $comments;

    protected $calendar;

    public function takeReservation()
    {
    }
}
