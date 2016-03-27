<?php

namespace Timegridio\Concierge\Booking;

use Timegridio\Concierge\Models\Business;

class BookingManager
{
    protected $business;

    protected $appointment;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function appointment($hash)
    {
        $this->appointment = $this->business->bookings()->where('hash', 'like', $hash.'%')->get()->first();

        return $this;
    }

    public function cancel()
    {
        return $this->appointment->doCancel();
    }

    public function confirm()
    {
        return $this->appointment->doConfirm();
    }

    public function serve()
    {
        return $this->appointment->doServe();
    }
}
