<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class PropertyImages extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table ='property_images';

    protected $fillable = [
        'propertys_id',
        'image',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];



}
