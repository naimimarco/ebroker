<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }
    public function handle($request, Closure $next)
    {
        // Check if an authorization token is provided in the request header
        $token = $request->bearerToken();
        if ($token) {
            // Attempt to find a token in the PersonalAccessToken model
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                // Authenticate the user associated with the token
                Auth::loginUsingId($accessToken->tokenable_id);
            }
        }
        // Continue with the request whether or not the user is authenticated
        return $next($request);
    }
}
