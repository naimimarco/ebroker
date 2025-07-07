<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class HomepageSection extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'section_type',
        'status',
        'sort_order',
        'created_at',
        'updated_at'
    ];

    /**
     * Boot function to set the sort order.
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->sort_order = static::max('sort_order') + 1;
        });
    }
}
