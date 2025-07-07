<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class report_reasons extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'report_reasons';
    protected static function boot() {
        parent::boot();
        static::deleting(static function ($report_reasons) {
            $report_reasons->user_reports()->delete();
        });
    }
    public function user_reports()
    {
        return $this->hasMany(user_reports::class, 'reason_id', 'id');
    }
}
