<?php

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $business_id
 * @property Timegridio\Concierge\Models\Business $business
 * @property Illuminate\Support\Collection $services
 * @property int $slug
 */
class ServiceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'business_id'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'slug'];

    /**
     * Has many services.
     *
     * @return Illuminate\Database\Query Relationship
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Belongs to Business.
     *
     * @return Illuminate\Database\Query Relationship
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
