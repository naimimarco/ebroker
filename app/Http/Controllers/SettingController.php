<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use TypeError;
use ZipArchive;
use App\Models\Setting;
use App\Models\Language;
use Stripe\Tax\Settings;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\HelperService;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Intl\Currencies;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request as RequestFacades;
use App\Models\PaymentTransaction;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private CachingService $cache;
    public function __construct(CachingService $cache) {
        $this->cache = $cache;
    }

    public function index()
    {
        $type = last(request()->segments());

        $type1 = str_replace('-', '_', $type);

        if (!has_permissions('read', $type1)) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $data = Setting::select('data')->where('type', $type1)->pluck('data')->first();
        return view('settings.' . $type, compact('data', 'type'));
    }

    public function settings(Request $request)
    {
        $permissionType = str_replace("-","_",$request->type);

        if (!has_permissions('update', $permissionType)) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {

            $request->validate([
                'data' => 'required',
            ]);

            $type1 = $request->type;
            if ($type1 != '') {
                $message = Setting::where('type', $type1)->first();
                if (empty($message)) {
                    Setting::create([
                        'type' => $type1,
                        'data' => $request->data
                    ]);
                } else {
                    $data['data'] = $request->data;
                    Setting::where('type', $type1)->update($data);
                }
                return redirect(str_replace('_', '-', $type1))->with('success', trans("Data Updated Successfully"));
            } else {
                return redirect(str_replace('_', '-', $type1))->with('error', 'Something Wrong');
            }
        }
    }

    public function systemSettingsIndex(){
        $stripe_currencies = ["USD", "AED", "AFN", "ALL", "AMD", "ANG", "AOA", "ARS", "AUD", "AWG", "AZN", "BAM", "BBD", "BDT", "BGN", "BIF", "BMD", "BND", "BOB", "BRL", "BSD", "BWP", "BYN", "BZD", "CAD", "CDF", "CHF", "CLP", "CNY", "COP", "CRC", "CVE", "CZK", "DJF", "DKK", "DOP", "DZD", "EGP", "ETB", "EUR", "FJD", "FKP", "GBP", "GEL", "GIP", "GMD", "GNF", "GTQ", "GYD", "HKD", "HNL", "HTG", "HUF", "IDR", "ILS", "INR", "ISK", "JMD", "JPY", "KES", "KGS", "KHR", "KMF", "KRW", "KYD", "KZT", "LAK", "LBP", "LKR", "LRD", "LSL", "MAD", "MDL", "MGA", "MKD", "MMK", "MNT", "MOP", "MRO", "MUR", "MVR", "MWK", "MXN", "MYR", "MZN", "NAD", "NGN", "NIO", "NOK", "NPR", "NZD", "PAB", "PEN", "PGK", "PHP", "PKR", "PLN", "PYG", "QAR", "RON", "RSD", "RUB", "RWF", "SAR", "SBD", "SCR", "SEK", "SGD", "SHP", "SLE", "SOS", "SRD", "STD", "SZL", "THB", "TJS", "TOP", "TTD", "TWD", "TZS", "UAH", "UGX", "UYU", "UZS", "VND", "VUV", "WST", "XAF", "XCD", "XOF", "XPF", "YER", "ZAR", "ZMW"];
        $languages = Language::all();


        $paypalCurrencies = array(
            'AUD' => 'Australian Dollar',
            'BRL' => 'Brazilian Real',
            'CAD' => 'Canadian Dollar',
            'CNY' => 'Chinese Renmenbi',
            'CZK' => 'Czeck Koruna',
            'DKK' => 'Danish Krone',
            'EUR' => 'Euro',
            'HKD' => 'Hong Kong Dollar',
            'HUF' => 'Hungarian Forint',
            'ILS' => 'Israeli New Sheqel',
            'JPY' => 'Japanese Yen',
            'MYR' => 'Malaysian Ringgit',
            'MXN' => 'Mexican Peso',
            'NOK' => 'Norwegian Krone',
            'TWD' => 'New Taiwan dollar',
            'NZD' => 'New Zealand Dollar',
            'NOK' => 'Norwegian krone',
            'PHP' => 'Philippine Peso',
            'PLN' => 'Polish Zloty',
            'GBP' => 'Pound Sterling',
            'SGD' => 'Singapore Dollar',
            'SEK' => 'Swedish Krona',
            'CHF' => 'Swiss Franc',
            'THB' => 'Thai Baht',
            'USD' => 'U.S. Dollar'
        );
        $listOfCurrencies = HelperService::currencyCode();

        $bankDetailsFieldsQuery = system_setting('bank_details');
        if(isset($bankDetailsFieldsQuery) && !empty($bankDetailsFieldsQuery)){
            $bankDetailsFields = json_decode($bankDetailsFieldsQuery, true);
        }else{
            $bankDetailsFields = [];
        }

        $settingsArray = array(
            'company_name', 'company_email', 'company_tel1', 'company_tel2', 'latitude', 'longitude', 'company_address',
            'currency_code', 'currency_symbol', 'timezone', 'min_radius_range', 'max_radius_range', 'place_api_key', 'unsplash_api_key', 'appstore_id', 'playstore_id', 'number_with_suffix', 'svg_clr','distance_option','system_color','web_url','text_property_submission','auto_approve_edited_listings',
            'number_with_otp_login','otp_service_provider','twilio_account_sid','twilio_auth_token','twilio_phone_number','social_login',
            'paypal_business_id','paypal_webhook_url','paypal_currency','paypal_gateway','sandbox_mode',
            'razor_key','razorpay_webhook_url','razor_secret','razorpay_gateway','razor_webhook_secret',
            'paystack_secret_key','paystack_webhook_url','paystack_currency','paystack_gateway','paystack_public_key',
            'stripe_publishable_key','stripe_webhook_url','stripe_currency','stripe_gateway','stripe_secret_key','stripe_webhook_secret_key',
            'flutterwave_public_key','flutterwave_secret_key','flutterwave_encryption_key','flutterwave_webhook_url','flutterwave_currency','flutterwave_status',
            'schema_for_deeplink',
            'favicon_icon','company_logo','login_image',
            'bank_transfer_status'
        );
        $systemSettings = HelperService::getMultipleSettingData($settingsArray);

        return view('settings.system-settings', compact('systemSettings', 'languages', 'stripe_currencies','paypalCurrencies', 'listOfCurrencies', 'bankDetailsFields'));
    }

    public function system_settings(Request $request)
    {

        if (!has_permissions('update', 'system_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        try {
            DB::beginTransaction();
            $input = $request->except(['_token', 'btnAdd', 'bank_details_fields']);

            if(($request->has('bank_transfer_status') && $request->bank_transfer_status == 0) && $request->razorpay_gateway == 0 && $request->paystack_gateway == 0 && $request->flutterwave_status == 0 && $request->stripe_gateway == 0 && $request->paypal_gateway == 0){
                ResponseService::errorResponse("Please enable at least one payment gateway");
            }

            $logoDestinationPath = public_path('assets/images/logo');
            $backgroundDestinationPath = public_path('assets/images/bg');

            if($request->hasFile('favicon_icon')){
                $filename = 'favicon.'.$request->file('favicon_icon')->getClientOriginalExtension();

                // Get Data from Settings table
                $faviconDatabaseData = system_setting('favicon_icon');
                $databaseData = !empty($faviconDatabaseData) ? $faviconDatabaseData : null;

                $input['favicon_icon'] = handleFileUpload($request, 'favicon_icon', $logoDestinationPath, $filename, $databaseData);
            }
            if($request->hasFile('company_logo')){
                $filename = 'logo.'.$request->file('company_logo')->getClientOriginalExtension();

                // Get Data from Settings table
                $companyLogoDatabaseData = system_setting('company_logo');
                $databaseData = !empty($companyLogoDatabaseData) ? $companyLogoDatabaseData : null;

                $input['company_logo'] = handleFileUpload($request, 'company_logo', $logoDestinationPath, $filename, $databaseData);
            }
            if($request->hasFile('login_image')){
                $filename = 'Login_BG.'.$request->file('login_image')->getClientOriginalExtension();

                // Get Data from Settings table
                $LoginImageDatabaseData = system_setting('company_logo');
                $databaseData = !empty($LoginImageDatabaseData) ? $LoginImageDatabaseData : null;

                $input['login_image'] = handleFileUpload($request, 'login_image', $backgroundDestinationPath, $filename, $databaseData);
            }

            if($request->has('bank_transfer_status')){
                $bankDetailsEnabled = $request->bank_transfer_status;
                if($bankDetailsEnabled == 1){
                    $rules = [
                        'bank_details_fields' => 'required|array',
                    ];

                    $messages = [
                        'bank_details_fields.required' => 'Bank Details Fields is required',
                    ];

                    // Loop through each item to dynamically add rules and custom messages
                    foreach ($request->input('bank_details_fields', []) as $i => $field) {
                        $index = $i + 1;

                        $rules["bank_details_fields.$i.title"] = 'required';
                        $rules["bank_details_fields.$i.value"] = 'required';

                        $messages["bank_details_fields.$i.title.required"] = "Bank Details : $index Title is required";
                        $messages["bank_details_fields.$i.value.required"] = "Bank Details : $index Value is required";
                    }

                    $validator = Validator::make($request->all(), $rules, $messages);

                    if($validator->fails()){
                        ResponseService::validationError($validator->errors()->first());
                    }
                    $input['bank_details'] = json_encode($request->bank_details_fields);

                }
            }

            $envUpdates = [
                'APP_NAME' => $request->company_name,
                'PLACE_API_KEY' => $request->place_api_key,
                'UNSPLASH_API_KEY' => $request->unsplash_api_key,
                'PRIMARY_COLOR' => $request->system_color,
                'PRIMARY_RGBA_COLOR' => $request->rgb_color,
                'PAYPAL_CURRENCY' => $request->paypal_currency,
                'PAYPAL_SANDBOX' => $request->sandbox_mode == 1 ? 1 : 0,
                'FLW_PUBLIC_KEY' => $request->flutterwave_public_key ?? "",
                'FLW_SECRET_KEY' => $request->flutterwave_secret_key ?? "",
                'FLW_SECRET_HASH' => $request->flutterwave_encryption_key ?? "",
                'PAYSTACK_PUBLIC_KEY' => $request->paystack_public_key ?? "",
                'PAYSTACK_SECRET_KEY' => $request->paystack_secret_key ?? "",
                'PAYSTACK_PAYMENT_URL' => "https://api.paystack.co"
            ];

            if($request->has('paypal_business_id') && !empty($request->paypal_business_id)){
                $envUpdates['BUSINESS'] = $request->paypal_business_id;
            }

            $envFile = file_get_contents(base_path('.env'));

            foreach ($envUpdates as $key => $value) {
                // Check if the key exists in the .env file
                if (strpos($envFile, "{$key}=") === false) {
                    // If the key doesn't exist, add it
                    $envFile .= "\n{$key}=\"{$value}\"";
                } else {
                    // If the key exists, replace its value
                    $envFile = preg_replace("/{$key}=.*/", "{$key}=\"{$value}\"", $envFile);
                }
            }

            // Save the updated .env file
            file_put_contents(base_path('.env'), $envFile);


            // Create or update records in the 'settings' table
            foreach ($input as $key => $value) {
                if($key == 'paypal_web_url' && !empty($value)){
                    // remove / from end of value
                    $value = rtrim($value,'/');
                }
                Setting::updateOrCreate(['type' => $key], ['data' => $value]);
            }

            $this->cache->removeSystemCache(config("constants.CACHE.SYSTEM.DEFAULT_LANGUAGE"));

            // Add New Default in Session
            $defaultLanguage = $this->cache->getDefaultLanguage();
            Session::remove('language');
            Session::remove('locale');
            Session::put('language', $defaultLanguage);
            Session::put('locale', $defaultLanguage->code);
            Session::save();
            app()->setLocale($defaultLanguage->code);
            Artisan::call('cache:clear');

            DB::commit();
            ResponseService::successResponse("Data Updated Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Something Went Wrong");
        }
    }

    public function firebase_settings(Request $request)
    {
        if (!has_permissions('update', 'firebase_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $input = $request->all();

            unset($input['btnAdd1']);
            unset($input['_token']);
            foreach ($input as $key => $value) {
                $result = Setting::where('type', $key)->first();
                if (empty($result)) {
                    Setting::create([
                        'type' => $key,
                        'data' => $value
                    ]);
                } else {
                    $data['data'] = ($value) ? $value : '';
                    Setting::where('type', $key)->update($data);
                }
            }
        }
        return redirect()->back()->with('success', trans("Data Updated Successfully"));
    }
    public function system_version()
    {
        if (!has_permissions('read', 'system_update')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('settings.system_version');
    }


    public function show_privacy_policy()
    {
        $appName = env("APP_NAME",'eBroker');
        $privacy_policy = Setting::select('data')->where('type', 'privacy_policy')->first();
        return view('settings.show_privacy_policy', compact('privacy_policy','appName'));
    }

    public function show_terms_conditions()
    {
        $terms_conditions = Setting::select('data')->where('type', 'terms_conditions')->first();
        return view('settings.show_terms_conditions', compact('terms_conditions'));
    }
    public function system_version_setting(Request $request)
    {
        if (!has_permissions('update', 'system_update')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $validator = Validator::make($request->all(), [
            'purchase_code' => 'required',
            'file' => 'required|file|mimes:zip',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $destinationPath = public_path() . '/update/tmp/';
        $app_url = (string)url('/');
        $app_url = preg_replace('#^https?://#i', '', $app_url);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://wrteam.in/validator/ebroker_validator?purchase_code=' . $request->purchase_code . '&domain_url=' . $app_url . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        curl_close($curl);

        $response = json_decode($response, true);
        if ($response['error']) {
            $response = array(
                'error' => true,
                'message' => $response["message"],
                'info' => $info
            );

            return redirect()->back()->with('error', $response["message"]);
        } else {
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, TRUE);
            }

            // zip upload
            $zipfile = $request->file('file');
            $fileName = $zipfile->getClientOriginalName();
            $zipfile->move($destinationPath, $fileName);

            $target_path = base_path();


            $zip = new ZipArchive();
            $filePath = $destinationPath . '/' . $fileName;
            $zipStatus = $zip->open($filePath);
            if ($zipStatus) {
                $zip->extractTo($destinationPath);
                $zip->close();
                unlink($filePath);

                $ver_file = $destinationPath . '/version_info.php';
                $source_path = $destinationPath . '/source_code.zip';
                if (file_exists($ver_file) && file_exists($source_path)) {
                    $ver_file1 = $target_path . '/version_info.php';
                    $source_path1 = $target_path . '/source_code.zip';
                    if (rename($ver_file, $ver_file1) && rename($source_path, $source_path1)) {
                        $version_file = require_once($ver_file1);

                        $current_version = Setting::select('data')->where('type', 'system_version')->pluck('data')->first();
                        if ($current_version == $version_file['current_version']) {
                            $zip1 = new ZipArchive();
                            $zipFile1 = $zip1->open($source_path1);
                            if ($zipFile1 === true) {
                                $zip1->extractTo($target_path);
                                $zip1->close();

                                Artisan::call('migrate');
                                unlink($source_path1);
                                unlink($ver_file1);
                                Setting::where('type', 'system_version')->update([
                                    'data' => $version_file['update_version']
                                ]);

                                $envUpdates = [
                                    'APP_URL' => RequestFacades::root(),
                                ];
                                updateEnv($envUpdates);
                                Artisan::call('optimize:clear');

                                return redirect()->back()->with('success', trans('System Updated Successfully'));
                            } else {
                                unlink($source_path1);
                                unlink($ver_file1);

                                return redirect()->back()->with('error', trans('Something Went Wrong'));
                            }
                        } else if ($current_version == $version_file['update_version']) {
                            unlink($source_path1);
                            unlink($ver_file1);


                            return redirect()->back()->with('error', trans('System Already Updated'));
                        } else {
                            unlink($source_path1);
                            unlink($ver_file1);

                            return redirect()->back()->with('error', $current_version . ' ' . trans('Update your version nearest to it'));
                        }
                    } else {

                        return redirect()->back()->with('error', trans('Invalid Zip Try Again'));
                    }
                } else {

                    return redirect()->back()->with('error', trans('Invalid Zip Try Again'));
                }
            } else {
                return redirect()->back()->with('error', trans('Something Went Wrong'));
            }
        }
    }

    public function appSettingsIndex(){
        $settingsArray = array(
            'ios_version','android_version','force_update','maintenance_mode',
            'light_tertiary','light_secondary','light_primary','dark_tertiary','dark_secondary','dark_primary',
            'show_admob_ads','android_banner_ad_id','ios_banner_ad_id','android_interstitial_ad_id','ios_interstitial_ad_id','android_native_ad_id','ios_native_ad_id',
            'app_home_screen','placeholder_logo'
        );
        $getAppSettings = HelperService::getMultipleSettingData($settingsArray);
        return view('settings.app-settings', compact('getAppSettings'));
    }

    public function app_settings(Request $request)
    {
        if (!has_permissions('update', 'app_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $validator = Validator::make($request->all(), [
                'app_home_screen' => 'nullable|image|mimes:png,jpg,jpeg|max:3000',
                'placeholder_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:3000',
            ],[
                'app_home_screen.mimes' => trans('Image must be JPG, JPEG or PNG'),
                'placeholder_logo.mimes' => trans('Image must be JPG, JPEG or PNG')
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $input = $request->except(['_token', 'btnAdd']);
            $destinationPath = public_path('assets/images/logo');

            if ($request->hasFile('app_home_screen') && $request->file('app_home_screen')->isValid()) {
                $file = $request->file('app_home_screen');

                // Get Data from Settings table
                $appHomeScreenDatabaseData = system_setting('app_home_screen');
                $databaseData = !empty($appHomeScreenDatabaseData) ? $appHomeScreenDatabaseData : null;

                $input['app_home_screen'] = handleFileUpload($request, 'app_home_screen', $destinationPath, "homeLogo", $databaseData);
            }
            if ($request->hasFile('placeholder_logo') && $request->file('placeholder_logo')->isValid()) {
                $file = $request->file('placeholder_logo');

                // Get Data from Settings table
                $placeHolderLogoDatabaseData = system_setting('placeholder_logo');
                $databaseData = !empty($placeHolderLogoDatabaseData) ? $placeHolderLogoDatabaseData : null;

                $input['placeholder_logo'] = handleFileUpload($request, 'placeholder_logo', $destinationPath, "placeholder", $databaseData);
            }

            foreach ($input as $key => $value) {

                Setting::updateOrCreate(['type' => $key], ['data' => $value]);
            }
        }

        return redirect()->back()->with('success', trans('Data Updated Successfully'));
    }


    public function webSettingsIndex(){
        $settingsArray = array('web_favicon','web_logo','web_placeholder_logo','web_footer_logo','iframe_link','facebook_id','instagram_id','twitter_id','youtube_id','category_background','sell_web_color','sell_web_background_color','rent_web_color','rent_web_background_color','buy_web_color','buy_web_background_color','web_maintenance_mode','allow_cookies');
        $getWebSettings = HelperService::getMultipleSettingData($settingsArray);
        return view('settings.web-settings', compact('getWebSettings'));
    }
    public function web_settings(Request $request)
    {
        if (!has_permissions('update', 'web_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $input = $request->except(['_token', 'btnAdd']);
            $destinationPath = public_path('assets/images/logo');


            if ($request->hasFile('web_logo')) {
                $file = $request->file('web_logo');

                // Get Data from Settings table
                $webLogoDatabaseData = system_setting('web_logo');
                $databaseData = !empty($webLogoDatabaseData) ? $webLogoDatabaseData : null;

                $input['web_logo'] = handleFileUpload($request, 'web_logo', $destinationPath, $file->getClientOriginalName(), $databaseData);
            }
            if ($request->hasFile('web_placeholder_logo') && $request->file('web_placeholder_logo')->isValid()) {
                $file = $request->file('web_placeholder_logo');

                // Get Data from Settings table
                $webPlaceholderLogoDatabaseData = system_setting('web_placeholder_logo');
                $databaseData = !empty($webPlaceholderLogoDatabaseData) ? $webPlaceholderLogoDatabaseData : null;

                $input['web_placeholder_logo'] = handleFileUpload($request, 'web_placeholder_logo', $destinationPath, $file->getClientOriginalName(), $databaseData);
            }
            if ($request->hasFile('web_favicon') && $request->file('web_favicon')->isValid()) {
                $file = $request->file('web_favicon');

                // Get Data from Settings table
                $webFavicon = system_setting('web_favicon');
                $databaseData = !empty($webFavicon) ? $webFavicon : null;

                $input['web_favicon'] = handleFileUpload($request, 'web_favicon', $destinationPath, $file->getClientOriginalName(), $databaseData);
            }
            if ($request->hasFile('web_footer_logo') && $request->file('web_footer_logo')->isValid()) {
                $file = $request->file('web_footer_logo');

                // Get Data from Settings table
                $webFooterLogo = system_setting('web_footer_logo');
                $databaseData = !empty($webFooterLogo) ? $webFooterLogo : null;

                $input['web_footer_logo'] = handleFileUpload($request, 'web_footer_logo', $destinationPath, $file->getClientOriginalName(), $databaseData);
            }

            foreach ($input as $key => $value) {

                Setting::updateOrCreate(['type' => $key], ['data' => $value]);
            }
        }

        return redirect()->back()->with('success', trans('Data Updated Successfully'));
    }

    public function notificationSettingIndex(){
        if (!has_permissions('read', 'notification_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $firebaseProjectId = Setting::where('type', 'firebase_project_id')->pluck('data')->first();
        $firebaseServiceJsonFile = Setting::where('type', 'firebase_service_json_file')->pluck('data')->first();
        return view('settings.notification-settings', compact('firebaseProjectId','firebaseServiceJsonFile'));
    }
    public function notificationSettingStore(Request $request){
        if (!has_permissions('update', 'notification_settings')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            // Declare the variables
            $directType = ['firebase_project_id'];
            $fileType = ['firebase_service_json_file'];

            // Loop to other than file data
            foreach ($directType as $type) {
                $data = $request->$type;
                Setting::updateOrCreate(['type' => $type], ['data' => $data]);
            }

            // Loop to file data
            foreach ($fileType as $type) {
                $destinationPath = public_path('assets');
                $file = $request->file($type);

                if($type == 'firebase_service_json_file'){
                    // When Type is firebase service file then pass custom name
                    if($request->hasFile($type)){
                        $name = handleFileUpload($request, $type, $destinationPath, 'firebase-service.json');
                        Setting::updateOrCreate(['type' => $type], ['data' => $name]);
                    }
                }else{
                    // When other file then pass the filename
                    if($request->hasFile($type)){
                        $name = handleFileUpload($request, $type, $destinationPath, $file->getClientOriginalName());
                        Setting::updateOrCreate(['type' => $type], ['data' => $name]);
                    }
                }
            }
        }
        return redirect()->back()->with('success', trans('Data Updated Successfully'));
    }


    // Email Configuration Index
    public function emailConfigurationsIndex(){
        if (!has_permissions('read', 'email_configurations')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('settings.email-configurations');
    }

    // Email Configuration Store
    public function emailConfigurationsStore(Request $request){
        if (!has_permissions('update', 'email_configurations')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $validator = Validator::make($request->all(), [
                'mail_mailer'       => 'required',
                'mail_host'         => 'required',
                'mail_port'         => 'required',
                'mail_username'     => 'required',
                'mail_password'     => 'required',
                'mail_encryption'   => 'required',
                'mail_send_from'    => 'required|email',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }

            try {
                // Get Request Data in Settings Array
                $settingsArray = $request->except('_token');

                // Create a settings data for database data insertions
                $settingsDataStore = array();
                foreach ($settingsArray as $key => $row) {
                    // If not empty then update or insert data according to type
                    Setting::updateOrInsert(
                        ['type' => $key], ['data' => $row]
                    );
                }

                // Add Email Configuration Verification Record to false
                Setting::updateOrInsert(
                    ['type' => 'email_configuration_verification'], ['data' => 0]
                );

                // Update ENV data variables
                $envUpdates = [
                    'MAIL_MAILER' => $request->mail_mailer,
                    'MAIL_HOST' => $request->mail_host,
                    'MAIL_PORT' => $request->mail_port,
                    'MAIL_USERNAME' => $request->mail_username,
                    'MAIL_PASSWORD' => $request->mail_password,
                    'MAIL_ENCRYPTION' => $request->mail_encryption,
                    'MAIL_FROM_ADDRESS' => $request->mail_send_from
                ];
                updateEnv($envUpdates);
                ResponseService::successResponse(trans("Data Updated Successfully"));

            } catch (Exception $e) {
                ResponseService::errorResponse(trans("Something Went Wrong"));
            }
        }
    }

    public function verifyEmailConfig(Request $request)
    {
        if (!has_permissions('update', 'email_configurations')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'verify_email' => 'required|email',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $data = [
                'email' => $request->verify_email,
            ];

            if (!filter_var($request->verify_email, FILTER_VALIDATE_EMAIL)) {
                $response = array(
                    'error' => true,
                    'message' => trans('Invalid Email'),
                );
                return response()->json($response);
            }

            // Get Data of email type
            $propertyStatusTemplate = "Your Email Configurations are working";

            $data = array(
                'email_template' => $propertyStatusTemplate,
                'email' => $request->verify_email,
                'title' => "Email Configuration Verification",
            );
            HelperService::sendMail($data,true);

            Setting::where('type','email_configuration_verification')->update(['data' => 1]);
            DB::commit();

            ResponseService::successResponse(trans("Email Sent Successfully"));
        } catch (Exception $e) {
            DB::rollback();
            if (Str::contains($e->getMessage(), [
                'Failed',
                'Mail',
                'Mailer',
                'MailManager',
                "Connection could not be established"
            ])) {
                ResponseService::validationError("There is issue with mail configuration, kindly contact admin regarding this");
            }
            ResponseService::errorResponse("Something went wrong");
        }
    }

    public function getCurrencySymbol(Request $request){
        try {
            $countryCode = $request->country_code;
            $symbol = Currencies::getSymbol($countryCode);
            ResponseService::successResponse("",$symbol);
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }


    // Email Templates Index
    public function emailTemplatesIndex(){
        if (!has_permissions('read', 'email_templates')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('mail-templates.templates-settings.index');
    }

    public function modifyMailTemplateIndex($type){
        if (!has_permissions('read', 'email_templates')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $types = array('verify_mail','reset_password','welcome_mail','property_status','project_status','property_ads_status','user_status','agent_verification_status');
        if (!in_array($type, $types)) {
            ResponseService::errorRedirectResponse("Type is invalid");
        }

        $data = HelperService::getEmailTemplatesTypes($type);

        $templateMailData = system_setting($data['type']);
        $templateMail = array('template' => $templateMailData);
        $data = array_merge($templateMail,$data);
        return view('mail-templates.templates-settings.update-template', compact('data'));
    }

    public function emailTemplatesList(){
        $data = HelperService::getEmailTemplatesTypes();
        $total = count($data);

        // $data->orderBy($sort, $order)->skip($offset)->take($limit);
        // $res = $data->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($data as $row) {
            $operate = BootstrapTableService::editButton(route('modify-mail-templates.index',$row['type']));

            $tempRow = $row;
            $tempRow['no'] = $no;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $no++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function emailTemplatesStore(Request $request){
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'data' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            Setting::updateOrCreate(
                array( 'type' => $request->type ),
                array( 'data' => $request->data ),
            );
            ResponseService::successResponse("Data Updated Successfully");
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,"Issue in email template storing with type :- $request->type");
        }
    }


    public function paystackPaymentSuccess(Request $request){
        // Get Web URL
        $webURL = HelperService::getSettingData('web_url') ?? null;
        $webWithStatusURL = $webURL.'/payment/success';

        if($webURL){
            echo "<html>
            <body>
            Redirecting...!
            </body>
            <script>
                window.location.replace('".$webWithStatusURL."');
            </script>
            </html>";
        }else{
            echo "<html>
            <body>
            Redirecting...!
            </body>
            <script>
                console.log('No web url added');
            </script>
            </html>";
        }
    }

    public function paystackPaymentCancel(Request $request){
        // Get Web URL and payment transaction ID
        $webURL = HelperService::getSettingData('web_url') ?? null;
        $webWithStatusURL = $webURL.'/payment/fail';
        $paymentTransactionId = $request->payment_transaction_id ?? null;

        // If transaction ID is available, update the payment status
        if ($paymentTransactionId) {
            PaymentTransaction::where('id', $paymentTransactionId)->update(['payment_status' => 'failed']);
        }

        if($webURL){
            echo "<html>
            <body>
            Redirecting...!
            </body>
            <script>
                window.location.replace('".$webWithStatusURL."');
            </script>
            </html>";
        }else{
            echo "<html>
            <body>
            Redirecting...!
            </body>
            <script>
                console.log('No web url added');
            </script>
            </html>";
        }
    }
}
