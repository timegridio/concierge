<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Timegridio\Concierge\Booking\Strategies\BookingStrategy;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\VacancyManager;

/*******************************************************************************
 * Concierge Service Layer
 *     High level booking manager
 ******************************************************************************/
class Concierge
{
    protected $business = null;

    protected $strategy = null;

    protected $vacancyManager = null;

    public function business($business)
    {
        $this->business = $business;

        return $this;
    }

    protected function strategy()
    {
        if ($this->strategy === null) {
            $this->strategy = new BookingStrategy($this->business->strategy);
        }

        return $this->strategy;
    }

    protected function vacancyManager()
    {
        if($this->vacancyManager === null)
        {
            $this->vacancyManager = new VacancyManager();
        }

        $this->vacancyManager->setBusiness($this->business);

        return $this->vacancyManager;
    }

    public function takeReservation($request)
    {
        $datetime = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);

        $appointment = $this->strategy()->generateAppointment(
            $request['issuer'],
            $this->business,
            $request['contact'],
            $request['service'],
            $datetime,
            $request['comments']
        );

        if ($appointment->duplicates()) {
            return $appointment;
            // throw new \Exception('Duplicated Appointment Attempted');
        }

        $vacancy = $this->vacancyManager()->getSlotFor($appointment->start_at, $appointment->service->id);

        if ($vacancy != null && $this->strategy()->hasRoom($appointment, $vacancy)) {
            $appointment->vacancy()->associate($vacancy);
            $appointment->save();
            return $appointment;
        }
        return false;
    }

    protected function makeDateTimeUTC($date, $time, $timezone = null)
    {
        if($timezone === null)
        {
            $timezone = $this->business->timezone;
        }

        return Carbon::parse("{$date} {$time} {$timezone}")->timezone('UTC');
    }
}
