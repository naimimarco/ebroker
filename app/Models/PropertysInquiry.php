<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class PropertysInquiry extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table ='propertys_inquiry';

    protected $fillable = [
        'propertys_id',
        'customers_id',
        'status'
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];



    public function customer(){
        return $this->hasOne(Customer::class,'id','customers_id')->select('id','name','email','mobile','address','fcm_id','notification');
    }

    public function property(){
        return $this->hasOne(Property::class,'id','propertys_id')->with('category')->with('customer:id,name,mobile');
    }

}
