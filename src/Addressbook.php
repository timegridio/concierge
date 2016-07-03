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
        $contact = $this->getSubscribed($data['email']);

        if ($contact) {
            return $contact;
        }

        $this->sanitizeDate($data['birthdate']);

        $contact = Contact::create($data);

        $this->business->contacts()->attach($contact, array_only($data, 'notes'));

        return $contact;
    }

    public function update(Contact $contact, $data = [], $notes = null)
    {
        $birthdate = array_get($data, 'birthdate');
        $this->sanitizeDate($birthdate);

        $contact->fill(array_except($data, 'birthdate'));
        $contact->birthdate = $birthdate;

        $contact->save();

        $this->updateNotes($contact, $notes);

        return $contact;
    }

    public function getSubscribed($email)
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

    public function getRegisteredUserId($userId)
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

    public function copyFrom(Contact $contact, $userId)
    {
        $replicatedContact = $contact->replicate(['id']);
        $replicatedContact->user()->associate($userId);
        $replicatedContact->businesses()->detach();
        $replicatedContact->save();

        $this->business->contacts()->attach($replicatedContact);
        $this->business->save();

        return $replicatedContact;
    }

    protected function updateNotes(Contact $contact, $notes)
    {
        $this->business->contacts()->find($contact->id)->pivot->update(compact('notes'));
    }

    protected function getDateFormat()
    {
        return $this->business->pref('date_format');
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
            return $value = Carbon::createFromFormat('m/d/Y', $value);
        }
    }
}
