<?php

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Preference extends EloquentModel
{
    /**
     * [$fillable description].
     *
     * @var [type]
     */
    protected $fillable = ['key', 'value', 'type'];

    /**
     * [getDefault description].
     *
     * @param \Timegridio\Concierge\Traits\Preferenceable $model [description]
     * @param [type] $key   [description]
     *
     * @return [type] [description]
     */
    public static function getDefault($model, $key)
    {
        $class = get_class($model);
        $value = config("preferences.{$class}.{$key}.value");
        $type = config("preferences.{$class}.{$key}.type");

        return new self([
            'key'                 => $key,
            'value'               => $value,
            'type'                => $type,
            'preferenceable_type' => $class,
            'preferenceable_id'   => $model,
            ]);
    }

    /**
     * [question description].
     *
     * @return [type] [description]
     */
    public function question()
    {
        return trans("preferences.{$this->preferenceable_type}.question.{$this->key}");
    }

    /**
     * [help description].
     *
     * @return [type] [description]
     */
    public function help()
    {
        return trans("preferences.{$this->preferenceable_type}.help.{$this->key}");
    }

    /**
     * [scopeForKey description].
     *
     * @param [type] $query [description]
     * @param [type] $key   [description]
     *
     * @return [type] [description]
     */
    public function scopeForKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Value getter.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Type getter.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }
}
