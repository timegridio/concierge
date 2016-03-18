<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableDateslotStrategy extends BaseTimetableStrategy implements TimetableStrategyInterface
{
    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    protected function initTimetable($starting, $days)
    {
        $this->timetable
             ->format('date.service.time')
             ->from($starting)
             ->future($days)
             ->init();
    }

    /**
     * Build timetable.
     *
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @param string                                  $starting
     * @param int                                     $days
     *
     * @return array
     */
    public function buildTimetable($vacancies, $starting = 'today', $days = 1)
    {
        $this->initTimetable($starting, $days);

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
