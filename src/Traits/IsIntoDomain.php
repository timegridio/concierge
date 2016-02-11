<?php

namespace Timegridio\Concierge\Traits;

use Timegridio\Concierge\Models\Domain;

trait IsIntoDomain
{
    /**
     * Belongs to a Domain relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
