<?php

namespace Timegridio\Concierge\Models;

use Fenos\Notifynder\Notifable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Timegridio\Concierge\Presenters\BusinessPresenter;
use Timegridio\Concierge\Traits\HasDomain;
use Timegridio\Concierge\Traits\Preferenceable;

class Business extends EloquentModel implements HasPresenter
{
    use SoftDeletes, Preferenceable, HasDomain, Notifable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'timezone', 'postal_address',
        'phone', 'social_facebook', 'strategy', 'plan', 'country_code', 'locale', ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Define model events.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($business) {

            $business->slug = $business->makeSlug($business->name);

        });
    }

    protected function makeSlug($name)
    {
        return str_slug($name);
    }

    /**
     * Create Business model.
     *
     * @param array $attributes Attributes for filling the model
     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->setSlugAttribute();
//    }

    ///////////////////
    // Relationships //
    ///////////////////

    /**
     * belongs to Category.
     *
     * @return Illuminate\Database\Query Relationship Business Category query
     */
    public function category()
    {
        /* TODO: Use cache here? */
        return $this->belongsTo(Category::class);
    }

    /**
     * holds Contacts.
     *
     * @return Illuminate\Database\Query Relationship Business held Contacts query
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class)
                    ->with('user')
                    ->withPivot('notes')
                    ->withTimestamps();
    }

    /**
     * provides Services.
     *
     * @return Illuminate\Database\Query Relationship Business provided Services query
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * provides Service types.
     *
     * @return Illuminate\Database\Query Relationship
     */
    public function servicetypes()
    {
        return $this->hasMany(ServiceType::class);
    }

    /**
     * publishes Vacancies.
     *
     * @return Illuminate\Database\Query Relationship Business published Vacancies query
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
    }

    /**
     * ToDo: Should be renamed to "appointments"
     * holds Appointments booking.
     *
     * @return Illuminate\Database\Query Relationship Business holds Appointments query
     */
    public function bookings()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * belongs to Users.
     *
     * @return Illuminate\Database\Query Relationship Business belongs to User (owners) query
     */
    public function owners()
    {
        /* TODO: Use cache here? */
        return $this->belongsToMany(config('auth.providers.users.model'))->withTimestamps();
    }

    /**
     * belongs to User.
     *
     * @return User Relationship Business belongs to User (owner)
     */
    public function owner()
    {
        /* TODO: Use cache here? */
        return $this->owners()->first();
    }

    /**
     * Get the real Users subscriptions count.
     *
     * @return Illuminate\Database\Query Relationship
     */
    public function subscriptionsCount()
    {
        return $this->belongsToMany(Contact::class)
                    ->selectRaw('id, count(*) as aggregate')
                    ->whereNotNull('user_id')
                    ->groupBy('business_id');
    }

    /**
     * get SubscriptionsCount Attribute.
     *
     * @return int Count of Contacts with real User held by this Business
     */
    public function getSubscriptionsCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('subscriptionsCount', $this->relations)) {
            $this->load('subscriptionsCount');
        }

        $related = $this->getRelation('subscriptionsCount');

        // then return the count directly
        return ($related->count() > 0) ? (int) $related->first()->aggregate : 0;
    }

    ///////////////
    // Overrides //
    ///////////////

    //

    ///////////////
    // Presenter //
    ///////////////

    /**
     * get presenter.
     *
     * @return BusinessPresenter Presenter class
     */
    public function getPresenterClass()
    {
        return BusinessPresenter::class;
    }

    ///////////////
    // Accessors //
    ///////////////

    /**
     * get route key.
     *
     * @return string Model slug
     */
    public function getRouteKey()
    {
        return $this->slug;
    }

    //////////////
    // Mutators //
    //////////////

    /**
     * set Slug.
     *
     * @return string Generated slug
     */
    public function setSlugAttribute()
    {
        return $this->attributes['slug'] = str_slug($this->name);
    }

    /**
     * set name of the business.
     *
     * @param string $name Name of business
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = trim($name);
        $this->setSlugAttribute();
    }

    /**
     * set Phone.
     *
     * Expected phone number is international format numeric only
     *
     * @param string $phone Phone number
     */
    public function setPhoneAttribute($phone)
    {
        $this->attributes['phone'] = trim($phone) ?: null;
    }

    /**
     * set Postal Address.
     *
     * @param string $postal_address Postal address
     */
    public function setPostalAddressAttribute($postalAddress)
    {
        $this->attributes['postal_address'] = trim($postalAddress) ?: null;
    }

    /**
     * set Social Facebook.
     *
     * @param string $social_facebook Facebook User URL
     */
    public function setSocialFacebookAttribute($facebookPageUrl)
    {
        $this->attributes['social_facebook'] = trim($facebookPageUrl) ?: null;
    }
}
