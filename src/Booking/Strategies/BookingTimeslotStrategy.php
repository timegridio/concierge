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

class BookingTimeslotStrategy implements BookingStrategyInterface
{
    private $timetable;

    private $interval = 30;

    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    public function generateAppointment(
        $issuerId,
        Business $business,
        Contact $contact,
        Service $service,
        Carbon $datetime,
        $comments = null
    ) {
        $appointment = new Appointment();

        $appointment->doReserve();
        $appointment->setStartAtAttribute($datetime);
        $appointment->setFinishAtAttribute($datetime->copy()->addMinutes($service->duration));
        $appointment->duration = $service->duration;
        $appointment->business()->associate($business);
        $appointment->issuer()->associate($issuerId);
        $appointment->contact()->associate($contact);
        $appointment->service()->associate($service);
        $appointment->comments = $comments;
        $appointment->doHash();

        return $appointment;
    }

    /**
     * [removeBookedVacancies description].
     *
     * @param Collection $vacancies
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function removeBookedVacancies(Collection $vacancies)
    {
        //$vacancies = $vacancies->reject(function ($vacancy) {
        //    return $vacancy->isFull();
        //});

        return $vacancies;
    }

    /**
     * [removeBookedVacancies description].
     *
     * @param Collection $vacancies
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function removeSelfBooked(Collection $vacancies, $userId)
    {
        //$vacancies = $vacancies->reject(function ($vacancy) use ($user) {
        //    return $vacancy->isHoldingAnyFor($user);
        //});

        return $vacancies;
    }

    /**
     * Build timetable.
     *
     * @param Illuminate\Database\Eloquent\Collection $vacancies
     * @param string                                  $starting
     * @param int                                     $days
     *
     * @return array
     */
    public function buildTimetable($vacancies, $starting = 'today', $days = 1)
    {
        $this->timetable
             ->interval($this->interval)
             ->format('date.service.time')
             ->from($starting)
             ->future($days)
             ->init();

        foreach ($vacancies as $vacancy) {
            $this->updateTimeslots($vacancy, $this->interval);
        }

        return $this->timetable->get();
    }

    protected function updateTimeslots(Vacancy $vacancy, $step = 30)
    {
        $fromTime = $vacancy->start_at;
        $toTime = $fromTime->copy();
        $limit = $vacancy->finish_at;

        while ($fromTime <= $limit) {
            $toTime->addMinutes($step);

            $capacity = $vacancy->getAvailableCapacityBetween($fromTime->timezone('UTC'), $toTime->timezone('UTC'));

            $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

            $this->timetable->capacity($vacancy->date, $time, $vacancy->service->slug, $capacity);

            $fromTime->addMinutes($step);
        }
    }
}
