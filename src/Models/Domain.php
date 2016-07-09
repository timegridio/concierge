<?php

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $slug
 * @property int $owner_id
 * @property mixed $owner
 * @property int $business_id
 * @property Timegridio\Concierge\Models\Business $business
 */
class Domain extends EloquentModel
{
    use SoftDeletes;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = ['slug', 'owner_id'];

    /**
     * Guarded attributes.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Has many businesses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businesses()
    {
        return $this->owner->businesses();
    }

    public function owner()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'owner_id');
    }
}
