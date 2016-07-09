<?php

namespace Timegridio\Concierge\Vacancy;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Vacancy;

class VacancyManager
{
    protected $business;

    protected $builder;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function builder()
    {
        if ($this->builder === null) {
            $this->builder = new VacancyTemplateBuilder();
        }

        return $this->builder;
    }

    /**
     * Update vacancies from batch statements.
     *
     * @param Business $business
     * @param array    $parsedStatements
     *
     * @return bool
     */
    public function updateBatch(Business $business, $parsedStatements)
    {
        $changed = false;
        $dates = $this->arrayGroupBy('date', $parsedStatements);

        foreach ($dates as $date => $statements) {
            $services = $this->arrayGroupBy('service', $statements);

            $changed |= $this->processServiceStatements($business, $date, $services);
        }

        return $changed;
    }

    public function unpublish()
    {
        return $this->business->vacancies()->delete();
    }

    protected function processServiceStatements($business, $date, $services)
    {
        $changed = false;
        foreach ($services as $serviceSlug => $statements) {
            $service = $business->services()->where('slug', $serviceSlug)->get()->first();

            if ($service === null) {

                //  Invalid services are skipped to avoid user frustration.
                //  TODO: Still, a user-level WARNING should be raised with no fatal error

                continue;
            }

            $vacancy = $business->vacancies()->forDate(Carbon::parse($date))->forService($service->id);

            if ($vacancy) {
                $vacancy->delete();
            }

            $changed |= $this->processStatements($business, $date, $service, $statements);
        }

        return $changed;
    }

    protected function processStatements($business, $date, $service, $statements)
    {
        $changed = false;
        foreach ($statements as $statement) {
            $changed |= $this->publishVacancy($business, $date, $service, $statement);
        }

        return $changed;
    }

    protected function publishVacancy($business, $date, $service, $statement)
    {
        $date = $statement['date'];
        $startAt = $statement['startAt'];
        $finishAt = $statement['finishAt'];

        $startAt = Carbon::parse("{$date} {$startAt} {$business->timezone}")->timezone('UTC');
        $finishAt = Carbon::parse("{$date} {$finishAt} {$business->timezone}")->timezone('UTC');

        $vacancyValues = [
            'business_id' => $business->id,
            'service_id'  => $service->id,
            'date'        => $statement['date'],
            'start_at'    => $startAt,
            'finish_at'   => $finishAt,
            ];

        // If capacity is a slug, grab the humanresource
        if(!is_numeric($statement['capacity']))
        {
            $vacancyValues['humanresource_id'] = $business->humanresources()
                                                          ->where('slug', $statement['capacity'])
                                                          ->first()
                                                          ->id;
        }
        else
        {
            $vacancyValues['capacity'] = intval($statement['capacity']);
        }

        $vacancy = Vacancy::create($vacancyValues);

        return $vacancy !== null;
    }

    protected function arrayGroupBy($key, $array)
    {
        $grouped = [];
        foreach ($array as $hash => $item) {
            if (!array_key_exists($item[$key], $grouped)) {
                $grouped[$item[$key]] = [];
            }
            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

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

    /**
     * [generateAvailability description].
     *
     * @param Illuminate\Database\Eloquent\Collection $vacancies
     * @param string                                  $startDate
     * @param int                                     $futureDays
     *
     * @return array
     */
    public function generateAvailability($startDate = 'today', $futureDays = 10)
    {
        $dates = [];
        for ($i = 0; $i < $futureDays; $i++) {
            $dates[date('Y-m-d', strtotime("$startDate +$i days"))] = [];
        }

        foreach ($this->business->vacancies as $vacancy) {
            if (array_key_exists($vacancy->date, $dates)) {
                $dates[$vacancy->date][$vacancy->service->slug] = $vacancy;
            }
        }

        return $dates;
    }
}
