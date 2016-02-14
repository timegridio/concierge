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

        $this->timezone($this->business->timezone);

        return $this;
    }

    public function timezone($timezone = null)
    {
        $this->timezone = ($timezone !== null) ?: $this->business->timezone;

        return $this;
    }
}
