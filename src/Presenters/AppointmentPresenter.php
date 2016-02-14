<?php

namespace Timegridio\Concierge\Presenters;

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
        if ($this->wrappedObject->start_at->isToday()) {
            return studly_case(trans('Concierge::appointments.text.today'));
        }

        if ($this->wrappedObject->start_at->isTomorrow()) {
            return studly_case(trans('Concierge::appointments.text.tomorrow'));
        }

        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format($format);
    }

    public function phone()
    {
        return $this->wrappedObject->business->phone;
    }

    public function location()
    {
        return $this->wrappedObject->business->postal_address;
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
}
