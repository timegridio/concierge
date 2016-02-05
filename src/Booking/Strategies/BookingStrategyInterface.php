<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

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

    public function removeBookedVacancies(Collection $vacancies);

    public function removeSelfBooked(Collection $vacancies, $userId);
}
