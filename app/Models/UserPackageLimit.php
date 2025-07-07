<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class UserPackageLimit extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = array('created_at','updated_at','deleted_at');
    protected $fillable = array(
        'id',
        'user_package_id',
        'package_feature_id',
        'total_limit',
        'used_limit',
        'created_at',
        'updated_at'
    );

    /**
     * Get the user that owns the UserPackageLimit
     */
    public function user_package()
    {
        return $this->belongsTo(UserPackage::class, 'user_package_id');
    }
}
