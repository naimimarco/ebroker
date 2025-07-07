<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Carbon\Carbon;
use App\Models\Advertisement;
use dacoto\EnvSet\Facades\EnvSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckAdvertisementsExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

        public function handle($request, Closure $next)
        {
            try{
                // Check Advertisement and make expire to those advertisements whose expiry date is passed by today's date
                $today = Carbon::today();
                if (!Cache::has('ads_expired_today')) {
                    // Check if the request URL contains "/install"
                    if (strpos($request->url(), '/install') !== false) {
                        // If it does, skip the middleware
                        return $next($request);
                    }

                    // Change Cache Driver to file
                    if(EnvSet::keyExists('CACHE_DRIVER') && env('CACHE_DRIVER') != 'file'){
                        EnvSet::setKey('CACHE_DRIVER','file');
                        EnvSet::save();
                    }

                    // Check DB connection
                    DB::connection()->getPdo();

                    // Expire advertisements
                    Advertisement::where('end_date', '<', $today)->where('status', '!=', '3')->update(['status' => '3', 'is_enable' => 0]);

                    // Set cache to avoid repetitive updates
                    Cache::put('ads_expired_today', true, Carbon::now()->endOfDay());
                }
            }catch(Exception $e){
                Log::error('CheckAdvertisementExpiration Middleware issue');
                return $next($request);
            }

            return $next($request);
        }

}
