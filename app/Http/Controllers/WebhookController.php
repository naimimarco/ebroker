<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use Stripe\Webhook;
use Razorpay\Api\Api;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Payments;
use App\Libraries\Paypal;
use App\Models\Usertokens;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\PackageFeature;
use App\Services\HelperService;
use App\Models\UserPackageLimit;
use App\Services\ResponseService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Exception;
use KingFlamez\Rave\Facades\Rave as Flutterwave;


class WebhookController extends Controller
{
    public function paystack()
    {
        try {
            // only a post with paystack signature header gets our attention
            if (!array_key_exists('HTTP_X_PAYSTACK_SIGNATURE', $_SERVER) || (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')) {
                echo "Signature not found";
                http_response_code(400);
                exit(0);
            }
            $inputJSON = @file_get_contents("php://input");
            $input = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR);

            // Calculate HMAC
            $paystackSecretKey = HelperService::getSettingData('paystack_secret_key');
            $headerSignature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
            $calculatedHMAC = hash_hmac('sha512', $inputJSON, $paystackSecretKey);
            if (!hash_equals($headerSignature, $calculatedHMAC)) {
                echo "Signature does not match";
                http_response_code(400);
                exit(0);
            }
            Log::info('Paystack Webhook Signature Verified Successfully');

            $transactionId = $input['data']['id'];
            $paymentTransactionId = $input['data']['metadata']['payment_transaction_id'];
            switch ($input['event']) {
                case 'charge.success':
                    $response = $this->assignPackage($paymentTransactionId,$transactionId);
                    if ($response['error']) {
                        Log::error("Paystack Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
                case 'charge.failed':
                    $response = $this->failedTransaction($paymentTransactionId);
                    if ($response['error']) {
                        Log::error("Paystack Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
            }
        }catch (Throwable $e) {
            Log::error("Paystack Webhook : Error occurred", [$e->getMessage() . ' --> ' . $e->getFile() . ' At Line : ' . $e->getLine()]);
            http_response_code(400);
            exit();
        }
    }
    public function razorpay(Request $request)
    {
        try {
            // get the json data of payment
            $webhookBody = $request->getContent();
            $webhookBody = file_get_contents('php://input');
            $data = json_decode($webhookBody, false, 512, JSON_THROW_ON_ERROR);


            // Get Config Data From Settings
            $razorPayConfigData = HelperService::getMultipleSettingData(array('razor_key','razor_secret','razor_webhook_secret'));
            $razorPayApiKey = $razorPayConfigData['razor_key'];
            $razorPaySecretKey = $razorPayConfigData['razor_secret'];
            $webhookSecret = $razorPayConfigData['razor_webhook_secret'];

            // gets the signature from header
            $webhookSignature = $request->header('X-Razorpay-Signature');

            //checks the signature
            $expectedSignature = hash_hmac("SHA256", $webhookBody, $webhookSecret);

            // Initiate Razorpay Class
            $api = new Api($razorPayApiKey, $razorPaySecretKey);

            if ($expectedSignature == $webhookSignature) {
                $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);

                switch ($data->event) {
                    case 'payment.captured':
                        $entityData = $data->payload->payment->entity;
                        $transactionId = $entityData->id;
                        $paymentTransactionId = $entityData->notes->payment_transaction_id;
                        $response = $this->assignPackage($paymentTransactionId,$transactionId);
                        if ($response['error']) {
                            Log::error("Razorpay Webhook : ", [$response['message']]);
                        }
                        http_response_code(200);
                        break;
                    case 'payment.failed':
                        $entityData = $data->payload->payment->entity;
                        $paymentTransactionId = $entityData->notes->payment_transaction_id;
                        $response = $this->failedTransaction($paymentTransactionId);
                        if ($response['error']) {
                            Log::error("Razorpay Webhook : ", [$response['message']]);
                        }
                        http_response_code(200);
                        break;
                }

                Log::info("Payment Done Successfully");
            } else {
                Log::error("Razorpay Signature Not Matched Payment Failed !!!!!!");
            }
        } catch (Exception $e) {
            Log::error("Razorpay Webhook : Error occurred", [$e->getMessage() . ' --> ' . $e->getFile() . ' At Line : ' . $e->getLine()]);
            http_response_code(400);
            exit();
        }
    }
    public function paypal(Request $request)
    {
        Log::info('Paypal Webhook Called');
        $input = file_get_contents('php://input');

        $paypal = new Paypal();
        // Check if $input is not empty
        if (!empty($input)) {
            parse_str($input, $arr);
            $ipnCheck = $paypal->validate_ipn($arr);
            if ($ipnCheck) {
                Log::debug('paypal IPN valid');
            } else {
                Log::debug('paypal IPN Invalid');
            }
            switch ($arr['payment_status']) {
                case 'Completed':
                    $transactionId = $arr['txn_id'];
                    $custom_data = explode(',', $arr['custom']);
                    $paymentTransactionId = $custom_data[0];
                    $response = $this->assignPackage($paymentTransactionId,$transactionId);
                    if ($response['error']) {
                        Log::error("Paypal Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
                case 'Failed':
                case 'Denied':
                case 'Expired':
                case 'Voided':
                    $custom_data = explode(',', $arr['custom']);
                    $paymentTransactionId = $custom_data[0];
                    $response = $this->failedTransaction($paymentTransactionId);
                    if ($response['error']) {
                        Log::error("Paypal Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
            }
        } else {
            Log::debug('input is empty');
        }
    }
    public function stripe(Request $request)
    {
        Log::info('Stripe Webhook Called');
        // Get File Contents
        $payload = $request->getContent();
        // Get Webhook Secret From Webhook
        $secret = system_setting('stripe_webhook_secret_key');
        // Get Signature from Header
        $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        try {
            // Create A Event
            $event = Webhook::constructEvent($payload, $signatureHeader, $secret);
            // Get Transaction ID
            $transactionID = $event->data->object->id;
            // Get Payment Transaction ID
            $paymentTransactionId = $event->data->object->metadata->payment_transaction_id;
            switch ($event->type) {
                case "payment_intent.succeeded":
                    $response = $this->assignPackage($paymentTransactionId,$transactionID);
                    if ($response['error']) {
                        Log::error("Stripe Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
                case 'payment_intent.payment_failed':
                    $response = $this->failedTransaction($paymentTransactionId);
                    if ($response['error']) {
                        Log::error("Stripe Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    break;
                default:
                    Log::error('Stripe Webhook : Received unknown event type');
                    break;
            }
            Log::info('Stripe Webhook received Successfully');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid Signature Log
            return Log::error('Stripe Webhook verification failed');
        } catch (\Exception $e) {
            // Other Error Exception
            return Log::error('Stripe Webhook failed');
        }
    }
    public function flutterwave(Request $request){
        try {
            //This verifies the webhook is sent from Flutterwave
            $verified = Flutterwave::verifyWebhook();
            $requestData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            // Verify the transaction
            if ($verified) {
                $verificationData = Flutterwave::verifyTransaction($requestData['data']['id']);
                if ($verificationData['status'] === 'success') {
                    $data = $verificationData['data'];
                    $transactionId = $data['id'];
                    $metaData = $data['meta'];
                    $paymentTransactionId = $metaData['payment_transaction_id'];
                    $response = $this->assignPackage($paymentTransactionId,$transactionId);
                    if ($response['error']) {
                        Log::error("Flutterwave Webhook : ", [$response['message']]);
                    }
                    http_response_code(200);
                    return true;
                }else{
                    $data = $verificationData['data'];
                    $paymentTransactionId = $data['meta']['payment_transaction_id'] ?? null;
                    if ($paymentTransactionId) {
                        $response = $this->failedTransaction($paymentTransactionId);
                        if ($response['error']) {
                            Log::error("Flutterwave Webhook : ", [$response['message']]);
                        }
                    } else {
                        Log::error('Flutterwave Webhook: Missing payment_transaction_id in metadata');
                    }
                    Log::error('Flutterwave Webhook Status Not Succeeded');
                }
            }else{
                Log::error('Flutterwave Webhook Verification Error');

                // Try to find the transaction in our database by the transaction reference
                // First extract the transaction reference from the request data
                $requestData = $request->all();
                $paymentTransactionId = null;

                if (isset($requestData['meta_data']['payment_transaction_id'])) {
                    $paymentTransactionId = $requestData['meta_data']['payment_transaction_id'];
                } elseif (isset($requestData['data']['tx_ref'])) {
                    // Get the tx_ref value
                    $txRef = $requestData['data']['tx_ref'];

                    // Try to find transaction by txRef in order_id field
                    $paymentTransaction = PaymentTransaction::where('order_id', $txRef)
                        ->where('payment_gateway', 'Flutterwave')
                        ->where('payment_status', 'pending')
                        ->first();

                    if ($paymentTransaction) {
                        $paymentTransactionId = $paymentTransaction->id;
                    }
                }

                if ($paymentTransactionId) {
                    $response = $this->failedTransaction($paymentTransactionId);
                    if ($response['error']) {
                        Log::error("Flutterwave Webhook (Failed Verification): ", [$response['message']]);
                    }
                    http_response_code(200);
                } else {
                    Log::error('Flutterwave Webhook: Could not find payment transaction to mark as failed');
                    http_response_code(400);
                }
            }
        }catch (\Exception $e) {
            // Other Error Exception
            Log::error('Flutterwave Webhook failed: ' . $e->getMessage());
            http_response_code(400);
            return;
        }
    }

    public function paystackSuccessCallback(){
        ResponseService::successResponse("Payment done successfully.");
    }



     /**
     * Success Business Login
     * @param $payment_transaction_id
     * @param $user_id
     * @param $package_id
     * @return array
     */
    private function assignPackage($paymentTransactionId,$transactionId) {
        try {
            $paymentTransactionData = PaymentTransaction::where('id', $paymentTransactionId)->first();
            if ($paymentTransactionData == null) {
                Log::error("Payment Transaction id not found");
                ResponseService::errorResponse("Payment Transaction id not found");
            }

            if ($paymentTransactionData->payment_status == "succeed") {
                Log::info("Transaction Already Succeed");
                ResponseService::errorResponse("Transaction Already Succeed");
            }

            DB::beginTransaction();
            $paymentTransactionData->update(['transaction_id' => $transactionId,'payment_status' => "success"]);

            $packageId = $paymentTransactionData->package_id;
            $userId = $paymentTransactionData->user_id;


            $package = Package::findOrFail($packageId);

            if (!empty($package)) {
                // Assign Package to user
                $userPackage = UserPackage::create([
                    'package_id'  => $packageId,
                    'user_id'     => $userId,
                    'start_date'  => Carbon::now(),
                    'end_date'    => $package->package_type == "unlimited" ? null : Carbon::now()->addHours($package->duration),
                ]);
                DB::commit();

                DB::beginTransaction();
                // Assign limited count feature to user with limits
                $packageFeatures = PackageFeature::where(['package_id' => $packageId, 'limit_type' => 'limited'])->get();
                if(collect($packageFeatures)->isNotEmpty()){
                    $userPackageLimitData = array();
                    foreach ($packageFeatures as $key => $feature) {
                        $userPackageLimitData[] = array(
                            'user_package_id' => $userPackage->id,
                            'package_feature_id' => $feature->id,
                            'total_limit' => $feature->limit,
                            'used_limit' => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        );
                    }

                    if(!empty($userPackageLimitData)){
                        UserPackageLimit::insert($userPackageLimitData);
                    }
                }
            }

            $userFcmTokensDB = Usertokens::where('customer_id', $userId)->pluck('fcm_id');
            if(collect($userFcmTokensDB)->isNotEmpty()){
                $title = "Package Purchased";
                $body = 'Amount :- ' . $paymentTransactionData->amount;

                $registrationIDs = array_filter($userFcmTokensDB->toArray());

                $fcmMsg = array(
                    'title' => $title,
                    'message' => $body,
                    "image" => null,
                    'type' => 'default',
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',

                );
                send_push_notification($registrationIDs, $fcmMsg);

                Notifications::create([
                    'title' => $title,
                    'message' => $body,
                    'image' => '',
                    'type' => '2',
                    'send_type' => '0',
                    'customers_id' => $userId,
                ]);
            }
            DB::commit();
            ResponseService::successResponse("Transaction Verified Successfully");

        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage() . "WebhookController -> assignPackage");
            ResponseService::errorResponse();
        }
    }


    /**
     * Failed Business Logic
     * @param $paymentTransactionId
     * @return array
     */
    private function failedTransaction($paymentTransactionId) {
        try {
            $paymentTransactionData = PaymentTransaction::find($paymentTransactionId);
            if (!$paymentTransactionData) {
                Log::error("Payment Transaction id not found");
                return ResponseService::errorResponse("Payment Transaction id not found");
            }

            if ($paymentTransactionData->payment_status == "failed") {
                Log::info("Transaction Already Failed");
                return ResponseService::errorResponse("Transaction Already Failed");
            }

            DB::beginTransaction();
            $paymentTransactionData->update(['payment_status' => "failed"]);

            $userId = $paymentTransactionData->user_id;
            $title = "Package Payment Failed";
            $body = 'Amount :- ' . $paymentTransactionData->amount;

            $userFcmTokensDB = Usertokens::where('customer_id', $userId)->pluck('fcm_id');
            if(collect($userFcmTokensDB)->isNotEmpty()){
                $registrationIDs = array_filter($userFcmTokensDB->toArray());

                $fcmMsg = array(
                    'title' => $title,
                    'message' => $body,
                    "image" => null,
                    'type' => 'default',
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',

                );
                send_push_notification($registrationIDs, $fcmMsg);
            }
            Notifications::create([
                'title' => $title,
                'message' => $body,
                'image' => '',
                'type' => '2',
                'send_type' => '0',
                'customers_id' => $userId,
            ]);

            DB::commit();
            ResponseService::successResponse("Transaction Failed Successfully");
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage() . "WebhookController -> failedTransaction");
            ResponseService::errorResponse();
        }
    }
}
