<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class Article extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public function getImageAttribute($image)
    {
        // return false;
        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.ARTICLE_IMG_PATH') . $image : '';
    }
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    // public function getCreatedAtAttribute($value){
    //     return \Carbon\Carbon::parse($value)->diffForHumans();
    // }
}
