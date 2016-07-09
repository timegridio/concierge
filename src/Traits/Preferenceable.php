<?php

namespace Timegridio\Concierge\Traits;

use Illuminate\Support\Facades\Cache;
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

            Cache::put("{$this->slug}.'/'.{$key}", $value, 60);
            return $value;
        }

        if($value = Cache::get("{$this->slug}.'/'.{$key}"))
        {
            return $value;
        }

        if ($pref = $this->preferences()->forKey($key)->first()) {
            $value = $pref->value();
            $type = $pref->type();
        } else {
            $default = Preference::getDefault($this, $key);
            $value = $default->value();
            $type = $default->type();
        }
        
        Cache::put("{$this->slug}.'/'.{$key}", $value, 60);
        return $this->cast($value, $type);
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
            case 'array':
                if (is_array($value)) {
                    return serialize($value);
                } else {
                    return unserialize($value);
                }
                break;
            default:
                return $value;
        }
    }
}
