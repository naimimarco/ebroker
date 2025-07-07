<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class Notifications extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'notification';

    protected $fillable = [
        'title',
        'message',
        'image',
        'type',
        'send_type',
        'customers_id',
        'propertys_id',
    ];


    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    public function property(){
        return $this->belongsTo(Property::class,'propertys_id');
    }


    public function getImageAttribute($image)
    {
        if($image){
            return url('') . config('global.IMG_PATH') .config('global.NOTIFICATION_IMG_PATH') . $image;
        }
        return null;
    }
}
