<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Imdhemy\AppStore\Jws\Parser;
use Imdhemy\AppStore\Jws\AppStoreJwsVerifier;
use Imdhemy\AppStore\ServerNotifications\ServerNotification;

class InAppPurchaseController extends Controller
{
    public function checkInAppPurchase(Request $request)
    {


        $signedPayload = $request->getSignedPayload(); // Should be the request body received from the App Store
        $jws = Parser::toJws($signedPayload);
        $verifier = new AppStoreJwsVerifier();
        if ($verifier->verify($jws)) {
            // The notification is valid

            $decodedPayload = V2DecodedPayload::fromJws($jws);
            // Then you have access to the notification attributes

        } else {
            // The notification is invalid
        }

        Log::debug('Hello');
        $input = @file_get_contents("php://input");

        Log::debug('\n paystack webhook called ---- 123' . var_export($input));
    }
}
