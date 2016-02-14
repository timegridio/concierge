<?php

namespace Timegridio\Concierge\Vacancy;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Vacancy;

class VacancyManager
{
    protected $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    /**
     * Get a Vacancy for a given DateTime and Service combination.
     *
     * @param Carbon  $targetDateTime
     * @param int $serviceId
     *
     * @return Timegridio\Concierge\Models\Vacancy
     */
//    public function getSlotFor(Carbon $targetDateTime, $serviceId)
//    {
//        return $this->business
//            ->vacancies()
//            ->forDateTime($targetDateTime)
//            ->forService($serviceId)
//            ->first();
//    }

    public function publish($date, Carbon $startAt, Carbon $finishAt, $serviceId, $capacity = 1)
    {
        $vacancyKeys = [
            'business_id' => $this->business->id,
            'service_id'  => $serviceId,
            'date'        => $date,
            ];
        $vacancyValues = [
            'capacity'    => intval($capacity),
            'start_at'    => $startAt->timezone('UTC')->toDateTimeString(),
            'finish_at'   => $finishAt->timezone('UTC')->toDateTimeString(),
            ];

        return Vacancy::updateOrCreate($vacancyKeys, $vacancyValues);
    }
}
