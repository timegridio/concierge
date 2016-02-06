<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;
use Timegridio\Concierge\Models\Vacancy;

class TimetableDateslotStrategy implements TimetableStrategyInterface
{
    private $timetable;

    private $interval = 30;

    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
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
            $this->updateTimeslots($vacancy);
        }

        return $this->timetable->get();
    }

    protected function updateTimeslots(Vacancy $vacancy)
    {
        $fromTime = $vacancy->start_at;
        $toTime = $vacancy->finish_at;

        $capacity = $vacancy->getAvailableCapacityBetween($fromTime->timezone('UTC'), $toTime->timezone('UTC'));

        $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

        $this->timetable->capacity($vacancy->date, $time, $vacancy->service->slug, $capacity);
    }
}
