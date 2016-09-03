<?php

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Timegridio\Concierge\Addressbook;
use Timegridio\Concierge\Presenters\BusinessPresenter;
use Timegridio\Concierge\Traits\IsIntoDomain;
use Timegridio\Concierge\Traits\Preferenceable;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $timezone
 * @property string $postal_address
 * @property string $phone
 * @property string $social_facebook
 * @property string $strategy
 * @property string $plan
 * @property string $country_code
 * @property string $locale
 * @property Illuminate\Support\Collection $contacts
 * @property Illuminate\Support\Collection $services
 * @property Illuminate\Support\Collection $vacancies
 * @property Illuminate\Support\Collection $vacancies
 * @property Illuminate\Support\Collection $bookings
 * @property Illuminate\Support\Collection $servicetypes
 * @property Illuminate\Support\Collection $owners
 * @property int $SubscriptionsCount
 */
class Business extends EloquentModel implements HasPresenter
{
    use SoftDeletes, Preferenceable, IsIntoDomain;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'timezone',
        'postal_address',
        'phone',
        'social_facebook',
        'strategy',
        'plan',
        'country_code',
        'locale',
        ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

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

    /**
     * Make Slug.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function makeSlug($name)
    {
        return str_slug($name);
    }

    ///////////////////
    // Relationships //
    ///////////////////

    /**
     * Belongs to a Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Has a Contact addressbook.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function addressbook()
    {
        return new Addressbook($this);
    }

    /**
     * Has a Contact addressbook.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class)
                    ->with('user')
                    ->withPivot('notes')
                    ->withTimestamps();
    }

    /**
     * Provides a catalog of Services.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Provides Services of Types.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicetypes()
    {
        return $this->hasMany(ServiceType::class);
    }

    /**
     * Publishes Vacancies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
    }

    /**
     * Has many human resources.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function humanresources()
    {
        return $this->hasMany(Humanresource::class);
    }

    /**
     * Holds booked Appointments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Is owned by Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners()
    {
        return $this->belongsToMany(config('auth.providers.users.model'))->withTimestamps();
    }

    /**
     * Belongs to a User.
     *
     * @return User
     */
    public function owner()
    {
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
     * Get presenter.
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
     * Set Slug.
     *
     * @return string Generated slug
     */
    public function setSlugAttribute()
    {
        return $this->attributes['slug'] = str_slug($this->name);
    }

    /**
     * Set name of the business.
     *
     * @param string $name Name of business
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = trim($name);
        $this->setSlugAttribute();
    }

    /**
     * Set Phone.
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
     * Set Postal Address.
     *
     * @param string $postalAddress Postal address
     */
    public function setPostalAddressAttribute($postalAddress)
    {
        $this->attributes['postal_address'] = trim($postalAddress) ?: null;
    }

    /**
     * Set Social Facebook.
     */
    public function setSocialFacebookAttribute($facebookPageUrl)
    {
        $this->attributes['social_facebook'] = trim($facebookPageUrl) ?: null;
    }
}
