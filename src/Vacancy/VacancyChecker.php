<?php

namespace Timegridio\Concierge\Vacancy;

use Timegridio\Concierge\Booking\Strategies\BookingStrategy;

class VacancyChecker
{
    protected $business;

    protected $strategy;

    public function __construct()
    {
        // $this->setBusiness($business);
    }

    public function setStrategy($strategy)
    {
        $this->strategy = new BookingStrategy($strategy);

        return $this;
    }

    public function isAvailable($userId)
    {
        $vacancies = $this->strategy->removeBookedVacancies($this->vacancies);
        $vacancies = $this->strategy->removeSelfBooked($vacancies, $userId);

        return !$vacancies->isEmpty();
    }

    public function getVacanciesFor($userId, $starting = 'today', $limit = 7)
    {
        $vacancies = $this->strategy->removeBookedVacancies($this->vacancies);
        $vacancies = $this->strategy->removeSelfBooked($vacancies, $userId);

        return $this->generateAvailability($vacancies, $starting, $limit);
    }
}
