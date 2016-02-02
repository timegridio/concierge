<?php

namespace Timegridio\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Timegridio\Concierge\Traits\OwnsBusinesses;

class User extends Model
{
    use OwnsBusinesses;

    protected $fillable = ['id'];
}
