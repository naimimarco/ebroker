<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class CachingService {

    /**
     * @param $key
     * @param callable $callback - Callback function must return a value
     * @param int $time = 3600
     * @return mixed
     */
    public function systemLevelCaching($key, callable $callback, int $time = 3600) {
        return Cache::remember($key, $time, $callback);
    }

    /**
     * @param array|string $key
     * @return mixed|string
     */

     public function getDefaultLanguage() {
        $systemDefaultLanguageCode = system_setting('default_language');
        if(!empty($systemDefaultLanguageCode)){
            $language = Language::where('code',$systemDefaultLanguageCode)->first();
            if(collect($language)->isNotEmpty()){
                return $this->systemLevelCaching(config('constants.CACHE.SYSTEM.DEFAULT_LANGUAGE'), function () use ($language) {
                    return $language;
                });
            }
            return null;
        }
    }
    public function removeSystemCache($key) {
        Cache::forget($key);
    }
}
