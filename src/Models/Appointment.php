<?php

namespace Timegridio\Concierge\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use McCool\LaravelAutoPresenter\HasPresenter;
use Timegridio\Concierge\Presenters\AppointmentPresenter;

/**
 * An Appointment can be understood as a reservation of a given Service,
 * provided by a given Business, targeted to a Contact, which will take place
 * on a determined Date and Time, and might have a duration and or comments.
 *
 * The Appointment can be issued by the Contact's User or by the Business owner.
 *
 * @property int $id
 * @property int $issuer_id
 * @property mixed $issuer
 * @property int $contact_id
 * @property Timegridio\Concierge\Models\Contact $contact
 * @property int $business_id
 * @property Timegridio\Concierge\Models\Business $business
 * @property int $service_id
 * @property Timegridio\Concierge\Models\Service $service
 * @property int $resource_id
 * @property Timegridio\Concierge\Models\Humanresource $resource
 * @property int $vacancy_id
 * @property Timegridio\Concierge\Models\Vacancy $vacancy
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $finish_at
 * @property int $duration
 * @property string $comments
 * @property string $hash
 * @property string $status
 * @property string $date
 * @property string $statusLabel
 * @property string $code
 * @property Carbon\Carbon $cancellationDeadline
 */
class Appointment extends EloquentModel implements HasPresenter
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'issuer_id',
        'contact_id',
        'business_id',
        'service_id',
        'resource_id',
        'start_at',
        'finish_at',
        'duration',
        'comments',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'hash', 'status', 'vacancy_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_at', 'finish_at'];

    /**
     * Appointment Hard Status Constants.
     */
    const STATUS_RESERVED = 'R';
    const STATUS_CONFIRMED = 'C';
    const STATUS_CANCELED = 'A';
    const STATUS_SERVED = 'S';

    ///////////////
    // PRESENTER //
    ///////////////

    /**
     * Get Presenter Class.
     *
     * @return App\Presenters\AppointmentPresenter
     */
    public function getPresenterClass()
    {
        return AppointmentPresenter::class;
    }

    /**
     * Generate hash and save the model to the database.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->doHash();

        return parent::save($options);
    }

    ///////////////////
    // Relationships //
    ///////////////////

    /**
     * Get the issuer (the User that generated the Appointment).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the target Contact (for whom is reserved the Appointment).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the holding Business (that has taken the reservation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the reserved Service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Humanresource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function humanresource()
    {
        return $this->belongsTo(Humanresource::class);
    }

    /**
     * Get the Vacancy (that justifies the availability of resources for the
     * Appointment generation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    ///////////
    // Other //
    ///////////

    /**
     * Get the User through Contact.
     *
     * @return User
     */
    public function user()
    {
        return $this->contact->user;
    }

    /**
     * Determine if the new Appointment will hash-crash with another existing
     * Appointment.
     *
     * @return bool
     */
    public function duplicates()
    {
        return !self::where('hash', $this->hash)->get()->isEmpty();
    }

    public function duration()
    {
        return $this->finish_at->diffInMinutes($this->start_at);
    }

    ///////////////
    // Accessors //
    ///////////////

    /**
     * Get Hash.
     *
     * @return string
     */
    public function getHashAttribute()
    {
        return isset($this->attributes['hash'])
            ? $this->attributes['hash']
            : $this->doHash();
    }

    /**
     * Get Finish At:
     * Calculates the start_at time plus duration in minutes.
     *
     * @return Carbon
     */
    public function getFinishAtAttribute()
    {
        if (array_get($this->attributes, 'finish_at') !== null) {
            return Carbon::parse($this->attributes['finish_at']);
        }

        if (is_numeric($this->duration)) {
            return $this->start_at->addMinutes($this->duration);
        }

        return $this->start_at;
    }

    /**
     * Get cancellation deadline (target date).
     *
     * @return Carbon\Carbon
     */
    public function getCancellationDeadlineAttribute()
    {
        $hours = $this->business->pref('appointment_cancellation_pre_hs');

        return $this->start_at
                    ->subHours($hours)
                    ->timezone($this->business->timezone);
    }

    /**
     * Get the human readable status name.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            Self::STATUS_RESERVED  => 'reserved',
            Self::STATUS_CONFIRMED => 'confirmed',
            Self::STATUS_CANCELED  => 'canceled',
            Self::STATUS_SERVED    => 'served',
            ];

        return array_key_exists($this->status, $labels)
            ? $labels[$this->status]
            : '';
    }

    /**
     * Get the date of the Appointment.
     *
     * @return string
     */
    public function getDateAttribute()
    {
        return $this->start_at
                    ->timezone($this->business->timezone)
                    ->toDateString();
    }

    /**
     * Get user-friendly unique identification code.
     *
     * @return string
     */
    public function getCodeAttribute()
    {
        $length = $this->business->pref('appointment_code_length');

        return strtoupper(substr($this->hash, 0, $length));
    }

    //////////////
    // Mutators //
    //////////////

    /**
     * Generate Appointment hash.
     *
     * @return string
     */
    public function doHash()
    {
        return $this->attributes['hash'] = md5(
            $this->start_at.'/'.
            $this->contact_id.'/'.
            $this->business_id.'/'.
            $this->service_id
        );
    }

    /**
     * Set start at.
     *
     * @param Carbon $datetime
     */
    public function setStartAtAttribute(Carbon $datetime)
    {
        $this->attributes['start_at'] = $datetime;
    }

    /**
     * Set finish_at attribute.
     *
     * @param Carbon $datetime
     */
    public function setFinishAtAttribute(Carbon $datetime)
    {
        $this->attributes['finish_at'] = $datetime;
    }

    /**
     * Set Comments.
     *
     * @param string $comments
     */
    public function setCommentsAttribute($comments)
    {
        $this->attributes['comments'] = trim($comments) ?: null;
    }

    /////////////////
    // HARD STATUS //
    /////////////////

    /**
     * Determine if is Reserved.
     *
     * @return bool
     */
    public function isReserved()
    {
        return $this->status == Self::STATUS_RESERVED;
    }

    ///////////////////////////
    // Calculated attributes //
    ///////////////////////////

    /**
     * Appointment Status Workflow.
     *
     * Hard Status: Those concrete values stored in DB
     * Soft Status: Those values calculated from stored values in DB
     *
     * Suggested transitions (Binding is not mandatory)
     *     Reserved -> Confirmed -> Served
     *     Reserved -> Served
     *     Reserved -> Canceled
     *     Reserved -> Confirmed -> Canceled
     *
     * Soft Status
     *     (Active)   [ Reserved  | Confirmed ]
     *     (InActive) [ Canceled  | Served    ]
     */

    /**
     * Determine if is Active.
     *
     * @return bool
     */
    public function isActive()
    {
        return
            $this->status == Self::STATUS_CONFIRMED ||
            $this->status == Self::STATUS_RESERVED;
    }

    /**
     * Determine if is Pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->isActive() && $this->isFuture();
    }

    /**
     * Determine if is Future.
     *
     * @return bool
     */
    public function isFuture()
    {
        return !$this->isDue();
    }

    /**
     * Determine if is due.
     *
     * @return bool
     */
    public function isDue()
    {
        return $this->start_at->isPast();
    }

    ////////////
    // Scopes //
    ////////////

    /**
     * Scope to Business.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeOfBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /////////////////////////
    // Hard Status Scoping //
    /////////////////////////

    /**
     * Scope to Contacts Collection.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeForContacts($query, $contacts)
    {
        return $query->whereIn('contact_id', $contacts->pluck('id'));
    }

    /**
     * Scope to Unarchived Appointments.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeUnarchived($query)
    {
        $carbon = Carbon::parse('today midnight')->timezone('UTC');

        return $query
            ->where(function ($query) use ($carbon) {

                $query->whereIn('status', [Self::STATUS_RESERVED, Self::STATUS_CONFIRMED])
                    ->where('start_at', '<=', $carbon)
                    ->orWhere(function ($query) use ($carbon) {
                        $query->where('start_at', '>=', $carbon);
                    });
            });
    }

    /**
     * Scope to Served Appointments.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeServed($query)
    {
        return $query->where('status', '=', Self::STATUS_SERVED);
    }

    /**
     * Scope to Canceled Appointments.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', '=', Self::STATUS_CANCELED);
    }

    /////////////////////////
    // Soft Status Scoping //
    /////////////////////////

    /**
     * Scope to not Served Appointments.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeUnServed($query)
    {
        return $query->where('status', '<>', Self::STATUS_SERVED);
    }

    /**
     * Scope to Active Appointments.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [Self::STATUS_RESERVED, Self::STATUS_CONFIRMED]);
    }

    /**
     * Scope of date.
     *
     * @param Illuminate\Database\Query $query
     * @param Carbon                    $date
     *
     * @return Illuminate\Database\Query
     */
    public function scopeOfDate($query, Carbon $date)
    {
        $date->timezone('UTC');

        return $query->whereRaw('date(`start_at`) = ?', [$date->toDateString()]);
    }

    /**
     * Soft check of time interval affectation
     *
     * @param Illuminate\Database\Query $query
     * @param Carbon                    $startAt
     * @param Carbon                    $finishAt
     *
     * @return Illuminate\Database\Query
     */
    public function scopeAffectingInterval($query, Carbon $startAt, Carbon $finishAt)
    {
        $startAt->timezone('UTC');
        $finishAt->timezone('UTC');

        return $query
            ->where(function ($query) use ($startAt, $finishAt) {

                $query->where(function ($query) use ($startAt, $finishAt) {
                    $query->where('finish_at', '>=', $finishAt)
                            ->where('start_at', '<', $startAt);
                })
                ->orWhere(function ($query) use ($startAt, $finishAt) {
                    $query->where('finish_at', '<=', $finishAt)
                            ->where('finish_at', '>', $startAt);
                })
                ->orWhere(function ($query) use ($startAt, $finishAt) {
                    $query->where('start_at', '>=', $startAt)
                            ->where('start_at', '<', $finishAt);
                });
//                ->orWhere(function ($query) use ($startAt, $finishAt) {
//                    $query->where('start_at', '>', $startAt)
//                            ->where('finish_at', '>', $finishAt);
//                });

            });
    }

    /**
     * Scope Affecting Humanresource.
     *
     * @param Illuminate\Database\Query $query
     *
     * @return Illuminate\Database\Query
     */
    public function scopeAffectingHumanresource($query, $humanresourceId)
    {
        if (is_null($humanresourceId)) {
            return $query;
        }

        return $query->where('humanresource_id', $humanresourceId);
    }

    //////////////////////////
    // Soft Status Checkers //
    //////////////////////////

    /**
     * User is target contact of the appointment.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isTarget($userId)
    {
        return $this->contact->isProfileOf($userId);
    }

    /**
     * User is issuer of the appointment.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isIssuer($userId)
    {
        return $this->issuer ? $this->issuer->id == $userId : false;
    }

    /**
     * User is owner of business.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isOwner($userId)
    {
        return $this->business->owners->contains($userId);
    }

    /**
     * can be canceled by user.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function canCancel($userId)
    {
        return $this->isOwner($userId) ||
            ($this->isIssuer($userId) && $this->isOnTimeToCancel()) ||
            ($this->isTarget($userId) && $this->isOnTimeToCancel());
    }

    /**
     * Determine if it is still possible to cancel according business policy.
     *
     * @return bool
     */
    public function isOnTimeToCancel()
    {
        $graceHours = $this->business->pref('appointment_cancellation_pre_hs');

        $diff = $this->start_at->diffInHours(Carbon::now());

        return intval($diff) >= intval($graceHours);
    }

    /**
     * can Serve.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function canServe($userId)
    {
        return $this->isOwner($userId);
    }

    /**
     * can confirm.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function canConfirm($userId)
    {
        return $this->isIssuer($userId) || $this->isOwner($userId);
    }

    /**
     * is Serveable by user.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isServeableBy($userId)
    {
        return $this->isServeable() && $this->canServe($userId);
    }

    /**
     * is Confirmable By user.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isConfirmableBy($userId)
    {
        return
            $this->isConfirmable() &&
            $this->shouldConfirmBy($userId) &&
            $this->canConfirm($userId);
    }

    /**
     * is cancelable By user.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isCancelableBy($userId)
    {
        return $this->isCancelable() && $this->canCancel($userId);
    }

    /**
     * Determine if the queried userId may confirm the appointment or not.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function shouldConfirmBy($userId)
    {
        return ($this->isSelfIssued() && $this->isOwner($userId)) || $this->isIssuer($userId);
    }

    /**
     * Determine if the target Contact's User is the same of the Appointment
     * issuer User.
     *
     * @return bool
     */
    public function isSelfIssued()
    {
        if (!$this->issuer) {
            return false;
        }
        if (!$this->contact) {
            return false;
        }
        if (!$this->contact->user) {
            return false;
        }

        return $this->issuer->id == $this->contact->user->id;
    }

    /**
     * Determine if the Serve action can be performed.
     *
     * @return bool
     */
    public function isServeable()
    {
        return $this->isActive() && $this->isDue();
    }

    /**
     * Determine if the Confirm action can be performed.
     *
     * @return bool
     */
    public function isConfirmable()
    {
        return $this->status == self::STATUS_RESERVED && $this->isFuture();
    }

    /**
     * Determine if the cancelable action can be performed.
     *
     * @return bool
     */
    public function isCancelable()
    {
        return $this->isActive();
    }

    /////////////////////////
    // Hard Status Actions //
    /////////////////////////

    /**
     * Check and perform Confirm action.
     *
     * @return $this
     */
    public function doReserve()
    {
        if ($this->status === null) {
            $this->status = self::STATUS_RESERVED;
        }

        return $this;
    }

    /**
     * Check and perform Confirm action.
     *
     * @return $this
     */
    public function doConfirm()
    {
        if ($this->isConfirmable()) {
            $this->status = self::STATUS_CONFIRMED;

            $this->save();
        }

        return $this;
    }

    /**
     * Check and perform cancel action.
     *
     * @return $this
     */
    public function doCancel()
    {
        if ($this->isCancelable()) {
            $this->status = self::STATUS_CANCELED;

            $this->save();
        }

        return $this;
    }

    /**
     * Check and perform Serve action.
     *
     * @return $this
     */
    public function doServe()
    {
        if ($this->isServeable()) {
            $this->status = self::STATUS_SERVED;

            $this->save();
        }

        return $this;
    }
}
