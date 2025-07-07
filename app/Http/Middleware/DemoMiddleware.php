<?php

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Console\View\Components\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class DemoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Exclude URLs
        $exclude_uri = array(
            '/login',
            '/logout',
            '/api/post_property',
            '/api/update_post_property',
            '/api/post_project',
            '/api/personalised-fields',
            '/api/add_favourite',
            '/api/send_message',
            '/api/delete_chat_message',
            '/api/system_settings',
            '/api/user_signup',
            '/project-generate-slug',
            'article/generate-slug',
            'category/generate-slug',
            'parameter/generate-slug',
            '/property/generate-slug'
        );

        // Exclude Emails
        $excludeEmails = [
            "superadmin@gmail.com",
        ];

        /**
         * Conditions
         * 1. Demo Mode is True.
         * 2. Request is not get
         * 3. Authenticated user
         * 4. Authenticated user's email is not in excluded emails
         * 5. Request URL is not in Excluded URL
        */
        if (env('DEMO_MODE') && !$request->isMethod('get') && Auth::check()) {
            if(in_array(Auth::user()->email, $excludeEmails)){
                return $next($request);
            }else if(Auth::user()->auth_id != 'sg6XM3VTneYnBI5xeJkZ1Dxti3f1' && Auth::user()->mobile != '919764318246' && Auth::user()->email != 'wrteamdemo@gmail.com' && Auth::user()->email != 'admin@gmail.com'){
                return $next($request);
            }else{
                if(!in_array($request->getRequestUri(), $exclude_uri)){
                    if ($request->ajax()) {
                        $response['error'] = true;
                        $response['message'] = 'This is not allowed in the Demo Version';
                        $response['code'] = 403;
                        return response()->json($response);
                    } else if (request()->wantsJson() || Str::startsWith(request()->path(), 'api')) {
                        $response['error'] = true;
                        $response['message'] = 'This is not allowed in the Demo Version';
                        $response['code'] = 403;
                        return response()->json($response);
                    } else {
                        return back()->with('error', 'This is not allowed in the Demo Version');
                    }
                }
            }
        }
        return $next($request);
    }
}
