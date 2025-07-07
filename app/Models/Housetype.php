<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class Housetype extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $table ='house_types';

    protected $fillable = [
        'type'
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];
}
