<?php

namespace Timegridio\Concierge\Booking\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Booking\Timetable;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class BookingTimeslotStrategy implements BookingStrategyInterface
{
    private $timetable;

    private $interval = 30;

    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    public function generateAppointment(
        $issuerId,
        Business $business,
        Contact $contact,
        Service $service,
        Carbon $datetime,
        $comments = null
    ) {
        $appointment = new Appointment();

        $appointment->doReserve();
        $appointment->setStartAtAttribute($datetime);
        $appointment->setFinishAtAttribute($datetime->copy()->addMinutes($service->duration));
        $appointment->duration = $service->duration;
        $appointment->business()->associate($business);
        $appointment->issuer()->associate($issuerId);
        $appointment->contact()->associate($contact);
        $appointment->service()->associate($service);
        $appointment->comments = $comments;
        $appointment->doHash();

        return $appointment;
    }

    public function hasRoom(Appointment $appointment, Vacancy $vacancy)
    {
        return $vacancy->hasRoomBetween($appointment->start_at, $appointment->finish_at);
    }

    /**
     * [removeBookedVacancies description].
     *
     * @param Collection $vacancies
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function removeBookedVacancies(Collection $vacancies)
    {
        //$vacancies = $vacancies->reject(function ($vacancy) {
        //    return $vacancy->isFull();
        //});

        return $vacancies;
    }

    /**
     * [removeBookedVacancies description].
     *
     * @param Collection $vacancies
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function removeSelfBooked(Collection $vacancies, $userId)
    {
        //$vacancies = $vacancies->reject(function ($vacancy) use ($user) {
        //    return $vacancy->isHoldingAnyFor($user);
        //});

        return $vacancies;
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
             ->services(['default'])
             ->format('date.service.time')
             ->from($starting)
             ->future($days)
             ->init();

        foreach($vacancies as $vacancy)
        {
            $this->chunkTimeslots($vacancy, 30);
        }

        return $this->timetable->get();
    }

    protected function chunkTimeslots(Vacancy $vacancy)
    {
        $step = $this->interval;

        $times = [];
        
        $fromTime = $vacancy->start_at;
        $toTime = $fromTime->copy()->addMinutes($step);
        $limit = $vacancy->finish_at;

        while ($fromTime <= $limit) {

            $capacity = $vacancy->getAvailableCapacityBetween($fromTime->timezone('UTC'), $toTime->timezone('UTC'));
            
            $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

            $this->timetable->capacity($vacancy->date, $time, $vacancy->service->slug, $capacity);

            $toTime->addMinutes($step);
            $fromTime->addMinutes($step);
        }
        return $times;
    }


//    protected function chunkTimeslots(Vacancy $vacancy)
//    {
//        $step = $this->interval;
//
//        $times = [];
//        
//        $startTime = $vacancy->start_at->timezone($vacancy->business->timezone)->toTimeString();
//        $startKey = date('Y-m-d H:i', strtotime("{$vacancy->date} {$startTime}")).' '.$vacancy->business->timezone;
//        
//        $finishTime = $vacancy->finish_at->timezone($vacancy->business->timezone)->toTimeString();
//        $endKey = date('Y-m-d H:i', strtotime("{$vacancy->date} {$finishTime}")).' '.$vacancy->business->timezone;
//        
//        $fromTime = Carbon::parse($startKey);
//        $toTime = $fromTime->copy()->addMinutes($step);
//        $limit = Carbon::parse($endKey);
//
//        while ($fromTime <= $limit) {
//
//            $time = $fromTime->format('H:i:s');
//            
//            $capacity = $vacancy->getAvailableCapacityBetween($fromTime->timezone('UTC'), $toTime->timezone('UTC'));
//
//            $this->timetable->capacity($vacancy->date, $time, $vacancy->service->slug, $capacity);
//            
//            echo $toTime->toDateTimeString()."\n";
//
//            $toTime->addMinutes($step);
//            $fromTime->addMinutes($step);
//        }
//        return $times;
//    }
//
    protected function templateTimeslots()
    {
        $times = [];

        for ($i = 12; $i < 40; $i++) {
            $minutes = 30 * $i;
            $times[date('H:i', strtotime("today midnight +$minutes minutes"))] = 0;
        }

        return $times;
    }

    protected function arrayKeySum(array &$array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (array_key_exists($key, $array1)) {
                $array1[$key] += $array2[$key];
            }
        }
    }
}
