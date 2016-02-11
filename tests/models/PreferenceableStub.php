<?php

namespace Timegridio\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Timegridio\Concierge\Traits\Preferenceable;

class PreferenceableStub extends Model
{
    use Preferenceable;

    protected $fillable = ['id'];
}
