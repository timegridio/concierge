<?php

namespace Timegridio\Concierge;

use Timegridio\Concierge\Models\Business;

class Workspace
{
    protected $business;

    protected $timezone = null;

    public function business($business)
    {
        $this->business = $business;

        return $this;
    }

    public function timezone($timezone = null)
    {
        if ($timezone == null) {
            return $this->timezone = $this->business->timezone;
        }

        return $this->timezone = $timezone;
    }
}
