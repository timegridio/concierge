<?php

namespace Timegridio\Concierge\Traits;

use Timegridio\Concierge\Models\Preference;

trait Preferenceable
{
    /**
     * Preferences morph.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function preferences()
    {
        return $this->morphMany(Preference::class, 'preferenceable');
    }

    /**
     * Get or set preference value.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @param  string $type
     *
     * @return mixed  Value.
     */
    public function pref($key, $value = null, $type = 'string')
    {
        if (isset($value)) {
            $this->preferences()->updateOrCreate(['key' => $key], ['value' => $this->cast($value, $type),
                                                                   'type'  => $type, ]);

            return $value;
        }
        $default = Preference::getDefault($this, $key);

        return($pref = $this->preferences()->forKey($key)->first()) ? $pref->value() : $default->value();
    }

    /**
     * Cast value.
     *
     * @param  mixed $value
     * @param  mixed $type
     *
     * @return mixed Value.
     */
    private function cast($value, $type)
    {
        switch ($type) {
            case 'bool':
                return boolval($value);
                break;
            case 'int':
                return intval($value);
                break;
            case 'float':
                return floatval($value);
                break;
            case 'string':
                return $value;
                break;
            default:
                return $value;
        }
    }
}
