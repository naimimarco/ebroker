<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAppTimezone;
class VerifyCustomer extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [
        'user_id',
        'status'
    ];

    /**
     * Get the user that owns the VerifyCustomer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Get all of the Verify Form Values for the VerifyCustomer
     *
     */
    public function verify_customer_values()
    {
        return $this->hasMany(VerifyCustomerValue::class, 'verify_customer_id');
    }
}
