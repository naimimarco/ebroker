<?php
namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Feature;
use App\Models\Setting;
use App\Models\Projects;
use App\Models\Property;
use App\Models\UserPackage;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Models\PackageFeature;
use App\Models\UserPackageLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Intl\Currencies;

class HelperService {
    public static function currencyCode(){
        $currencies = Currencies::getNames();
        $currenciesArray = array();
        foreach ($currencies as $key => $value) {
            $currenciesArray[] = array(
                'currency_code' => $key,
                'currency_name' => $value
            );
        }
        return $currenciesArray;
    }

    public static function getCurrencyData($code){
        $name = Currencies::getName($code);
        $currencySymbol = Currencies::getSymbol($code);
        return array('code' => $code, 'name' => $name, 'symbol' => $currencySymbol);
    }

    // Generate Token
    public static function generateToken(){
        return bin2hex(random_bytes(50)); // Generates a secure random token
    }

    // Store Token
    public static function storeToken($email,$token){
        $expiresAt = now()->addMinutes(60); // Set token to expire after 60 minutes
        PasswordReset::updateOrCreate(
            array(
                'email' => $email
            ),
            array(
                'token' => $token,
                'expires_at' => $expiresAt,
            )
        );
        return true;
    }

    // Verify Token
    public static function verifyToken($token){
        $record = PasswordReset::where('token', $token)->where('expires_at', '>', now())->first();
        if ($record) {
            return $record->email;
        } else {
            return false;
        }
    }

    // Make Token Expire
    public static function expireToken($email){
        $expiresAt = now(); // Set token to expire after 60 minutes
        PasswordReset::updateOrCreate(
            array(
                'email' => $email
            ),
            array(
                'expires_at' => $expiresAt,
            )
        );
        return true;
    }

