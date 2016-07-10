<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\BookingManager;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Timetable\Strategies\TimetableStrategy;
use Timegridio\Concierge\Vacancy\VacancyManager;

/*******************************************************************************
 * Concierge Service Layer
 *     High level booking manager
 ******************************************************************************/
class Concierge extends Workspace
{
    protected $timetable = null;

    protected $calendar = null;

    protected $booking = null;

    protected $vacancies = null;

    protected $appointment = null;

    protected function calendar()
    {
        if ($this->calendar === null) {
            $this->calendar = new Calendar($this->business->strategy, $this->business->vacancies(), $this->business->timezone);
        }

        return $this->calendar;
    }

    public function timetable()
    {
        if ($this->timetable === null) {
            $this->timetable = new TimetableStrategy($this->business->strategy);
        }

        return $this->timetable;
    }

    public function vacancies()
    {
        if ($this->vacancies === null && $this->business !== null) {
            $this->vacancies = new VacancyManager($this->business);
        }

        return $this->vacancies;
    }

    public function booking()
    {
        if ($this->booking === null && $this->business !== null) {
            $this->booking = new BookingManager($this->business);
        }

        return $this->booking;
    }

    public function takeReservation(array $request)
    {
        $issuer = $request['issuer'];
        $service = $request['service'];
        $contact = $request['contact'];
        $comments = $request['comments'];

        $vacancies = $this->calendar()
                          ->forService($service->id)
                          ->withDuration($service->duration)
                          ->forDate($request['date'])
                          ->atTime($request['time'], $request['timezone'])
                          ->find();

        if ($vacancies->count() == 0) {
            // TODO: Log failure feedback message / raise exception
            return false;
        }

        if ($vacancies->count() > 1) {
            // Log unexpected behavior message / raise exception
            $vacancy = $vacancies->first();
        }

        if ($vacancies->count() == 1) {
            $vacancy = $vacancies->first();
        }

        $humanresourceId = $vacancy->humanresource ? $vacancy->humanresource->id : null;

        $startAt = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);
        $finishAt = $startAt->copy()->addMinutes($service->duration);

        $appointment = $this->generateAppointment(
            $issuer,
            $this->business->id,
            $contact->id,
            $service->id,
            $startAt,
            $finishAt,
            $comments,
            $humanresourceId
        );

        /* Should be moved inside generateAppointment() */
        if ($appointment->duplicates()) {
            $this->appointment = $appointment;

            throw new DuplicatedAppointmentException($appointment->code);
        }

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
        $comments = null,
        $humanresourceId = null)
    {
        $appointment = new Appointment();

        $appointment->doReserve();
        $appointment->setStartAtAttribute($startAt);
        $appointment->setFinishAtAttribute($finishAt);
        $appointment->business()->associate($businessId);
        $appointment->issuer()->associate($issuerId);
        $appointment->contact()->associate($contactId);
        $appointment->service()->associate($serviceId);
        $appointment->humanresource()->associate($humanresourceId);
        $appointment->comments = $comments;
        $appointment->doHash();

        return $appointment;
    }

    /**
     * Determine if the Business has any published Vacancies available for booking.
     *
     * @param string $fromDate
     * @param int $days
     *
     * @return bool
     */
    public function isBookable($fromDate = 'now', $days = 7)
    {
        $fromDate = Carbon::parse($fromDate)->timezone($this->business->timezone);

        $count = $this->business
                      ->vacancies()
                      ->future($fromDate)
                      ->until($fromDate->addDays($days))
                      ->count();

        return $count > 0;
    }

    //////////////////
    // FOR REFACTOR //
    //////////////////

    public function getActiveAppointments()
    {
        return $this->business
            ->bookings()->with('contact')
            ->with('business')
            ->with('service')
            ->active()
            ->orderBy('start_at')
            ->get();
    }

    public function getUnservedAppointments()
    {
        return $this->business
            ->bookings()->with('contact')
            ->with('business')
            ->with('service')
            ->unserved()
            ->orderBy('start_at')
            ->get();
    }

    public function getUnarchivedAppointments()
    {
        return $this->business
            ->bookings()->with('contact')
            ->with('business')
            ->with('service')
            ->unarchived()
            ->orderBy('start_at')
            ->get();
    }

    protected function makeDateTime($date, $time, $timezone = null)
    {
        return Carbon::parse("{$date} {$time} {$timezone}");
    }

    protected function makeDateTimeUTC($date, $time, $timezone = null)
    {
        return $this->makeDateTime($date, $time, $timezone)->timezone('UTC');
    }

    public function appointment()
    {
        return $this->appointment;
    }
}
