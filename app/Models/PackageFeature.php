<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class PackageFeature extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = array('created_at','updated_at','deleted_at');
    protected $fillable = array(
        'id',
        'package_id',
        'feature_id',
        'limit_type',
        'limit'
    );


    /** Relations */

    /**
     * Get the package that owns the PackageFeature
     *
    */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id')->withTrashed();
    }

    /**
     * Get the feature that owns the PackageFeature
     *
    */
    public function feature()
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    /**
     * Get all of the user_package_limits for the PackageFeature
     *
     */
    public function user_package_limits()
    {
        return $this->hasMany(UserPackageLimit::class, 'package_feature_id', 'id');
    }
}
