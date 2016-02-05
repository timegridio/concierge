<?php

namespace Timegridio\Concierge;

use Timegridio\Concierge\Models\Business;

class Repository
{
    /**
     * Default lookup identificator.
     *
     * @var string
     */
    private $identificator = 'slug';

    public function getBusiness($id, $identificator = null)
    {
        $identificator = $identificator ?: $this->identificator;

        return Business::where($identificator, $id)->first();
    }

    public function getService($business, $id, $identificator = null)
    {
        $identificator = $identificator ?: $this->identificator;

        return $business->services()->where($identificator, $id)->first();
    }
}
