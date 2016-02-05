<?php

namespace Timegridio\Concierge\Calendar;

class DateslotCalendar extends BaseCalendar
{
    public function find()
    {
        $this->prepare();

        $results = $this->vacancies->get();

        $results = $results->reject(function ($vacancy) {

            return !$vacancy->hasRoom();
        });

        return $results;
    }

    protected function prepare()
    {
        if ($this->service !== null) {
            $this->vacancies->forService($this->service);
        }

        if ($this->date !== null) {
            $this->vacancies->forDate($this->date());
        }

        return $this;
    }
}
