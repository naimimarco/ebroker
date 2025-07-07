<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAppTimezone;
class ProjectPlans extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = array(
        'title',
        'document',
        'project_id',
        'created_at',
        'updated_at'
    );
    public function getDocumentAttribute($name)
    {
        return $name != '' ? url('') . config('global.IMG_PATH') . config('global.PROJECT_DOCUMENT_PATH') . $name : '';
    }
}
