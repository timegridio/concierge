<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;
use Timegridio\Concierge\Calendar\VacancyCalendar;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

/*******************************************************************************
 * Concierge Service Layer
 *     High level booking manager
 ******************************************************************************/
class Concierge extends Workspace
{
    protected $strategy = null;

    protected $vacancyCalendar = null;

    protected function strategy()
    {
        if ($this->strategy === null) {
            $this->strategy = new BookingStrategy($this->business->strategy);
        }

        return $this->strategy;
    }

    protected function vacancyCalendar()
    {
        if ($this->vacancyCalendar === null) {
            $this->vacancyCalendar = new VacancyCalendar($this->strategy, $this->business->vacancies());
        }

        return $this->vacancyCalendar;
    }

    public function takeReservation($request)
    {
        //        $datetime = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);
//
//        $appointment = $this->strategy()->generateAppointment(
//            $request['issuer'],
//            $this->business,
//            $request['contact'],
//            $request['service'],
//            $datetime,
//            $request['comments']
//        );
//
//        if ($appointment->duplicates()) {
//            return $appointment;
//            // throw new \Exception('Duplicated Appointment Attempted');
//        }
//
//        $vacancy = $this->vacancyCalendar()->getSlotFor($appointment->start_at, $appointment->finish_at, $appointment->service->id);
//
//        if ($vacancy != null) {
//            $appointment->vacancy()->associate($vacancy);
//            $appointment->save();
//
//            return $appointment;
//        }
//
//        return false;
    }

    protected function makeDateTimeUTC($date, $time, $timezone = null)
    {
        if ($timezone === null) {
            $timezone = $this->business->timezone;
        }

        return Carbon::parse("{$date} {$time} {$timezone}")->timezone('UTC');
    }
}
