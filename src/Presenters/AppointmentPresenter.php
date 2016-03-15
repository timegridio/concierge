<?php

namespace Timegridio\Concierge\Presenters;

use JsonLd\Context;
use McCool\LaravelAutoPresenter\BasePresenter;
use Timegridio\Concierge\Models\Appointment;

class AppointmentPresenter extends BasePresenter
{
    public function __construct(Appointment $resource)
    {
        $this->wrappedObject = $resource;
    }

    public function code()
    {
        $length = $this->wrappedObject->business->pref('appointment_code_length');

        return strtoupper(substr($this->wrappedObject->hash, 0, $length));
    }

    public function date($format = 'Y-m-d')
    {
        // Translated text for friendly date should not be resposibility of this class

        // if ($this->wrappedObject->start_at->isToday()) {
        //     return studly_case(trans('Concierge::appointments.text.today'));
        // }

        // if ($this->wrappedObject->start_at->isTomorrow()) {
        //     return studly_case(trans('Concierge::appointments.text.tomorrow'));
        // }

        $dateFormat = $this->dateFormat($format);

        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format($dateFormat);
    }

    public function time()
    {
        $timeFormat = $this->timeFormat();

        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format($timeFormat);
    }

    public function arriveAt()
    {
        $timeFormat = $this->timeFormat();

        if (!$this->wrappedObject->business->pref('appointment_flexible_arrival')) {
            return ['at' => $this->time];
        }

        $fromTime = $this->wrappedObject
                         ->vacancy
                         ->start_at
                         ->timezone($this->wrappedObject->business->timezone)
                         ->format($timeFormat);

        $toTime = $this->wrappedObject
                       ->vacancy
                       ->finish_at
                       ->timezone($this->wrappedObject->business->timezone)
                       ->format($timeFormat);

        return ['from' => $fromTime, 'to' => $toTime];
    }

    public function finishTime()
    {
        $timeFormat = $this->timeFormat();

        return $this->wrappedObject
                    ->finish_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format($timeFormat);
    }

    public function duration()
    {
        return $this->wrappedObject->duration() . '&prime;';
    }

    public function phone()
    {
        return $this->wrappedObject->business->phone;
    }

    public function location()
    {
        return $this->wrappedObject->business->postal_address;
    }

    public function statusLetter()
    {
        return substr(trans('appointments.status.'.$this->wrappedObject->statusLabel), 0, 1);
    }

    public function status()
    {
        return trans('appointments.status.'.$this->wrappedObject->statusLabel);
    }

    public function statusToCssClass()
    {
        switch ($this->wrappedObject->status) {
            case Appointment::STATUS_ANNULATED:
                return 'danger';
                break;
            case Appointment::STATUS_CONFIRMED:
                return 'success';
                break;
            case Appointment::STATUS_RESERVED:
                return 'warning';
                break;
            case Appointment::STATUS_SERVED:
            default:
                return 'default';
        }
    }

    public function panel()
    {
        return view('widgets.appointment.panel._body', ['appointment' => $this, 'user' => auth()->user()])->render();
    }

    public function row()
    {
        return view('widgets.appointment.row._body', ['appointment' => $this, 'user' => auth()->user()])->render();
    }

    public function jsonLd()
    {
        $attributes = [
            'name'         => $this->wrappedObject->business->name,
            'description'  => $this->wrappedObject->service->description,
            'telephone'    => $this->wrappedObject->business->phone,
            'startDate'    => $this->wrappedObject->start_at->toIso8601String(),
            'url'          => '#',
            'location'     => [
                'name'    => $this->wrappedObject->business->name,
                'address' => [
                    'streetAddress'   => $this->wrappedObject->business->postal_address,
                ],
            ],
        ];

        return Context::create('event', $attributes);
    }

    protected function timeFormat()
    {
        return $this->wrappedObject->business->pref('time_format') ?: 'h:i a';
    }

    protected function dateFormat($defaultFormat = 'Y-m-d')
    {
        return $this->wrappedObject->business->pref('date_format') ?: $defaultFormat;
    }
}
