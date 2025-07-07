<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class VerifyCustomerFormValue extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [
        'verify_customer_form_id',
        'value',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the Form Field that owns the VerifyCustomerFormValue
     *
     */
    public function verify_customer_form()
    {
        return $this->belongsTo(VerifyCustomerForm::class, 'verify_customer_form_id');
    }
}
