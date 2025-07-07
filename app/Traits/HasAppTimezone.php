<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Services\HelperService;
use Illuminate\Support\Facades\Log;

trait HasAppTimezone
{
    // Define date fields that should be converted
    protected $dateFields = [
        'created_at',
        'updated_at',
        'deleted_at',
        'date',
        'time',
        'datetime',
        'timestamp',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'start_datetime',
        'end_datetime',
        'start_timestamp',
        'end_timestamp'
    ];

    /**
     * Boot the trait.
     * This is called automatically by Laravel when the model using this trait is being booted.
     */
    public static function bootHasAppTimezone()
    {
        static::retrieved(function ($model) {
            $model->convertDatesToAppTimezone();
        });
    }

    /**
     * Convert dates to application timezone
     */
    protected function convertDatesToAppTimezone()
    {
        foreach ($this->dates as $field) {
            if (in_array($field, $this->dateFields)) {
                try {
                    if (isset($this->attributes[$field])) {
                        $value = $this->getAttributeValue($field);
                        $value = HelperService::toAppTimezone(new Carbon($value));
                        $this->attributes[$field] = $value;
                    }
                } catch (Exception $e) {
                    // Log the error instead of silently returning true
                    Log::error('Error converting date to app timezone: ' . $e->getMessage(), [
                        'field' => $field,
                        'model' => get_class($this),
                        'id' => $this->getKey(),
                    ]);
                }
            }
        }
    }
}