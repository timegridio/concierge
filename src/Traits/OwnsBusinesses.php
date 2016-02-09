<?php

namespace Timegridio\Concierge\Traits;

use Timegridio\Concierge\Models\Business;

trait OwnsBusinesses
{
    /**
     * Owns Businesses relationship.
     *
     * @return \Illuminate\Database\Eloquent\belongsToMany
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }
}
