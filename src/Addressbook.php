<?php

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Models\Business;
use Timegridio\Concierge\Models\Contact;

/**
 * The Addressbook class acts as a simplified Contact repository with most
 * common read/write functions.
 */
class Addressbook
{
    private $business;

    public function __construct($business)
    {
        $this->business = $business;
    }

    public function listing($limit)
    {
        return $this->business->contacts()->orderBy('lastname', 'ASC')->simplePaginate($limit);
    }

    public function find(Contact $contact)
    {
        return $this->business->contacts()->find($contact->id);
    }

    public function register($data)
    {
        $contact = $this->reuseExisting($data['email']);

        if ($contact) {
            return $contact;
        }

        $this->sanitizeDate($data['birthdate']);

        $contact = Contact::create($data);

        $this->business->contacts()->attach($contact);
        $this->business->save();

        if (array_key_exists('notes', $data)) {
            $this->updateNotes($contact, $data['notes']);
        }

        return $contact;
    }

    public function update(Contact $contact, $data = [], $notes = null)
    {
        $birthdate = array_get($data, 'birthdate');
        $this->sanitizeDate($birthdate);

        $contact->firstname = array_get($data, 'firstname');
        $contact->lastname = array_get($data, 'lastname');
        $contact->email = array_get($data, 'email');
        $contact->nin = array_get($data, 'nin');
        $contact->gender = array_get($data, 'gender');
        $contact->birthdate = $birthdate;
        $contact->mobile = array_get($data, 'mobile');
        $contact->mobile_country = array_get($data, 'mobile_country');
        $contact->postal_address = array_get($data, 'postal_address');

        $contact->save();

        $this->updateNotes($contact, $notes);

        return $contact;
    }

    public function reuseExisting($email)
    {
        if (trim($email) == '') {
            return false;
        }

        return $this->business->contacts()->where('email', '=', $email)->first();
    }

    public function getExisting($email)
    {
        if (trim($email) == '') {
            return false;
        }

        return Contact::whereNotNull('user_id')->where('email', '=', $email)->first();
    }

    public function getSubscribed($email)
    {
        if (trim($email) == '') {
            return false;
        }

        return $this->business->contacts()->where('email', '=', $email)->first();
    }

    public function getRegisteredUserId($userId)
    {
        return $this->business->contacts()->where('user_id', '=', $userId)->first();
    }

    public function reuseExistingByUserId($userId)
    {
        return $this->business->contacts()->where('user_id', '=', $userId)->first();
    }

    public function remove(Contact $contact)
    {
        return $this->business->contacts()->detach($contact->id);
    }

    public function linkToUserId(Contact $contact, $userId)
    {
        $contact->user()->associate($userId);
        $contact->save();

        return $contact->fresh();
    }

    public function copyFrom(Contact $existingContact, $userId)
    {
        $existingContactData = $existingContact->toArray();
        $this->sanitizeDate($existingContactData['birthdate']);
        $contact = Contact::create($existingContactData);
        $contact->user()->associate($userId);
        $contact->businesses()->detach();
        $contact->save();

        $this->business->contacts()->attach($contact);
        $this->business->save();

        return $contact;
    }

    protected function updateNotes(Contact $contact, $notes = null)
    {
        if ($notes) {
            $this->business->contacts()->find($contact->id)->pivot->update(compact('notes'));
        }
    }

    protected function sanitizeDate(&$value)
    {
        if (!is_string($value)) {
            return $value;
        }

        if (trim($value) == '') {
            return $value = null;
        }

        if (strlen($value) == 19) {
            return $value = Carbon::parse($value);
        }

        if (strlen($value) == 10) {
            return $value = Carbon::createFromFormat(trans('app.dateformat.carbon'), $value);
        }
    }
}
