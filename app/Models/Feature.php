<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAppTimezone;
class Feature extends Model
{
    use HasFactory,SoftDeletes, HasAppTimezone;
    protected $hidden = array('created_at','updated_at','deleted_at');
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = array(
        'id',
        'name',
        'status'
    );
}
