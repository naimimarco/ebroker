<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class NumberOtp extends Model
{
    use HasFactory, HasAppTimezone;
    protected $table = 'number_otps';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [
        'number',
        'email',
        'otp',
        'expire_at',
    ];

    public function setOtpAttribute($value) {
        $this->attributes['otp'] = base64_encode($value);
    }

    public function getOtpAttribute($value) {
        return base64_decode($value);
    }
}
