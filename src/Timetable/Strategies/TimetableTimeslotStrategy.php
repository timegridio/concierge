<?php

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableTimeslotStrategy extends BaseTimetableStrategy implements TimetableStrategyInterface
{
    private $interval = 30;

    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    protected function initTimetable($starting, $days)
    {
        $this->timetable
             ->interval($this->interval)
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
            $this->updateTimeslots($vacancy, $this->interval);
        }

        return $this->timetable->get();
    }

    protected function updateTimeslots(Vacancy $vacancy, $step = 30)
    {
        $fromTime = $vacancy->start_at->timezone('UTC');
        $toTime = $fromTime->copy();
        $limit = $vacancy->finish_at;

        while ($fromTime <= $limit) {
            $toTime->addMinutes($step);

            $capacity = $vacancy->getAvailableCapacityBetween($fromTime, $toTime);

            $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

            $this->timetable->capacity($vacancy->date, $time, $vacancy->service->slug, $capacity);

            $fromTime->addMinutes($step);
        }
    }
}
