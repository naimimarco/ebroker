<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Traits\HasAppTimezone;
class Language extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    // public function getFileNameAttribute($file)
    // {

    //     $json_string = file_exists(public_path('languages/' . $file)) ? file_get_contents(public_path('languages/' . $file)) : "This File Is Not Available";
    //     return json_decode($json_string);
    // }
}
