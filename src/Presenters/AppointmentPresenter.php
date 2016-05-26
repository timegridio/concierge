<?php

namespace Timegridio\Concierge\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Timegridio\Concierge\Duration;
use Timegridio\Concierge\Models\Appointment;

class AppointmentPresenter extends BasePresenter
{
    protected $timezone = null;

    public function __construct(Appointment $resource)
    {
        $this->wrappedObject = $resource;

        $this->setTimezone(session()->get('timezone'));
    }

    public function setTimezone($timezone = false)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function timezone()
    {
        if ($this->timezone === null) {
            $this->timezone = $this->wrappedObject->business->timezone;
        }

        return $this->timezone;
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
                    ->timezone($this->timezone)
                    ->format($dateFormat);
    }

    public function time()
    {
        $timeFormat = $this->timeFormat();

        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->timezone)
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
                         ->timezone($this->timezone)
                         ->format($timeFormat);

        $toTime = $this->wrappedObject
                       ->vacancy
                       ->finish_at
                       ->timezone($this->timezone)
                       ->format($timeFormat);

        return ['from' => $fromTime, 'to' => $toTime];
    }

    public function finishTime()
    {
        $timeFormat = $this->timeFormat();

        return $this->wrappedObject
                    ->finish_at
                    ->timezone($this->timezone)
                    ->format($timeFormat);
    }

    public function duration()
    {
        $duration = new Duration(intval($this->wrappedObject->duration()) * 60000);
        $format = [
            'template'  => '{hours} {minutes} {seconds}',
            '{hours}'   => '{hours} hours',
            '{minutes}' => '{minutes} minutes',
            '{seconds}' => '{seconds} seconds',
        ];

        return $duration->format($format);
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
            case Appointment::STATUS_CANCELED:
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

    protected function timeFormat()
    {
        return $this->wrappedObject->business->pref('time_format') ?: 'h:i a';
    }

    protected function dateFormat($defaultFormat = 'Y-m-d')
    {
        return $this->wrappedObject->business->pref('date_format') ?: $defaultFormat;
    }
}
