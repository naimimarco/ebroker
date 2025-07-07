<?php

namespace App\Models;

use App\Traits\HasAppTimezone;
use App\Services\HelperService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes, HasAppTimezone;

    protected $fillable = array(
        'id',
        'name',
        'ios_product_id',
        'package_type',
        'price',
        'duration',
        'status'
    );

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /** Relations */
    /**
     * Get all of the features for the Package
     *
     */
    public function package_features()
    {
        return $this->hasMany(PackageFeature::class, 'package_id', 'id')->with('feature');
    }

    public function user_packages(){
        return $this->hasMany(UserPackage::class, 'package_id', 'id');
    }


    public function getIsActiveAttribute(){
        if(Auth::guard('sanctum')->user()){
            $userId = Auth::guard('sanctum')->user()->id;
            $packageId = $this->id;

            $getActivePackages = HelperService::getActivePackage($userId,$packageId);
            if(collect($getActivePackages)->isNotEmpty()){
                return true;
            }
        }
        return false;
    }

    public function getPackagePaymentStatusAttribute(){
        if(Auth::guard('sanctum')->user()){
            $userId = Auth::guard('sanctum')->user()->id;
            $packageId = $this->id;

            // Check if package is active
            $getActivePackages = HelperService::getActivePackage($userId, $packageId);
            if(collect($getActivePackages)->isNotEmpty()){
                return 'active';
            }

            // Check payment transaction status
            $paymentTransaction = PaymentTransaction::where('user_id', $userId)
                ->where('package_id', $packageId)
                ->latest()
                ->first();

            if ($paymentTransaction) {
                switch ($paymentTransaction->payment_status) {
                    case 'pending':
                        return 'pending';
                    case 'review':
                        return 'review';
                    case 'failed':
                        return 'failed';
                    case 'rejected':
                        return 'rejected';
                    case 'success':
                        // If payment is successful but package is not active, it might have expired
                        return 'expired';
                    default:
                        return 'inactive';
                }
            }
        }

        return 'inactive';
    }

    public function getPaymentTransactionIdAttribute(){
        if(Auth::guard('sanctum')->user()){
            $userId = Auth::guard('sanctum')->user()->id;
            $packageId = $this->id;

            // Check payment transaction status
            $paymentTransaction = PaymentTransaction::where('user_id', $userId)
                ->where('package_id', $packageId)
                ->latest()
                ->first();

            if (!empty($paymentTransaction)) {
                return $paymentTransaction->id;
            }
        }

        return null;
    }
}
