<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

/*******************************************************************************
 * Concierge Service Layer
 *     High level booking manager
 ******************************************************************************/
class Concierge extends Workspace
{
    protected $calendar = null;

    protected $booking = null;

    protected function calendar()
    {
        if ($this->calendar === null) {
            $this->calendar = new Calendar($this->business->strategy, $this->business->vacancies(), $this->business->timezone);
        }

        return $this->calendar;
    }

    protected function booking()
    {
        if ($this->booking === null) {
            $this->booking = new BookingStrategy($this->business->strategy);
        }

        return $this->booking;
    }

    public function takeReservation($request)
    {
        $issuer = $request['issuer'];
        $service = $request['service'];
        $contact = $request['contact'];
        $comments = $request['comments'];

        $vacancies = $this->calendar()
                          ->forService($service->id)
                          ->forDate($request['date'])
                          ->atTime($request['time'])
                          ->find();

        if ($vacancies->count() == 0) {
            // Log failure feedback message
            return false;
        }

        $datetime = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);

        $appointment = $this->booking()->generateAppointment(
            $issuer,
            $this->business,
            $contact,
            $service,
            $datetime,
            $comments
        );

        /* Should be moved inside generateAppointment() */
        if ($appointment->duplicates()) {
            // Throw Exception('Duplicated Appointment Attempted')
            return false;
        }

        /* Should be moved inside generateAppointment() */
        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        return $appointment;
    }

    protected function makeDateTime($date, $time, $timezone = null)
    {
        return Carbon::parse("{$date} {$time} {$timezone}");
    }

    protected function makeDateTimeUTC($date, $time, $timezone = null)
    {
        return $this->makeDateTime($date, $time, $timezone)->timezone('UTC');
    }
}
