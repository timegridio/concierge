<?php

namespace Timegridio\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Timegridio\Concierge\Traits\OwnsBusinesses;

class BusinessOwnerStub extends Model
{
    use OwnsBusinesses;
}
