<?php

namespace Timegridio\Concierge\Calendar;

class VacancyCalendar extends ServiceCalendar
{
//    private $strategy = null;
//    
//    private $vacancies = null;
//
//    public function __construct($strategy, $timezone, $vacancies)
//    {
//        $this->strategy = $strategy;
//    
//        $this->timezone($timezone);
//    
//        $this->vacancies = $vacancies;
//    }
//
//    protected $find = null;
//
//    /////////////////////
//    // Vacancy Queries //
//    /////////////////////
//
//    public function forServiceAndDateTime($serviceId, $datetime = null, $timezone = null)
//    {
//        $this->find = $this->filtered()->forDateTime($datetime)->forService($serviceId);
//
//        return $this;
//    }
//
//    public function forDateTimeUntil($fromDatetime = null, $toDatetime = null, $timezone = null)
//    {
//        $this->find = $this->filtered()->forDateTime($datetime);
//
//        return $this;
//    }
//
//    public function forDateTime($datetime = null, $timezone = null)
//    {
//        if ($datetime === null && $this->datetime !== null) {
//            $datetime = $this->datetime;
//        }
//
//        $this->find = $this->filtered()->forDateTime($datetime);
//
//        return $this;
//    }
//
//    public function find()
//    {
//        return $this->filtered()->first();
//    }
//
//    public function filtered()
//    {
//        if ($this->find === null) {
//            $this->find = $this->vacancies->with('appointments');
//        }
//        parent::filtered();
//    }
}
