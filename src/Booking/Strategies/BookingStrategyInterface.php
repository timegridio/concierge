<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;

interface BookingStrategyInterface
{
    public function generateAppointment(
        $issuerId,
        Business $business,
        Contact $contact,
        Service $service,
        Carbon $datetime,
        $comments = null
    );
}
