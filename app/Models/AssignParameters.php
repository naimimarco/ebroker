<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class AssignParameters extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'assign_parameters';

    protected $fillable = [
        'modal_type',
        'modal_id',
        'property_id',
        'parameter_id',
        'value'
    ];

    public function modal()
    {
        return $this->morphTo();
    }
    public function parameter()
    {
        return  $this->belongsTo(parameter::class,'parameter_id');
    }


    public function getValueAttribute($value)
    {
        if(!empty($value)){
            $a = json_decode($value, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                if ($a == NULL) {
                    /** Was Getting Null in string that's why commented $value return code */
                    // return $value;
                    return "";
                } else {
                    return $a;
                }
            }else{
                return $value;
            }
        }
        return "";
    }
//     public function getValueAttribute($value)
// {
//     // Try to decode JSON strings
//     $decoded = json_decode($value, true);
//     if ($decoded !== null) {
//         return $decoded;
//     }

//     // Try to convert numeric strings to numbers
//     if (is_numeric($value)) {
//         if (strpos($value, '.') !== false) {
//             return floatval($value);
//         } else {
//             return intval($value);
//         }
//     }

//     // Otherwise return the original string
//     return $value;
// }

}