    public static function getEmailTemplatesTypes($type = null){
        // Return required data if type is passed
        if($type){
            switch ($type) {
                case 'verify_mail':
                    return array(
                        'title' => 'Verify Email Account',
                        'type' => 'verify_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'otp','is_condition' => false,
                            ],
                        )
                    );
                case 'reset_password':
                    return array(
                        'title' => 'Password Reset Mail',
                        'type' => 'password_reset_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'link','is_condition' => false,
                            ],
                        )
                    );
                case 'welcome_mail':
                    return array(
                        'title' => 'Welcome Mail',
                        'type' => 'welcome_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                        )
                    );
                case 'property_status':
                    return array(
                        'title' => 'Property status change by admin',
                        'type' => 'property_status_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                            [
                                'name' => 'property_name','is_condition' => false,
                            ],
                            [
                                'name' => 'status','is_condition' => false,
                            ],
                            [
                                'name' => 'reject_reason','is_condition' => false,
                            ],
                        )
                    );
                case 'project_status':
                    return array(
                        'title' => 'Project status change by admin',
                        'type' => 'project_status_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                            [
                                'name' => 'project_name','is_condition' => false,
                            ],
                            [
                                'name' => 'status','is_condition' => false,
                            ],
                            [
                                'name' => 'reject_reason','is_condition' => false,
                            ],
                        )
                    );
                case 'property_ads_status':
                    return array(
                        'title' => 'Property Advertisement status change by admin',
                        'type' => 'property_ads_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                            [
                                'name' => 'property_name','is_condition' => false,
                            ],
                            [
                                'name' => 'advertisement_status','is_condition' => false,
                            ],
                        )
                    );
                case 'user_status':
                    return array(
                        'title' => 'User account active de-active status',
                        'type' => 'user_status_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                            [
                                'name' => 'status','is_condition' => false,
                            ],
                        )
                    );
                case 'agent_verification_status':
                    return array(
                        'title' => 'Agent Verification Status',
                        'type' => 'agent_verification_status_mail_template',
                        'required_fields' => array(
                            [
                                'name' => 'app_name','is_condition' => false,
                            ],
                            [
                                'name' => 'user_name','is_condition' => false,
                            ],
                            [
                                'name' => 'status','is_condition' => false,
                            ],
                        )
                    );
            }
        }

        // Return All if no type is passed
        return array(
            [
                'title' => 'Verify Account Email Account',
                'type' => 'verify_mail',
            ],
            [
                'title' => 'Password Reset Mail',
                'type' => 'reset_password',
            ],
            [
                'title' => 'Welcome Mail',
                'type' => 'welcome_mail',
            ],
            [
                'title' => 'Property status change by admin',
                'type' => 'property_status',
            ],
            [
                'title' => 'Project status change by admin',
                'type' => 'project_status',
            ],
            [
                'title' => 'Property Advertisement status change by admin',
                'type' => 'property_ads_status',
            ],
            [
                'title' => 'User account active de-active status',
                'type' => 'user_status',
            ],
            [
                'title' => 'Agent Verification Status',
                'type' => 'agent_verification_status',
            ],
        );
    }


    public static function replaceEmailVariables($templateContent, $variables){
        foreach ($variables as $key => $variable) {

            // Create the placeholder format
            $placeholder = '{' . $key . '}';
            $endPlaceHolderPair = "{end_$key}";
            if (strpos($templateContent, $placeholder) !== false && strpos($templateContent, $endPlaceHolderPair) !== false) {
                $pattern=$placeholder.$endPlaceHolderPair;
                $templateContent = str_replace($pattern, $variable, $templateContent);
            }else{
                // Replace the placeholder with the variable format
                $templateContent = str_replace($placeholder, $variable, $templateContent);
            }
        }
        return $templateContent;
    }

    public static function sendMail($data, $requiredEmailException = false){
        try {
            $adminMail = env('MAIL_FROM_ADDRESS');
            Mail::send('mail-templates.mail-template', $data, function ($message) use ($data, $adminMail) {
                $message->to($data['email'])->subject($data['title']);
                $message->from($adminMail, 'Admin');
            });
        } catch (Exception $e) {
            if($requiredEmailException == true){
                DB::rollback();
                throw $e;
            }

            if (Str::contains($e->getMessage(), [
                'Failed',
                'Mail',
                'Mailer',
                'MailManager'
                ])) {
                    Log::error("Cannot send mail, there is issue with mail configuration.");
            } else {
                $logMessage = "Send Mail for property feature status changed";
                Log::error($logMessage . ' ' . $e->getMessage() . '---> ' . $e->getFile() . ' At Line : ' . $e->getLine());
            }
        }
    }
    public static function getFeatureList(){
        try {
            $features = Feature::where('status',1)->get();
            return $features;
        } catch (Exception $e) {
            Log::error('Issue in Get Feature list of Helper Service :- '.$e->getMessage());
            return array();
        }
    }

    public static function getSettingData($type){
        $settingData = Setting::where('type',$type)->pluck('data')->first();
        return !empty($settingData) ? $settingData : null;
    }

    public static function getMultipleSettingData(array $types, $raw = false){
        $settingData = Setting::whereIn('type',$types)->get();
        if(!empty($settingData)){
            $data = array();
            foreach ($settingData as $setting) {
                $data[$setting->type] = $setting->data;
            }
            return $data;
        }
        return null;
    }

    public static function getActivePaymentGateway(){
        try {
            $paymentMethodTypes = array('stripe_gateway','razorpay_gateway','paystack_gateway','paypal_gateway','flutterwave_status');
            $settingsData = Setting::whereIn('type',$paymentMethodTypes)->get();
            foreach ($settingsData as $key => $setting) {
                if($setting->data == 1){
                    return $setting->type;
                }
            }
            return 'none';
        } catch (Exception $e) {
            Log::error('Issue in Get Active Payment Gateway function of Helper Service :- '.$e->getMessage());
            return false;
        }
    }


    public static function getActivePaymentDetails(){
        try {
            $getActivePaymentName = self::getActivePaymentGateway();
            switch ($getActivePaymentName) {
                case 'stripe_gateway':
                    $types = array('stripe_currency','stripe_gateway','stripe_publishable_key','stripe_secret_key');
                    $data = array('payment_method' => 'stripe');
                    return array_merge($data,self::getMultipleSettingData($types));
                    break;
                case 'razorpay_gateway':
                    $types = array('razorpay_gateway','razor_key','razor_secret','razorpay_webhook_url','razor_webhook_secret');
                    $data = array('payment_method' => 'razorpay');
                    return array_merge($data,self::getMultipleSettingData($types));
                    break;
                case 'paystack_gateway':
                    $types = array('paystack_secret_key','paystack_public_key','paystack_currency');
                    $data = array('payment_method' => 'paystack');
                    return array_merge($data,self::getMultipleSettingData($types));
                    break;
                case 'paypal_gateway':
                    $types = array('paypal_business_id','paypal_currency','switch_sandbox_mode');
                    $data = array('payment_method' => 'paypal');
                    return array_merge($data,self::getMultipleSettingData($types));
                    break;
                case 'flutterwave_status':
                    $types = array('flutterwave_public_key','flutterwave_secret_key','flutterwave_webhook_url','flutterwave_currency',' flutterwave_status');
                    $data = array('payment_method' => 'flutterwave');
                    return array_merge($data,self::getMultipleSettingData($types));
                    break;

                default:
                    return false;
                    break;
            }
        } catch (Exception $e) {
            Log::error('Issue in Get Payment Details function of Helper Service :- '.$e->getMessage());
            return false;
        }
    }

    public static function changeEnv($updateData = array()): bool {
        if (count($updateData) > 0) {
            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');
            // Split string on every " " and write into array
            $env = preg_split('/\r\n|\r|\n/', $env);
            $env_array = [];
            foreach ($env as $env_value) {
                if (empty($env_value)) {
                    //Add and Empty Line
                    $env_array[] = "";
                    continue;
                }

                $entry = explode("=", $env_value, 2);
                $env_array[$entry[0]] = $entry[0] . "=\"" . str_replace("\"", "", $entry[1]) . "\"";
            }

            foreach ($updateData as $key => $value) {
                $env_array[$key] = $key . "=\"" . str_replace("\"", "", $value) . "\"";
            }
            // Turn the array back to a String
            $env = implode("\n", $env_array);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);
            return true;
        }
        return false;
    }

    public static function getAllActivePackageIds($userId){
        // Retrieve user packages with end_time less than or equal to current date
        $packageIds = UserPackage::where('user_id', $userId)
            ->onlyActive()
            ->pluck('package_id');
        return $packageIds;
    }
    public static function getActivePackage($userId, $packageId){
        // Retrieve user packages with end_time less than or equal to current date
        $userPackages = UserPackage::where('user_id', $userId)
            ->where('package_id', $packageId)
            ->onlyActive()
            ->first();
        return $userPackages;
    }

    public static function getFeatureId($type){
        try {
            $featureQuery = Feature::query();
            switch ($type) {
                case 'property_list':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PROPERTY_LIST'));
                    break;
                case 'project_list':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PROJECT_LIST'));
                    break;
                case 'property_feature':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PROPERTY_FEATURE'));
                    break;
                case 'project_feature':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PROJECT_FEATURE'));
                    break;
                case 'mortgage_calculator_detail':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.MORTGAGE_CALCULATOR_DETAIL'));
                    break;
                case 'premium_properties':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PREMIUM_PROPERTIES'));
                    break;
                case 'project_access':
                    $featureQuery = $featureQuery->clone()->where('name',config('constants.FEATURES.PROJECT_ACCESS'));
                    break;
                default:
                    Log::error('Type not allowed in getFeatureId function of HelperService');
                    return false;
                    break;
            }
            return $featureQuery->pluck('id')->first();
        } catch (Exception $e) {
            Log::error('Issue in Get Feature ID HelperService Function => '.$e->getMessage());
            return false;
        }
    }


    public static function updatePackageLimit($type,$getPackageDataReturn = false)
    {
        try {
            $featureTypes = array('property_list', 'property_feature', 'project_list', 'project_feature', 'mortgage_calculator_detail', 'premium_properties', 'project_access');
            if (!in_array($type, $featureTypes)) {
                ApiResponseService::validationError("Invalid Feature Type");
            }

            $featureId = HelperService::getFeatureId($type);

            if (collect($featureId)->isEmpty()) {
                ApiResponseService::validationError("Invalid Feature Type");
            }

            if(Auth::guard('sanctum')->check()){
                $loggedInUserData = Auth::guard('sanctum')->user();
            }else{
                ApiResponseService::validationError('Package not found');
            }

            $packagesIds = HelperService::getAllActivePackageIds($loggedInUserData->id);
            if (collect($packagesIds)->isEmpty()) {
                ApiResponseService::validationError('Package not available');
            }
            $userPackageIds = UserPackage::whereIn('package_id', $packagesIds)->where('user_id',$loggedInUserData->id)->pluck('id');

            $packageFeatureQuery = PackageFeature::where('feature_id', $featureId)->whereIn('package_id', $packagesIds);
            $packageFeatureIds = $packageFeatureQuery->clone()->pluck('id');

            if (collect($packageFeatureIds)->isEmpty()) {
                ApiResponseService::validationError('Package not available');
            }

            $packageFeatures = $packageFeatureQuery->clone()->with(['user_package_limits' => function ($query) use($userPackageIds){
                $query->whereIn('user_package_id', $userPackageIds);
            },'package'])->get();

            foreach ($packageFeatures as $packageFeatureData) {
                if ($packageFeatureData->limit_type == 'unlimited') {
                    if($getPackageDataReturn == true){
                        return $packageFeatureData->package;
                    }else{
                        return true;
                    }
                }
                if($packageFeatureData->user_package_limits){
                    foreach ($packageFeatureData->user_package_limits as $package) {
                        if ($package->total_limit > $package->used_limit) {
                            // Deduct one limit
                            $package->used_limit += 1;
                            $package->save();
                            if($getPackageDataReturn == true){
                                return $packageFeatureData->package;
                            }else{
                                return true;
                            }
                        }
                    }
                }
            }

            ApiResponseService::validationError("Limit Not Available");
        } catch (Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Issue in update package limit helper function');
        }
    }


    public static function checkPackageLimit($type, $getCheckDataInReturn = false){
        try{
            $packageAvailable = false;
            $featureAvailable = false;
            $limitAvailable = false;
            $loggedInUserData = null;
            if(Auth::guard('sanctum')->check()){
                $loggedInUserData = Auth::guard('sanctum')->user();
            }
            $featureId = HelperService::getFeatureId($type);

            if (!empty($featureId)) {
                if($loggedInUserData){
                    $packageIds = HelperService::getAllActivePackageIds($loggedInUserData->id);
                }
                if (isset($packageIds) && collect($packageIds)->isNotEmpty()) {
                    $packageAvailable = true;
                    $userPackages = UserPackage::whereIn('package_id', $packageIds)->where('user_id',$loggedInUserData->id)->get();
                    $userPackageIds = $userPackages->pluck('id');

                    $packageFeatureQuery = PackageFeature::where('feature_id', $featureId)->whereIn('package_id', $packageIds);
                    $getPackageFeatureData = $packageFeatureQuery->clone()->get();
                    if(collect($getPackageFeatureData)->isNotEmpty()){
                        $featureAvailable = true;
                        foreach ($getPackageFeatureData as $packageFeatureData) {
                            if($packageFeatureData->limit_type == 'unlimited'){
                                $limitAvailable = true;
                            }else if($packageFeatureData->limit_type == 'limited'){
                                $packageFeatureIds = $packageFeatureQuery->clone()->pluck('id');
                                $userPackageLimit = UserPackageLimit::whereIn('user_package_id', $userPackageIds)->whereIn('package_feature_id', $packageFeatureIds)->get();
                                if (collect($userPackageLimit)->isNotEmpty()) {
                                    foreach ($userPackageLimit as $package) {
                                        if($package->total_limit > $package->used_limit){
                                            $limitAvailable = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if($getCheckDataInReturn == true){
                return [
                    'package_available' => $packageAvailable,
                    'feature_available' => $featureAvailable,
                    'limit_available' => $limitAvailable,
                ];
            }else{
                if($packageAvailable){
                    if($featureAvailable){
                        if($limitAvailable){
                            return true;
                        }else{
                            ApiResponseService::validationError("Limit Not Available");
                        }
                    }else{
                        ApiResponseService::validationError("Feature Not Available");
                    }
                }else{
                    ApiResponseService::validationError("Package Not Available");
                }
            }

        } catch (Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Issue in check package limit helper function');
        }
    }
    public static function incrementTotalClick($type,$id = null,$slugId = null){
        if($type == 'project'){
            Projects::where('id',$id)->orWhere('slug_id',$slugId)->increment('total_click');
        }else if($type == 'property'){
            Property::where('id',$id)->orWhere('slug_id',$slugId)->increment('total_click');
        }
        return true;
    }


    // Convert a UTC datetime to app timezone
    public static function toAppTimezone($dateTime)
    {
        $timezone = self::getSettingData('timezone');
        if ($dateTime instanceof Carbon) {
            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, 'UTC')->setTimezone($timezone);
        }
        return $dateTime;
    }

    // public static function getIntervalOfDate($endDate){
    //     $startDate = Carbon::now();
    //     $endDate = Carbon::parse($endDate);
    //     $diff = $startDate->diff($endDate);

    //     if ($diff->y > 0) {
    //         $interval = $diff->format('%y years left');
    //     } elseif ($diff->m > 0) {
    //         $interval = $diff->format('%m months left');
    //     } elseif ($diff->d > 0) {
    //         $interval = $diff->format('%d days left');
    //     } elseif ($diff->h > 0) {
    //         $interval = $diff->format('%h hours left');
    //     } elseif ($diff->i > 0) {
    //         $interval = $diff->format('%i minutes left');
    //     } else {
    //         $interval = $diff->format('%s seconds left');
    //     }
    //     return $interval ?? null;
    // }


    public static function getFeatureNames(){
        $featureNames = array(
            config('constants.FEATURES.PROPERTY_LIST'),
            config('constants.FEATURES.PROPERTY_FEATURE'),
            config('constants.FEATURES.PROJECT_LIST'),
            config('constants.FEATURES.PROJECT_FEATURE'),
            config('constants.FEATURES.MORTGAGE_CALCULATOR_DETAIL'),
            config('constants.FEATURES.PREMIUM_PROPERTIES'),
            config('constants.FEATURES.PROJECT_ACCESS'),
        );
        return $featureNames;
    }

}

?>
