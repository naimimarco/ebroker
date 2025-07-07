<?php

namespace App\Models;

use App\Models\VerifyCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAppTimezone;
class VerifyCustomerValue extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [
        'verify_customer_id',
        'verify_customer_form_id',
        'value',
        'created_at',
        'updated_at',
    ];

    /**
     * Get Data from Verify Customer
     *
     */
    public function verify_customer()
    {
        return $this->belongsTo(VerifyCustomer::class, 'verify_customer_id');
    }

    /**
     * Get the Verify Form that owns the VerifyCustomerValue
     *
     */
    public function verify_form()
    {
        return $this->belongsTo(VerifyCustomerForm::class, 'verify_customer_form_id');
    }



    public function getValueAttribute($value) {
        if ($this->relationLoaded('verify_form')) {
            if ($this->verify_form->field_type == "file") {
                if (!empty($value)) {
                    // Check if value is already a URL
                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        // Extract the file name from the URL
                        $fileName = basename($value);
                        // Regenerate the URL
                        return url('') . config('global.IMG_PATH') . config('global.AGENT_VERIFICATION_DOC_PATH') . $fileName;
                    } else {
                        return url('') . config('global.IMG_PATH') . config('global.AGENT_VERIFICATION_DOC_PATH') . $value;
                    }
                } else {
                    return null;
                }
            } else if ($this->verify_form->field_type == "checkbox") {
                $decodedValue = json_decode($value);
                return explode(",", $decodedValue);
            }
        }
        return $value;
    }
}
