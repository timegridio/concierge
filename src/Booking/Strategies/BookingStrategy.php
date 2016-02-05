<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Booking\Timetable;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class BookingStrategy
{
    protected $strategy = null;

    public function __construct($strategyId)
    {
        info("BookingStrategy: Using {$strategyId}");
        switch ($strategyId) {
            case 'timeslot':
                $this->strategy = new BookingTimeslotStrategy(new Timetable());
                break;
            case 'dateslot':
                $this->strategy = new BookingDateslotStrategy(new Timetable());
                break;
            default:
                logger("BookingStrategy: __construct: Illegal strategy:{$strategyId}");
                break;
        }
    }

    public function generateAppointment(
        $issuerId,
        Business $business,
        Contact $contact,
        Service $service,
        Carbon $datetime,
        $comments = null
    ) {
        return $this->strategy->generateAppointment($issuerId, $business, $contact, $service, $datetime, $comments);
    }

    public function hasRoom(Appointment $appointment, Vacancy $vacancy)
    {
        return $this->strategy->hasRoom($appointment, $vacancy);
    }

    public function removeBookedVacancies(Collection $vacancies)
    {
        return $this->strategy->removeBookedVacancies($vacancies);
    }

    public function removeSelfBooked(Collection $vacancies, $userId)
    {
        return $this->strategy->removeSelfBooked($vacancies, $userId);
    }

    public function buildTimetable($vacancies, $starting = 'today', $days = 1)
    {
        return $this->strategy->buildTimetable($vacancies, $starting, $days);
    }
}
