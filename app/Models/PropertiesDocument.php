<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class PropertiesDocument extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table ='properties_documents';

    protected $fillable = [
        'property_id',
        'name',
        'type'
    ];

    public function getNameAttribute($name)
    {
        return $name != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_DOCUMENT_PATH'). $this->property_id . "/" . $name : '';
    }

}
