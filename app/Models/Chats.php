<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\HasAppTimezone;

class Chats extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'chats';
    protected $fillable = ['sender_id', 'receiver_id', 'property_id', 'message', 'is_read', 'file', 'audio', 'created_at','updated_at'];


    protected static function boot() {
        parent::boot();
        static::deleting(static function ($chat) {
            if(collect($chat)->isNotEmpty()){
                // before delete() method call this

                // Delete File
                if ($chat->getRawOriginal('file') != '') {
                    $file = $chat->getRawOriginal('file');
                    if (file_exists(public_path('images') . config('global.CHAT_FILE') . $file)) {
                        unlink(public_path('images') . config('global.CHAT_FILE') . $file);
                    }
                }

                // Delete Audio
                if ($chat->getRawOriginal('audio') != '') {
                    $audio = $chat->getRawOriginal('audio');
                    if (file_exists(public_path('images') . config('global.CHAT_AUDIO') . $audio)) {
                        unlink(public_path('images') . config('global.CHAT_AUDIO') . $audio);
                    }
                }
            }
        });
    }

    public function sender()
    {
        return $this->belongsTo(Customer::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Customer::class, 'receiver_id');
    }
       public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
    public function getFileAttribute($file)
    {
        return $file != "" ? url('') . config('global.IMG_PATH') . config('global.CHAT_FILE') . $file : '';
    }
    public function getAudioAttribute($value)
    {
        return $value != "" ? url('') . config('global.IMG_PATH') . config('global.CHAT_AUDIO') . $value : '';
    }

    public function setMessageAttribute($value) {
        $this->attributes['message'] = htmlspecialchars($value);
    }

    public function getMessageAttribute($value){
        // e() functions is used to print message in plain text
        return e(htmlspecialchars_decode($value));
    }

}
