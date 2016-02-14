<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
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

    public function takeReservation(array $request)
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
            // TODO: Log failure feedback message / raise exception
            return false;
        }

//      DEBUG / INCONSISTENT DB RECORDS CHECK
//        if ($vacancies->count() > 1) {
//            // Log unexpected behavior message / raise exception
//            $vacancy = $vacancies->first();
//        }

        if ($vacancies->count() == 1) {
            $vacancy = $vacancies->first();
        }

        $startAt = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);
        $finishAt = $startAt->copy()->addMinutes($service->duration);

        $appointment = $this->generateAppointment(
            $issuer,
            $this->business->id,
            $contact->id,
            $service->id,
            $startAt,
            $finishAt,
            $comments
        );

        /* Should be moved inside generateAppointment() */
        if ($appointment->duplicates()) {
            throw new DuplicatedAppointmentException();
        }

        /* Should be moved inside generateAppointment() */
        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        return $appointment;
    }

    protected function generateAppointment(
        $issuerId,
        $businessId,
        $contactId,
        $serviceId,
        Carbon $startAt,
        Carbon $finishAt,
        $comments = null)
    {
        $appointment = new Appointment();

        $appointment->doReserve();
        $appointment->setStartAtAttribute($startAt);
        $appointment->setFinishAtAttribute($finishAt);
        $appointment->business()->associate($businessId);
        $appointment->issuer()->associate($issuerId);
        $appointment->contact()->associate($contactId);
        $appointment->service()->associate($serviceId);
        $appointment->comments = $comments;
        $appointment->doHash();

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
