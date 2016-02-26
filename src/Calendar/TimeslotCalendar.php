<?php

namespace Timegridio\Concierge\Calendar;

class TimeslotCalendar extends BaseCalendar
{
    public function find()
    {
        $this->prepare();

        $results = $this->vacancies->get();

        $fromDatetime = $this->getUTCDateTime();

        if ($this->duration !== null) {
            $toDatetime = $fromDatetime->addMinutes($this->duration);

            $results = $results->reject(function ($vacancy) use ($fromDatetime, $toDatetime) {
                return !$vacancy->hasRoomBetween($fromDatetime, $toDatetime);
            });
        }

        return $results;
    }

    protected function prepare()
    {
        if ($this->service !== null) {
            $this->vacancies->forService($this->service);
        }

        if ($this->date !== null && $this->time !== null) {
            $this->vacancies->forDateTime($this->getUTCDateTime());
        }

        return $this;
    }
}
