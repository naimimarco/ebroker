<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class BankReceiptFile extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'payment_transaction_id',
        'file',
    ];

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            unlink_image($model->file);
        });
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function getFileAttribute($value)
    {
        return $value != '' ? url('') . config('global.IMG_PATH') . config('global.BANK_RECEIPT_FILE_PATH') . $value : '';
    }
}
