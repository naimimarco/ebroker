<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [

        '/webhook/razorpay',
        '/webhook/paystack',
        '/webhook/paypal',
        '/webhook/stripe',
        '/webhook/flutterwave',
                '/InApp/Appstore',


        '/firebase_messaging_settings'





    ];
}
