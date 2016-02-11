<?php

namespace Timegridio\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Timegridio\Concierge\Traits\IsIntoDomain;

class IntoDomainStub extends Model
{
    use IsIntoDomain;
}
