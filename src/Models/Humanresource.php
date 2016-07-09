<?php

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property string $slug
 */
class Humanresource extends EloquentModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'capacity'];

    protected $guarded = ['id', 'slug'];

    /**
     * Has many human resources.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * TODO: Check slug setting can be moved to a more proper place.
     *
     * Save the model to the database.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->attributes['slug'] = str_slug($this->attributes['name']);

        return parent::save($options);
    }
}
