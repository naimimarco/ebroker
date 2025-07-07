<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class parameter extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'parameters';

    protected $fillable = [
        'name',
        'type_of_parameter',
        'type_values',
        'is_required',
        'image'
    ];
    protected $hidden = ["created_at", "updated_at"];

    public function getTypeValuesAttribute($value)
    {
        $a = json_decode($value, true);
        if ($a == NULL) {
            return $value;
        } else {
            return (json_decode($value, true));
        }
    }
    public function getImageAttribute($image)
    {
        return $image != "" ? url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMAGE_PATH')  . $image : "";
    }
    public function assigned_parameter()
    {
        return $this->hasOne(AssignParameters::class, 'parameter_id');
    }
}
