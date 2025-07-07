<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class Unit extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table ='units';

    protected $fillable = [
        'measurement'
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];


}
