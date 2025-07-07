<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAppTimezone;
class UserPackage extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'start_date', 'end_date'];
    protected $hidden = array('created_at','updated_at','deleted_at');
    protected $fillable = array(
        'id',
        'user_id',
        'package_id',
        'start_date',
        'end_date',
    );

    /**
     * Get all of the user_package_limit for the UserPackage
     */
    public function user_package_limits()
    {
        return $this->hasMany(UserPackageLimit::class, 'user_package_id', 'id');
    }

    /**
     * Get the customer that owns the UserPackage
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Get the package that owns the UserPackage
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }


    // Scope
    public function scopeOnlyActive($query) {
        $currentDate = Carbon::now();
        return $query->where(function ($q) use($currentDate){
            $q->whereDate('end_date', '>=', $currentDate)->orWhereNull('end_date');
        })->orderBy('end_date', 'asc');
    }

}
