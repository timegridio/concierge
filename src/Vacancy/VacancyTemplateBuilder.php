<?php

namespace Timegridio\Concierge\Vacancy;

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class VacancyTemplateBuilder
{
    protected $statement = [];

    public function getTemplate(Business $business, Service $service)
    {
        $this->addServiceStatement($service);
        $this->addDaysStatement();
        $this->addTimeRangeStatement($business->pref('start_at'), $business->pref('finish_at'));

        return $this->build();
    }

    protected function addServiceStatement(Service $service)
    {
        $this->addStatement($service->slug.':1');
    }

    protected function addDaysStatement()
    {
        $this->addStatement(' mon, tue, wed, thu, fri, sat');
    }

    protected function addTimeRangeStatement($startAt, $finishAt)
    {
        $this->addStatement('  '.$startAt.' - '.$finishAt);
    }

    protected function addStatement($statement)
    {
        $this->statement[] = $statement;
    }

    protected function build()
    {
        return implode("\n", $this->statement);
    }
}
