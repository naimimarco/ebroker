<?php
namespace App\Http\Controllers;



use DateTime;
use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Faq;

use App\Models\User;
use App\Models\Chats;
use Razorpay\Api\Api;
use App\Models\Slider;

use App\Models\Article;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Setting;
use Stripe\ApiResource;


use Stripe\ApiResponse;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Language;
use App\Models\Payments;
use App\Models\Projects;
use App\Models\Property;
use App\Libraries\Paypal;
use App\Models\CityImage;
use App\Models\Favourite;
use App\Models\NumberOtp;
use App\Models\parameter;

// use GuzzleHttp\Client;
use App\Models\OldPackage;
use App\Models\Usertokens;

use App\Models\SeoSettings;

use App\Models\UserPackage;
use Carbon\CarbonInterface;

use Illuminate\Support\Str;
use App\Models\ProjectPlans;
use App\Models\user_reports;
use App\Models\UserInterest;

use Illuminate\Http\Request;
use App\Models\Advertisement;
// use PayPal_Pro as GlobalPayPal_Pro;
use App\Models\Notifications;

use App\Models\InterestedUser;
use App\Models\PackageFeature;
use App\Models\PropertyImages;
use App\Models\report_reasons;
use App\Models\VerifyCustomer;
use App\Models\BankReceiptFile;
use App\Models\BlockedChatUser;
use App\Models\Contactrequests;
use App\Models\HomepageSection;
use App\Services\HelperService;
use App\Models\AssignParameters;
use App\Models\ProjectDocuments;
use App\Models\UserPackageLimit;
use App\Models\OutdoorFacilities;
use App\Services\ResponseService;
use App\Models\PaymentTransaction;
use App\Models\PropertiesDocument;
use App\Models\VerifyCustomerForm;
use Illuminate\Support\Facades\DB;
use App\Models\VerifyCustomerValue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Twilio\Exceptions\RestException;
use App\Models\OldUserPurchasedPackage;
use App\Models\VerifyCustomerFormValue;
use App\Services\Payment\PaymentService;
use App\Models\AssignedOutdoorFacilities;
use Illuminate\Support\Facades\Validator;
use App\Services\PDF\PaymentReceiptService;
use Twilio\Rest\Client as TwilioRestClient;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Support\Facades\Request as FacadesRequest;

class ApiController extends Controller
{

    //* START :: get_system_settings   *//
    public function get_system_settings(Request $request)
    {
        $result =  Setting::select('type', 'data')->get();

        foreach ($result as $row) {


            if ($row->type == "place_api_key" || $row->type == "stripe_secret_key") {

                $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                $encryptedData = '';
                if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {

                    $tempRow[$row->type] = base64_encode($encryptedData);
                }
            } else if ($row->type == 'company_logo') {

                $tempRow[$row->type] = url('/assets/images/logo/logo.png');
            } else if ($row->type == 'web_logo' || $row->type == 'web_placeholder_logo' || $row->type == 'app_home_screen' || $row->type == 'web_footer_logo' || $row->type == 'placeholder_logo' || $row->type == 'favicon_icon') {


                $tempRow[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
            } else {
                $tempRow[$row->type] = $row->data;
            }
        }

        if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
            $loggedInUserId = Auth::guard('sanctum')->user()->id;
            update_subscription($loggedInUserId);

            $customer_data = Customer::find($loggedInUserId);
            if ($customer_data->isActive == 0) {

                $tempRow['is_active'] = false;
            } else {
                $tempRow['is_active'] = true;
            }
            if ($row->type == "seo_settings") {

                $tempRow[$row->type] = $row->data == 1 ? true : false;
            }

            $customer = Customer::select('id', 'subscription', 'is_premium')
                ->where(function ($query) {
                    $query->where('subscription', 1)
                        ->orWhere('is_premium', 1);
                })
                ->find($loggedInUserId);



            if (($customer)) {
                $tempRow['is_premium'] = $customer->is_premium == 1 ? true : ($customer->subscription == 1 ? true : false);

                $tempRow['subscription'] = $customer->subscription == 1 ? true : false;
            } else {

                $tempRow['is_premium'] = false;
                $tempRow['subscription'] = false;
            }
        }
        $language = Language::select('code', 'name')->get();
        $user_data = User::find(1);
        $tempRow['admin_name'] = $user_data->name;
        $tempRow['admin_image'] = url('/assets/images/faces/2.jpg');
        $tempRow['demo_mode'] = env('DEMO_MODE');
        $tempRow['languages'] = $language;
        $tempRow['img_placeholder'] = url('/assets/images/placeholder.svg');


        $tempRow['min_price'] = DB::table('propertys')
            ->selectRaw('MIN(price) as min_price')
            ->value('min_price');


        $tempRow['max_price'] = DB::table('propertys')
            ->selectRaw('MAX(price) as max_price')
            ->value('max_price');

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $tempRow;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: Get System Setting   *//


    //* START :: user_signup   *//
    public function user_signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:0,1,2,3',
            'auth_id' => 'required_if:type,0,1,2',
            'email' => 'required_if:type,3',
            'password' => 'required_if:type,3'
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        $type = $request->type;
        if($type == 3){
            $email = $request->email;
            $user = Customer::where(['email' => $email, 'logintype' => 3])->first();

            if($user){
                if(!Hash::check($request->password, $user->password)){
                    ApiResponseService::validationError("Invalid Password");
                }else if($user->is_email_verified == false){
                    ApiResponseService::validationError("Email is not verified");
                }
            }else{
                ApiResponseService::validationError("Invalid Email");
            }

            $auth_id = $user->auth_id;
        }else{
            $auth_id = $request->auth_id;
            $user = Customer::where('auth_id', $auth_id)->where('logintype', $type)->first();
        }
        if (collect($user)->isEmpty()) {
            $saveCustomer = new Customer();
            $saveCustomer->name = isset($request->name) ? $request->name : '';
            $saveCustomer->email = isset($request->email) ? $request->email : '';
            $saveCustomer->mobile = isset($request->mobile) ? $request->mobile : null;
            $saveCustomer->slug_id = generateUniqueSlug($request->name, 5);
            $saveCustomer->logintype = isset($request->type) ? $request->type : '';
            $saveCustomer->address = isset($request->address) ? $request->address : '';
            $saveCustomer->auth_id = isset($request->auth_id) ? $request->auth_id : '';
            $saveCustomer->about_me = isset($request->about_me) ? $request->about_me : '';
            $saveCustomer->facebook_id = isset($request->facebook_id) ? $request->facebook_id : '';
            $saveCustomer->twiiter_id = isset($request->twiiter_id) ? $request->twiiter_id : '';
            $saveCustomer->instagram_id = isset($request->instagram_id) ? $request->instagram_id : '';
            $saveCustomer->youtube_id = isset($request->youtube_id) ? $request->youtube_id : '';
            $saveCustomer->latitude = isset($request->latitude) ? $request->latitude : '';
            $saveCustomer->longitude = isset($request->longitude) ? $request->longitude : '';
            $saveCustomer->notification = 1;
            $saveCustomer->about_me = isset($request->about_me) ? $request->about_me : '';
            $saveCustomer->facebook_id = isset($request->facebook_id) ? $request->facebook_id : '';
            $saveCustomer->twiiter_id = isset($request->twiiter_id) ? $request->twiiter_id : '';
            $saveCustomer->instagram_id = isset($request->instagram_id) ? $request->instagram_id : '';
            $saveCustomer->isActive = '1';


            $destinationPath = public_path('images') . config('global.USER_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload

            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $saveCustomer->profile = $imageName;
            } else {
                $saveCustomer->profile = $request->profile;
            }

            $saveCustomer->save();
            // Create a new personal access token for the user
            $token = $saveCustomer->createToken('token-name');


            $response['error'] = false;
            $response['message'] = 'User Register Successfully';

            $credentials = Customer::find($saveCustomer->id);
            $credentials = Customer::where('auth_id', $auth_id)->where('logintype', $type)->first();

            $response['token'] = $token->plainTextToken;
            $response['data'] = $credentials;

            if(!empty($credentials->email)){
                Log::info('under Mail');
                $data = array(
                    'appName' => env("APP_NAME"),
                    'email' => $credentials->email
                );
                try {
                    // Get Data of email type
                    $emailTypeData = HelperService::getEmailTemplatesTypes("welcome_mail");

                    // Email Template
                    $welcomeEmailTemplateData = system_setting($emailTypeData['type']);
                    $appName = env("APP_NAME") ?? "eBroker";
                    $variables = array(
                        'app_name' => $appName,
                        'user_name' => !empty($request->name) ? $request->name : "$appName User",
                        'email' => $request->email,
                    );
                    if(empty($welcomeEmailTemplateData)){
                        $welcomeEmailTemplateData = "Welcome to $appName";
                    }
                    $welcomeEmailTemplate = HelperService::replaceEmailVariables($welcomeEmailTemplateData,$variables);

                    $data = array(
                        'email_template' => $welcomeEmailTemplate,
                        'email' => $request->email,
                        'title' => $emailTypeData['title'],
                    );
                    HelperService::sendMail($data);
                } catch (Exception $e) {
                    Log::info("Welcome Mail Sending Issue with error :- ".$e->getMessage());
                }
            }
        } else {
            $credentials = Customer::where('auth_id', $auth_id)->where('logintype', $type)->first();
            if ($credentials->isActive == 0) {
                $response['error'] = true;
                $response['message'] = 'Account Deactivated by Administrative please connect to them';
                $response['is_active'] = false;
                return response()->json($response);
            }
            $credentials->update();
            $token = $credentials->createToken('token-name');

            // Update or add FCM ID in UserToken for Current User
            if ($request->has('fcm_id') && !empty($request->fcm_id)) {
                Usertokens::updateOrCreate(
                    ['fcm_id' => $request->fcm_id],
                    ['customer_id' => $credentials->id]
                );
            }
            $response['error'] = false;
            $response['message'] = 'Login Successfully';
            $response['token'] = $token->plainTextToken;
            $response['data'] = $credentials;
        }
        return response()->json($response);
    }



    //* START :: get_slider   *//
    public function getSlider(Request $request)
    {
        $sliderData = Slider::select('id','type', 'image', 'web_image', 'category_id', 'propertys_id','show_property_details','link')->with(['category' => function($query){
            $query->select('id', 'category')->where('status',1);
        }],'property:id,title,title_image,price,propery_type as property_type')->orderBy('id', 'desc')->get()->map(function($slider){
            if(collect($slider->property)->isNotEmpty()){
                $slider->property->parameters = $slider->property->parameters;
            }
            return $slider;
        });

        if(collect($sliderData)->isNotEmpty()){
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $sliderData;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    //* END :: get_slider   *//


    //* START :: get_categories   *//
    public function get_categories(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $latitude = $request->has('latitude') ? $request->latitude : null;
        $longitude = $request->has('longitude') ? $request->longitude : null;

        $categories = Category::select('id', 'category', 'image', 'parameter_types', 'meta_title', 'meta_description', 'meta_keywords', 'slug_id')->where('status', '1');

        if($request->has('has_property') && $request->has_property == true){
            $categories = $categories->clone()->whereHas('properties',function($query) use($latitude, $longitude){
                $query->where(['status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                    $query->where('latitude', $latitude)->where('longitude', $longitude);
                });
            })->withCount(['properties' => function ($query) use($latitude, $longitude) {
                    $query->where(['status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                        $query->where('latitude', $latitude)->where('longitude', $longitude);
                    });
                }
            ]);
        }

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $categories->where('category', 'LIKE', "%$search%");
        }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $categories->where('id', $id);
        }
        if (isset($request->slug_id) && !empty($request->slug_id)) {
            $id = $request->slug_id;
            $categories->where('slug_id', $request->slug_id);
        }

        $total = $categories->get()->count();
        $result = $categories->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();

        $result->map(function ($result) {
            $result['meta_image'] = $result->image;
        });


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            foreach ($result as $row) {
                $parameterData = $row->parameters;
                if (collect($parameterData)->isNotEmpty()) {
                    $parameterData = $parameterData->map(function ($item) {
                        unset($item->assigned_parameter);
                        return $item;
                    });
                }
                $row->parameter_types = $parameterData;
            }

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: get_slider   *//


    //* START :: about_meofile   *//
    public function update_profile(Request $request)
    {
        try {
            DB::beginTransaction();
            $currentUser = Auth::user();
            $customer =  Customer::find($currentUser->id);

            if (!empty($customer)) {

                // update the Data passed in payload
                $fieldsToUpdate = $request->only([
                    'name', 'email', 'mobile', 'fcm_id', 'address', 'notification', 'about_me',
                    'facebook_id', 'twiiter_id', 'instagram_id', 'youtube_id', 'latitude', 'longitude',
                    'city', 'state', 'country'
                ]);

                $customer->update($fieldsToUpdate);

                if ($request->has('fcm_id') && !empty($request->fcm_id)) {
                    Usertokens::updateOrCreate(
                        ['fcm_id' => $request->fcm_id],
                        ['customer_id' => $customer->id,]
                    );
                }

                // Update Profile
                if ($request->hasFile('profile')) {
                    $destinationPath = public_path('images') . config('global.USER_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    $old_image = $customer->profile;
                    $profile = $request->file('profile');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();

                    if ($profile->move($destinationPath, $imageName)) {
                        $customer->profile = $imageName;
                        if ($old_image != '') {
                            if (file_exists(public_path('images') . config('global.USER_IMG_PATH') . $old_image)) {
                                unlink(public_path('images') . config('global.USER_IMG_PATH') . $old_image);
                            }
                        }
                        $customer->update();
                    }
                }

                DB::commit();
                return response()->json(['error' => false, 'data' => $customer]);
            } else {
                return response()->json(['error' => false, 'message' => "No data found!", 'data' => []]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => true, 'message' => 'Something Went Wrong'], 500);
        }
    }

    //* END :: update_profile   *//


    //* START :: get_user_by_id   *//
    public function getUserData()
    {
        try {
            // Get LoggedIn User Data from Toke
            $userData = Auth::user();
            // Check the User Data is not Empty
            if (collect($userData)->isNotEmpty()) {
                $response['error'] = false;
                $response['data'] = $userData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    //* END :: get_user_by_id   *//


    //* START :: get_property   *//
    public function get_property(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
            $current_user = Auth::guard('sanctum')->user()->id;
        } else {
            $current_user = null;
        }
        $property = Property::with('customer', 'user', 'category:id,category,image,slug_id', 'assignfacilities.outdoorfacilities', 'parameters', 'favourite', 'interested_users')->where(['status' => 1, 'request_status' => 'approved']);

        $max_price = isset($request->max_price) ? $request->max_price : Property::max('price');
        $min_price = isset($request->min_price) ? $request->min_price : 0;
        $totalClicks = 0;

        // If parameter ID passed
        if ($request->has('parameter_id') && !empty($request->parameter_id)) {
            $parameterId = $request->parameter_id;
            $property = $property->whereHas('parameters', function ($q) use ($parameterId) {
                $q->where('parameter_id', $parameterId);
            });
        }

        // If Max Price And Min Price passed
        if (isset($request->max_price) && isset($request->min_price) && (!empty($request->max_price) && !empty($min_price))) {
            $property = $property->whereBetween('price', [$min_price, $max_price]);
        }

        $property_type = $request->property_type;  //0 : Sell 1:Rent
        // If Property Type Passed
        if (isset($property_type) && (!empty($property_type) || $property_type == 0)) {
            $property = $property->where('propery_type', $property_type);
        }

        // If Posted Since 0 or 1 is passed
        if ($request->has('posted_since') && !empty($request->posted_since)) {
            $posted_since = $request->posted_since;
            // 0 - Last Week
            if ($posted_since == 0) {
                $startDateOfWeek = Carbon::now()->subWeek()->startOfWeek();
                $endDateOfWeek = Carbon::now()->subWeek()->endOfWeek();
                $property = $property->whereBetween('created_at', [$startDateOfWeek, $endDateOfWeek]);
            }
            // 1 - Yesterday
            if ($posted_since == 1) {
                $yesterdayDate = Carbon::yesterday();
                $property =  $property->whereDate('created_at', $yesterdayDate);
            }
        }

        // If Category Id is Passed
        if ($request->has('category_id') && !empty($request->category_id)) {
            $property = $property->where('category_id', $request->category_id);
        }

        // If Id is passed
        if ($request->has('id') && !empty($request->id)) {
            $property = $property->where('id', $request->id);
            HelperService::incrementTotalClick('property',$request->id);
        }

        if ($request->has('category_slug_id') && !empty($request->category_slug_id)) {
            // Get the category date on category slug id
            $category = Category::where('slug_id', $request->category_slug_id)->first();
            // if category data exists then get property on the category id
            if (collect($category)->isNotEmpty()) {
                $property = $property->where('category_id', $category->id);
            }
        }

        // If Property Slug is passed
        if ($request->has('slug_id') && !empty($request->slug_id)) {
            $property = $property->where('slug_id', $request->slug_id);
            HelperService::incrementTotalClick('property',null,$request->slug_id);
        }

        // If Country is passed
        if ($request->has('country') && !empty($request->country)) {
            $property = $property->where('country', $request->country);
        }

        // If State is passed
        if ($request->has('state') && !empty($request->state)) {
            $property = $property->where('state', $request->state);
        }

        // If City is passed
        if ($request->has('city') && !empty($request->city)) {
            $property = $property->where('city', $request->city);
        }

        // If promoted is passed then get the properties according to advertisement's data except the advertisement's slider data
        if ($request->has('promoted') && !empty($request->promoted)) {
            $propertiesId = Advertisement::whereNot('type', 'Slider')->where('is_enable', 1)->pluck('property_id');
            $property = $property->whereIn('id', $propertiesId)->inRandomOrder();
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }

        // IF User Promoted Param Passed then show the User's Advertised data
        if ($request->has('users_promoted') && !empty($request->users_promoted)) {
            $propertiesId = Advertisement::where('customer_id', $current_user)->pluck('property_id');
            $property = $property->whereIn('id', $propertiesId);
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $property = $property->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%")
                    ->orWhere('address', 'LIKE', "%$search%")
                    ->orWhereHas('category', function ($query1) use ($search) {
                        $query1->where('category', 'LIKE', "%$search%");
                    });
            });
        }

        // If Top Rated passed then show the property data with Order by on Total Click Descending
        if ($request->has('top_rated') && $request->top_rated == 1) {
            $property = $property->orderBy('total_click', 'DESC');
        }

        // IF Most Liked Passed then show the data according to
        if ($request->has('most_liked') && !empty($request->most_liked)) {
            $property = $property->withCount('favourite')->orderBy('favourite_count', 'DESC');
        }

        $total = $property->count();
        $result = $property->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

        if (!$result->isEmpty()) {
            $property_details  = get_property_details($result, $current_user);

            // Check that Property Details exists or not
            if (isset($property_details) && collect($property_details)->isNotEmpty()) {
                /**
                 * Check that id or slug id passed and get the similar properties data according to param passed
                 * If both passed then priority given to id param
                 * */
                if ((isset($id) && !empty($id))) {
                    $getSimilarPropertiesQueryData = Property::where('id', '!=', $id)->select('id', 'slug_id', 'category_id', 'title', 'added_by', 'address', 'city', 'country', 'state', 'propery_type', 'price', 'created_at', 'title_image','request_status')->where(function($query) use($property_details){
                        return $query->where(['status' => 1, 'request_status' => 'approved', 'category_id' => $property_details[0]['category_id']]);
                    })->inRandomOrder()->limit(10)->get();
                    $getSimilarProperties = get_property_details($getSimilarPropertiesQueryData, $current_user);
                } else if ((isset($request->slug_id) && !empty($request->slug_id))) {
                    $getSimilarPropertiesQueryData = Property::where('slug_id', '!=', $request->slug_id)->select('id', 'slug_id', 'category_id', 'title', 'added_by', 'address', 'city', 'country', 'state', 'propery_type', 'price', 'created_at', 'title_image','request_status')->where(function($query) use($property_details){
                        return $query->where(['status' => 1, 'request_status' => 'approved', 'category_id' => $property_details[0]['category']['id']]);
                    })->inRandomOrder()->limit(10)->get();
                    $getSimilarProperties = get_property_details($getSimilarPropertiesQueryData, $current_user);
                }
            }


            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['similar_properties'] = $getSimilarProperties ?? array();
            $response['total'] = $total;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return ($response);
    }
    //* END :: get_property   *//



    //* START :: post_property   *//
    public function post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required',
            'description'       => 'required',
            'category_id'       => 'required',
            'property_type'     => 'required',
            'address'           => 'required',
            'title_image'       => 'required|file|max:3000|mimes:jpeg,png,jpg',
            'three_d_image'     => 'nullable|mimes:jpg,jpeg,png,gif|max:3000',
            'documents.*'       => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
            'price'             => ['required', 'numeric', 'min:1', 'max:9223372036854775807', function ($attribute, $value, $fail) {
                if ($value >= 9223372036854775807) {
                    $fail("The Price must not exceed more than 9223372036854775807.");
                }
            }],
            'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                // Regular expression to validate YouTube URLs
                $youtubePattern = '/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/';

                if (!preg_match($youtubePattern, $value)) {
                    return $fail("The Video Link must be a valid YouTube URL.");
                }

                // Transform youtu.be short URL to full YouTube URL for validation
                if (strpos($value, 'youtu.be') !== false) {
                    $value = 'https://www.youtube.com/watch?v=' . substr(parse_url($value, PHP_URL_PATH), 1);
                }

                // Get the headers of the URL
                $headers = @get_headers($value);

                // Check if the URL is accessible
                if (!$headers || strpos($headers[0], '200') === false) {
                    return $fail("The Video Link must be accessible.");
                }
            }]

        ], [], [
            'documents.*' => 'document :position'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            DB::beginTransaction();
            HelperService::updatePackageLimit('property_list');
            $loggedInUserId = Auth::user()->id;

            $slugData = (isset($request->slug_id) && !empty($request->slug_id)) ? $request->slug_id : $request->title;
            $saveProperty = new Property();
            $saveProperty->category_id = $request->category_id;
            $saveProperty->slug_id = generateUniqueSlug($slugData, 1);
            $saveProperty->title = $request->title;
            $saveProperty->description = $request->description;
            $saveProperty->address = $request->address;
            $saveProperty->client_address = (isset($request->client_address)) ? $request->client_address : '';
            $saveProperty->propery_type = $request->property_type;
            $saveProperty->price = $request->price;
            $saveProperty->country = (isset($request->country)) ? $request->country : '';
            $saveProperty->state = (isset($request->state)) ? $request->state : '';
            $saveProperty->city = (isset($request->city)) ? $request->city : '';
            $saveProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
            $saveProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
            $saveProperty->rentduration = (isset($request->rentduration)) ? $request->rentduration : '';
            $saveProperty->meta_title = (isset($request->meta_title)) ? $request->meta_title : '';
            $saveProperty->meta_description = (isset($request->meta_description)) ? $request->meta_description : '';
            $saveProperty->meta_keywords = (isset($request->meta_keywords)) ? $request->meta_keywords : '';
            $saveProperty->added_by = $loggedInUserId;
            $saveProperty->video_link = (isset($request->video_link)) ? $request->video_link : "";
            $saveProperty->package_id = $request->package_id;
            $saveProperty->post_type = 1;

            $autoApproveStatus = $this->getAutoApproveStatus($loggedInUserId);
            if($autoApproveStatus){
                $saveProperty->request_status = 'approved';
            }else{
                $saveProperty->request_status = 'pending';
            }
            $saveProperty->status = 1;

            //Title Image
            if ($request->hasFile('title_image')) {
                $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file = $request->file('title_image');
                $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                $titleImageName = handleFileUpload($request, 'title_image', $destinationPath, $imageName);
                $saveProperty->title_image = $titleImageName;
            } else {
                $saveProperty->title_image  = '';
            }

            // Meta Image
            if ($request->hasFile('meta_image')) {
                $destinationPath = public_path('images') . config('global.PROPERTY_SEO_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file = $request->file('meta_image');
                $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                $metaImageName = handleFileUpload($request, 'meta_image', $destinationPath, $imageName);
                $saveProperty->meta_image = $metaImageName;
            }

            // three_d_image
            if ($request->hasFile('three_d_image')) {
                $destinationPath = public_path('images') . config('global.3D_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file = $request->file('three_d_image');
                $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                $three_dImage = handleFileUpload($request, 'three_d_image', $destinationPath, $imageName);
                $saveProperty->three_d_image = $three_dImage;
            } else {
                $saveProperty->three_d_image  = '';
            }


            $saveProperty->is_premium = isset($request->is_premium) ? ($request->is_premium == "true" ? 1 : 0) : 0;
            $saveProperty->save();

            $destinationPathForParam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->facilities) {
                foreach ($request->facilities as $key => $value) {
                    $facilities = new AssignedOutdoorFacilities();
                    $facilities->facility_id = $value['facility_id'];
                    $facilities->property_id = $saveProperty->id;
                    $facilities->distance = $value['distance'];
                    $facilities->save();
                }
            }
            if ($request->parameters) {
                foreach ($request->parameters as $key => $parameter) {
                    if(isset($parameter['value']) && !empty($parameter['value'])){
                        $AssignParameters = new AssignParameters();
                        $AssignParameters->modal()->associate($saveProperty);
                        $AssignParameters->parameter_id = $parameter['parameter_id'];
                        if ($request->hasFile('parameters.' . $key . '.value')) {
                            $profile = $request->file('parameters.' . $key . '.value');
                            $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                            $profile->move($destinationPathForParam, $imageName);
                            $AssignParameters->value = $imageName;
                        } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                            $ch = curl_init($parameter['value']);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $fileContents = curl_exec($ch);
                            curl_close($ch);
                            $filename = microtime(true) . basename($parameter['value']);
                            file_put_contents($destinationPathForParam . '/' . $filename, $fileContents);
                            $AssignParameters->value = $filename;
                        } else {
                            $AssignParameters->value = $parameter['value'];
                        }
                        $AssignParameters->save();
                    }
                }
            }

            /// START :: UPLOAD GALLERY IMAGE
            $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
            if (!is_dir($FolderPath)) {
                mkdir($FolderPath, 0777, true);
            }
            $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $saveProperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $name = microtime(true). '.' . $file->extension();
                    $file->move($destinationPath, $name);
                    $gallary_image = new PropertyImages();
                    $gallary_image->image = $name;
                    $gallary_image->propertys_id = $saveProperty->id;
                    $gallary_image->save();
                }
            }
            /// END :: UPLOAD GALLERY IMAGE


            /// START :: UPLOAD DOCUMENTS
            $FolderPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH');
            if (!is_dir($FolderPath)) {
                mkdir($FolderPath, 0777, true);
            }
            $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $saveProperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('documents')) {
                $documentsData = array();
                foreach ($request->file('documents') as $file) {
                    $name = microtime(true). '.' . $file->extension();
                    $type = $file->extension();
                    $file->move($destinationPath, $name);
                    $documentsData[] = array(
                        'property_id' => $saveProperty->id,
                        'name' => $name,
                        'type' => $type
                    );
                }

                if(collect($documentsData)->isNotEmpty()){
                    PropertiesDocument::insert($documentsData);
                }
            }
            /// END :: UPLOAD DOCUMENTS

            // START :: ADD CITY DATA
            if(isset($request->city) && !empty($request->city)){
                CityImage::updateOrCreate(array('city' => $request->city));
            }
            // END :: ADD CITY DATA

            $result = Property::with('customer')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where('id', $saveProperty->id)->get();
            $property_details = get_property_details($result);

            DB::commit();
            $response['error'] = false;
            $response['message'] = 'Property Post Successfully';
            $response['data'] = $property_details;
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
        return response()->json($response);
    }

    //* END :: post_property   *//
    //* START :: update_post_property   *//
    /// This api use for update and delete  property
    public function update_post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action_type'           => 'required',
            'three_d_image'         => 'nullable|mimes:jpg,jpeg,png,gif|max:3000',
            'remove_three_d_image'  => 'nullable|in:0,1',
            'documents.*'           => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
            'price'                 => ['required', 'numeric', 'min:1', 'max:9223372036854775807', function ($attribute, $value, $fail) {
                if ($value >= 9223372036854775807) {
                    $fail("The Price must not exceed more than 9223372036854775807.");
                }
            }],
            'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                // Regular expression to validate YouTube URLs
                $youtubePattern = '/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/';

                if (!preg_match($youtubePattern, $value)) {
                    return $fail("The Video Link must be a valid YouTube URL.");
                }

                // Transform youtu.be short URL to full YouTube URL for validation
                if (strpos($value, 'youtu.be') !== false) {
                    $value = 'https://www.youtube.com/watch?v=' . substr(parse_url($value, PHP_URL_PATH), 1);
                }

                // Get the headers of the URL
                $headers = @get_headers($value);

                // Check if the URL is accessible
                if (!$headers || strpos($headers[0], '200') === false) {
                    return $fail("The Video Link must be accessible.");
                }
            }]


        ], [], [
            'documents.*' => 'document :position'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            DB::beginTransaction();
            $current_user = Auth::user()->id;
            $id = $request->id;
            $action_type = $request->action_type;
            if ($request->slug_id) {
                $property = Property::where('added_by', $current_user)->where('slug_id', $request->slug_id)->first();
                if (!$property) {
                    $property = Property::where('added_by', $current_user)->find($id);
                }
            } else {
                $property = Property::where('added_by', $current_user)->find($id);
            }
            if (($property)) {
                // 0: Update 1: Delete
                if ($action_type == 0) {

                    $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    if (isset($request->category_id)) {
                        $property->category_id = $request->category_id;
                    }

                    if (isset($request->title)) {
                        $property->title = $request->title;
                        $slugData = (isset($request->slug_id) && !empty($request->slug_id)) ? $request->slug_id : $request->title;
                        $property->slug_id = generateUniqueSlug($slugData, 1,null,$id);
                    }

                    if(isset($request->slug_id) && !empty($request->slug_id)){
                        $property->slug_id = generateUniqueSlug($request->slug_id, 1,null,$id);
                    }

                    if (isset($request->description)) {
                        $property->description = $request->description;
                    }

                    if (isset($request->address)) {
                        $property->address = $request->address;
                    }

                    if (isset($request->client_address)) {
                        $property->client_address = $request->client_address;
                    }

                    if (isset($request->property_type)) {
                        $property->propery_type = $request->property_type;
                    }

                    if (isset($request->price)) {
                        $property->price = $request->price;
                    }
                    if (isset($request->country)) {
                        $property->country = $request->country;
                    }
                    if (isset($request->state)) {
                        $property->state = $request->state;
                    }
                    if (isset($request->city)) {
                        $property->city = $request->city;
                    }
                    if (isset($request->status)) {
                        $property->status = $request->status;
                    }
                    if (isset($request->latitude)) {
                        $property->latitude = $request->latitude;
                    }
                    if (isset($request->longitude)) {
                        $property->longitude = $request->longitude;
                    }
                    if (isset($request->rentduration)) {
                        $property->rentduration = $request->rentduration;
                    }

                    if (isset($request->video_link)) {
                        $property->video_link = $request->video_link;
                    }

                    $property->meta_title = $request->meta_title ?? null;
                    $property->meta_description = $request->meta_description ?? null;
                    $property->meta_keywords = $request->meta_keywords ?? null;
                    $property->is_premium = !empty($request->is_premium) && $request->is_premium == "true" ? 1 : 0;

                    if(HelperService::getSettingData('auto_approve_edited_listings') == 0){
                        $property->request_status = 'pending';
                    }

                    if ($request->hasFile('title_image')) {
                        $profile = $request->file('title_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);
                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') .  $property->title_image)) {
                                unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                            }
                        }
                        $property->title_image = $imageName;
                    }

                    // if ($request->hasFile('meta_image')) {
                    //     if (!empty($property->meta_image)) {
                    //         $url = $property->meta_image;
                    //         $relativePath = parse_url($url, PHP_URL_PATH);
                    //         if (file_exists(public_path()  . $relativePath)) {
                    //             unlink(public_path()  . $relativePath);
                    //         }
                    //     }
                    //     $destinationPath = public_path('images') . config('global.PROPERTY_SEO_IMG_PATH');
                    //     if (!is_dir($destinationPath)) {
                    //         mkdir($destinationPath, 0777, true);
                    //     }
                    //     $profile = $request->file('meta_image');
                    //     $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                    //     $profile->move($destinationPath, $imageName);
                    //     $property->meta_image = $imageName;
                    // } else {
                    //     if (!empty($property->meta_image)) {
                    //         $url = $property->meta_image;
                    //         $relativePath = parse_url($url, PHP_URL_PATH);
                    //         if (file_exists(public_path()  . $relativePath)) {
                    //             unlink(public_path()  . $relativePath);
                    //         }
                    //     }
                    //     $property->meta_image = null;
                    // }



                    if($request->has('meta_image')){
                        if($request->meta_image != $property->meta_image){
                            if (!empty($request->meta_image && $request->hasFile('meta_image'))) {
                                if (!empty($property->meta_image)) {
                                    $url = $property->meta_image;
                                    $relativePath = parse_url($url, PHP_URL_PATH);
                                    if (file_exists(public_path()  . $relativePath)) {
                                        unlink(public_path()  . $relativePath);
                                    }
                                }
                                $destinationPath = public_path('images') . config('global.PROPERTY_SEO_IMG_PATH');
                                if (!is_dir($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }
                                $profile = $request->file('meta_image');
                                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                $profile->move($destinationPath, $imageName);
                                $property->meta_image = $imageName;
                            } else {
                                if (!empty($property->meta_image)) {
                                    $url = $property->meta_image;
                                    $relativePath = parse_url($url, PHP_URL_PATH);
                                    if (file_exists(public_path()  . $relativePath)) {
                                        unlink(public_path()  . $relativePath);
                                    }
                                }
                                $property->meta_image = null;
                            }
                        }
                    }

                    if($request->has('remove_three_d_image') && $request->remove_three_d_image == 1){
                        $threeDImage = $property->getRawOriginal('three_d_image');
                        if (!empty($threeDImage)) {
                            if (file_exists(public_path('images') . config('global.3D_IMG_PATH') .  $threeDImage)) {
                                unlink(public_path('images') . config('global.3D_IMG_PATH') . $threeDImage);
                            }
                        }
                        $property->three_d_image = null;
                    }

                    if ($request->hasFile('three_d_image')) {
                        $destinationPath1 = public_path('images') . config('global.3D_IMG_PATH');
                        if (!is_dir($destinationPath1)) {
                            mkdir($destinationPath1, 0777, true);
                        }
                        $profile = $request->file('three_d_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath1, $imageName);
                        if ($property->three_d_image != '') {
                            if (file_exists(public_path('images') . config('global.3D_IMG_PATH') .  $property->three_d_image)) {
                                unlink(public_path('images') . config('global.3D_IMG_PATH') . $property->three_d_image);
                            }
                        }
                        $property->three_d_image = $imageName;
                    }

                    if ($request->parameters) {
                        $destinationPathforparam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        foreach ($request->parameters as $key => $parameter) {
                            $AssignParameters = AssignParameters::where('modal_id', $property->id)->where('parameter_id', $parameter['parameter_id'])->pluck('id');
                            if (count($AssignParameters)) {
                                $update_data = AssignParameters::find($AssignParameters[0]);
                                if ($request->hasFile('parameters.' . $key . '.value')) {
                                    $profile = $request->file('parameters.' . $key . '.value');
                                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                    $profile->move($destinationPathforparam, $imageName);
                                    $update_data->value = $imageName;
                                } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                                    $ch = curl_init($parameter['value']);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $fileContents = curl_exec($ch);
                                    curl_close($ch);
                                    $filename = microtime(true) . basename($parameter['value']);
                                    file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                    $update_data->value = $filename;
                                } else {
                                    $update_data->value = $parameter['value'];
                                }
                                $update_data->save();
                            } else {
                                $AssignParameters = new AssignParameters();
                                $AssignParameters->modal()->associate($property);
                                $AssignParameters->parameter_id = $parameter['parameter_id'];
                                if ($request->hasFile('parameters.' . $key . '.value')) {
                                    $profile = $request->file('parameters.' . $key . '.value');
                                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                    $profile->move($destinationPathforparam, $imageName);
                                    $AssignParameters->value = $imageName;
                                } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                                    $ch = curl_init($parameter['value']);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $fileContents = curl_exec($ch);
                                    curl_close($ch);
                                    $filename = microtime(true) . basename($parameter['value']);
                                    file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                    $AssignParameters->value = $filename;
                                } else {
                                    $AssignParameters->value = $parameter['value'];
                                }
                                $AssignParameters->save();
                            }
                        }
                    }

                    if ($request->id) {
                        $prop_id = $request->id;
                        AssignedOutdoorFacilities::where('property_id', $request->id)->delete();
                    } else {
                        $prop = Property::where('slug_id', $request->slug_id)->first();
                        $prop_id = $prop->id;
                        AssignedOutdoorFacilities::where('property_id', $prop->id)->delete();

                    }
                    // AssignedOutdoorFacilities::where('property_id', $request->id)->delete();
                    if ($request->facilities) {
                        foreach ($request->facilities as $key => $value) {
                            $facilities = new AssignedOutdoorFacilities();
                            $facilities->facility_id = $value['facility_id'];
                            $facilities->property_id = $prop_id;
                            $facilities->distance = $value['distance'];
                            $facilities->save();
                        }
                    }

                    $property->update();
                    $update_property = Property::with('customer')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where('id', $request->id)->get();

                    /// START :: UPLOAD GALLERY IMAGE
                    $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                    if (!is_dir($FolderPath)) {
                        mkdir($FolderPath, 0777, true);
                    }
                    $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $property->id;
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    if ($request->remove_gallery_images) {
                        foreach ($request->remove_gallery_images as $key => $value) {
                            $gallary_images = PropertyImages::find($value);
                            if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $gallary_images->propertys_id . '/' . $gallary_images->image)) {
                                unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $gallary_images->propertys_id . '/' . $gallary_images->image);
                            }
                            $gallary_images->delete();
                        }
                    }
                    if ($request->hasfile('gallery_images')) {
                        foreach ($request->file('gallery_images') as $file) {
                            $name = microtime(true). '.' . $file->extension();
                            $file->move($destinationPath, $name);
                            PropertyImages::create([
                                'image' => $name,
                                'propertys_id' => $property->id,
                            ]);
                        }
                    }
                    /// END :: UPLOAD GALLERY IMAGE



                    /// START :: UPLOAD DOCUMENTS
                    if ($request->remove_documents) {
                        foreach ($request->remove_documents as $key => $value) {
                            $document = PropertiesDocument::find($value);
                            if (file_exists(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $document->propertys_id . '/' . $document->name)) {
                                unlink(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $document->propertys_id . '/' . $document->name);
                            }
                            $document->delete();
                        }
                    }

                    $FolderPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH');
                    if (!is_dir($FolderPath)) {
                        mkdir($FolderPath, 0777, true);
                    }
                    $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $property->id;
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    if ($request->hasfile('documents')) {
                        $documentsData = array();
                        foreach ($request->file('documents') as $file) {
                            // $name = time() . rand(1, 100) . '.' . $file->extension();
                            // $type = $file->extension();
                            // $file->move($destinationPath, $name);

                            $type = $file->extension();
                            $name = microtime(true). '.' . $type;
                            $file->move($destinationPath, $name);

                            $documentsData[] = array(
                                'property_id' => $property->id,
                                'name' => $name,
                                'type' => $type
                            );
                        }

                        if(collect($documentsData)->isNotEmpty()){
                            PropertiesDocument::insert($documentsData);
                        }
                    }
                    /// END :: UPLOAD DOCUMENTS

                    // START :: ADD CITY DATA
                    if(isset($request->city) && !empty($request->city)){
                        CityImage::updateOrCreate(array('city' => $request->city));
                    }
                    // END :: ADD CITY DATA


                    $current_user = Auth::user()->id;
                    $property_details = get_property_details($update_property, $current_user, true);
                    $response['error'] = false;
                    $response['message'] = 'Property Update Successfully';
                    $response['data'] = $property_details;
                } elseif ($action_type == 1) {
                    if ($property->delete()) {
                        $response['error'] = false;
                        $response['message'] =  'Delete Successfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'something wrong';
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'No Data Found';
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }

        return response()->json($response);
    }
    //* END :: update_post_property   *//


    //* START :: remove_post_images   *//
    public function remove_post_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if (!$validator->fails()) {
            $id = $request->id;
            $getImage = PropertyImages::where('id', $id)->first();
            $image = $getImage->image;
            $propertys_id =  $getImage->propertys_id;

            if (PropertyImages::where('id', $id)->delete()) {
                if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image)) {
                    unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image);
                }
                $response['error'] = false;
            } else {
                $response['error'] = true;
            }

            $countImage = PropertyImages::where('propertys_id', $propertys_id)->get();
            if ($countImage->count() == 0) {
                rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id);
            }

            $response['error'] = false;
            $response['message'] = 'Property Post Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: remove_post_images   *//

    //* START :: set_property_inquiry   *//




    //* START :: get_notification_list   *//
    public function get_notification_list(Request $request)
    {
        $loggedInUserId = Auth::user()->id;
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $notificationQuery = Notifications::where("customers_id", $loggedInUserId)
            ->orWhere('send_type', '1')
            ->with('property:id,title_image')
            ->select('id', 'title', 'message', 'image', 'type', 'send_type', 'customers_id', 'propertys_id', 'created_at')
            ->orderBy('id', 'DESC');

        $result = $notificationQuery->clone()
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = $notificationQuery->count();

        if (!$result->isEmpty()) {
            $result = $result->map(function ($notification) {
                $notification->created = $notification->created_at->diffForHumans();
                $notification->notification_image = !empty($notification->image) ? $notification->image : (!empty($notification->propertys_id) && !empty($notification->property) ? $notification->property->title_image : "");
                unset($notification->image);
                return $notification;
            });

            $response = [
                'error' => false,
                'total' => $total,
                'data' => $result->toArray(),
            ];
        } else {
            $response = [
                'error' => false,
                'message' => 'No data found!',
                'data' => [],
            ];
        }

        return response()->json($response);
    }
    //* END :: get_notification_list   *//


    //* START :: delete_user   *//
    public function delete_user(Request $request)
    {
        try {
            DB::beginTransaction();
            $loggedInUserId = Auth::user()->id;
            $customer = Customer::find($loggedInUserId);
            if(collect($customer)->isNotEmpty()){
                $customer->delete();
            }
            DB::commit();
            $response['error'] = false;
            $response['message'] = 'Delete Successfully';
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollBack();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    //* END :: delete_user   *//
    public function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }
    //*START :: add favoutite *//
    public function add_favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'property_id' => 'required',


        ]);

        if (!$validator->fails()) {
            //add favourite
            $current_user = Auth::user()->id;
            if ($request->type == 1) {


                $fav_prop = Favourite::where('user_id', $current_user)->where('property_id', $request->property_id)->get();

                if (count($fav_prop) > 0) {
                    $response['error'] = false;
                    $response['message'] = "Property already add to favourite";
                    return response()->json($response);
                }
                $favourite = new Favourite();
                $favourite->user_id = $current_user;
                $favourite->property_id = $request->property_id;
                $favourite->save();
                $response['error'] = false;
                $response['message'] = "Property add to Favourite add successfully";
            }
            //delete favourite
            if ($request->type == 0) {
                Favourite::where('property_id', $request->property_id)->where('user_id', $current_user)->delete();

                $response['error'] = false;
                $response['message'] = "Property remove from Favourite  successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }


        return response()->json($response);
    }

    public function get_articles(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $article = Article::with('category:id,category,slug_id')->select('id', 'slug_id', 'image', 'title', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'category_id', 'created_at');

        if (isset($request->category_id)) {
            $category_id = $request->category_id;
            if ($category_id == 0) {
                $article = $article->clone()->where('category_id', '');
            } else {

                $article = $article->clone()->where('category_id', $category_id);
            }
        }

        if (isset($request->id)) {
            $similarArticles = $article->clone()->where('id', '!=', $request->id)->get();
            $article = $article->clone()->where('id', $request->id);
        } else if (isset($request->slug_id)) {
            $category = Category::where('slug_id', $request->slug_id)->first();
            if ($category) {
                $article = $article->clone()->where('category_id', $category->id);
            } else {
                $similarArticles = $article->clone()->where('slug_id', '!=', $request->slug_id)->get();
                $article = $article->clone()->where('slug_id', $request->slug_id);
            }
        }


        $total = $article->clone()->get()->count();
        $result = $article->clone()->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();
        if (!$result->isEmpty()) {
            $result = $result->toArray();

            foreach ($result as &$item) {
                $item['meta_image'] = $item['image'];
                $item['created_at'] = Carbon::parse($item['created_at'])->diffForHumans();
            }

            $response['data'] = $result;
            $response['similar_articles'] = $similarArticles ?? array();
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['total'] = $total;
            $response['data'] = [];
        }
        return response()->json($response);
    }



    public function store_advertisement(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'feature_for' => 'required|in:property,project',
            'property_id' => 'nullable|required_if:feature_for,property',
            'project_id' => 'nullable|required_if:feature_for,project',
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            $current_user = Auth::user()->id;
            $advertisementQuery = Advertisement::whereIn('status',[0,1]);
            if($request->feature_for == 'property'){
                $packageData = HelperService::updatePackageLimit('property_feature',true);
                $checkAdvertisement = $advertisementQuery->clone()->where('property_id', $request->property_id)->count();
            }else{
                $packageData = HelperService::updatePackageLimit('project_feature',true);
                $checkAdvertisement = $advertisementQuery->clone()->where('project_id', $request->project_id)->count();
            }
            if(collect($packageData)->isEmpty()){
                ApiResponseService::validationError("Package not found");
            }
            if(!empty($checkAdvertisement)){
                ApiResponseService::validationError("Advertisement Already Exists");
            }
            $advertisementData = new Advertisement();
            $advertisementData->for = $request->feature_for;
            $advertisementData->start_date = Carbon::now();
            if (isset($request->end_date)) {
                $advertisementData->end_date = $request->end_date;
            } else {
                $advertisementData->end_date = Carbon::now()->addHours($packageData->duration);
            }
            $advertisementData->package_id = $packageData->id;
            $advertisementData->type = 'HomeScreen';
            if($request->feature_for == 'property'){
                $advertisementData->property_id = $request->property_id;
            }else{
                $advertisementData->project_id = $request->project_id;
            }
            $advertisementData->customer_id = $current_user;
            $advertisementData->is_enable = false;

            // Check the auto approve and verified user status and make advertisement auto approved or pending and is enable true or false
            $autoApproveStatus = $this->getAutoApproveStatus($current_user);
            if($autoApproveStatus){
                $advertisementData->status = 0;
                $advertisementData->is_enable = true;
            }else{
                $advertisementData->status = 1;
                $advertisementData->is_enable = false;
            }
            $advertisementData->save();

            DB::commit();
            ApiResponseService::successResponse("Advertisement add successfully");
        } catch (\Throwable $th) {
            DB::rollback();
            ApiResponseService::errorResponse();
        }
    }

    public function get_advertisement(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $date = date('Y-m-d');

        $adv = Advertisement::select('id', 'image', 'category_id', 'property_id', 'type', 'customer_id', 'is_enable', 'status')->with('customer:id,name')->where('end_date', '>', $date);
        if (isset($request->customer_id)) {
            $adv->where('customer_id', $request->customer_id);
        }
        $total = $adv->get()->count();
        $result = $adv->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();
        if (!$result->isEmpty()) {
            foreach ($adv as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.ADVERTISEMENT_IMAGE_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }


        return response()->json($response);
    }
    public function get_package(Request $request)
    {
        if ($request->platform == "ios") {
            $packages = OldPackage::where('status', 1)
                ->where('ios_product_id', '!=', '')
                ->orderBy('price', 'ASC')
                ->get();
        } else {
            $packages = Package::where('status', 1)
                ->orderBy('price', 'ASC')
                ->get();
        }

        $packages->transform(function ($item) use ($request) {
            if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
                $currentDate = Carbon::now()->format("Y-m-d");

                $loggedInUserId = Auth::guard('sanctum')->user()->id;
                $user_package = OldUserPurchasedPackage::where('modal_id', $loggedInUserId)->where(function ($query) use ($currentDate) {
                    $query->whereDate('start_date', '<=', $currentDate)
                        ->whereDate('end_date', '>=', $currentDate);
                });

                if ($request->type == 'property') {
                    $user_package->where('prop_status', 1);
                } else if ($request->type == 'advertisement') {
                    $user_package->where('adv_status', 1);
                }

                $user_package = $user_package->where('package_id', $item->id)->first();


                if (!empty($user_package)) {
                    $startDate = new DateTime(Carbon::now());
                    $endDate = new DateTime($user_package->end_date);

                    // Calculate the difference between two dates
                    $interval = $startDate->diff($endDate);

                    // Get the difference in days
                    $diffInDays = $interval->days;

                    $item['is_active'] = 1;
                    $item['type'] = $item->type === "premium_user" ? "premium_user" : "product_listing";

                    if (!($item->type === "premium_user")) {
                        $item['used_limit_for_property'] = $user_package->used_limit_for_property;
                        $item['used_limit_for_advertisement'] = $user_package->used_limit_for_advertisement;
                        $item['property_status'] = $user_package->prop_status;
                        $item['advertisement_status'] = $user_package->adv_status;
                    }

                    $item['start_date'] = $user_package->start_date;
                    $item['end_date'] = $user_package->end_date;
                    $item['remaining_days'] = $diffInDays;
                } else {
                    $item['is_active'] = 0;
                }
            }

            if (!($item->type === "premium_user")) {
                $item['advertisement_limit'] = $item->advertisement_limit == '' ? "unlimited" : ($item->advertisement_limit == 0 ? "not_available" : $item->advertisement_limit);
                $item['property_limit'] = $item->property_limit == '' ? "unlimited" : ($item->property_limit == 0 ? "not_available" : $item->property_limit);
            } else {
                unset($item['property_limit']);
                unset($item['advertisement_limit']);
            }


            return $item;
        });

        // Sort the packages based on is_active flag (active packages first)
        $packages = $packages->sortByDesc('is_active');

        $response = [
            'error' => false,
            'message' => 'Data Fetch Successfully',
            'data' => $packages->values()->all(), // Reset the keys after sorting
        ];

        return response()->json($response);
    }
    public function user_purchase_package(Request $request)
    {

        $start_date =  Carbon::now();
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);

        if (!$validator->fails()) {
            $loggedInUserId = Auth::user()->id;
            if (isset($request->flag)) {
                $user_exists = OldUserPurchasedPackage::where('modal_id', $loggedInUserId)->get();
                if ($user_exists) {
                    OldUserPurchasedPackage::where('modal_id', $loggedInUserId)->delete();
                }
            }

            $package = Package::find($request->package_id);
            $user = Customer::find($loggedInUserId);
            $data_exists = OldUserPurchasedPackage::where('modal_id', $loggedInUserId)->get();
            if (count($data_exists) == 0 && $package) {
                $user_package = new OldUserPurchasedPackage();
                $user_package->modal()->associate($user);
                $user_package->package_id = $request->package_id;
                $user_package->start_date = $start_date;
                $user_package->end_date = $package->duratio != 0 ? Carbon::now()->addDays($package->duration) : NULL;
                $user_package->save();

                $user->subscription = 1;
                $user->update();

                $response['error'] = false;
                $response['message'] = "purchased package  add successfully";
            } else {
                $response['error'] = true;
                $response['message'] = "data already exists or package not found or add flag for add new package";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    public function get_favourite_property(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;

        $current_user = Auth::user()->id;

        $favourite = Favourite::where('user_id', $current_user)->select('property_id')->get();
        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }

        $property_details = Property::whereIn('id', $arr)->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('parameters');
        $result = $property_details->clone()->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();

        $total = $property_details->clone()->count();

        if (!$result->isEmpty()) {

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] =  get_property_details($result, $current_user, true);
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function delete_advertisement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',

        ]);

        if (!$validator->fails()) {
            $adv = Advertisement::find($request->id);
            if (!$adv) {
                $response['error'] = false;
                $response['message'] = "Data not found";
            } else {

                $adv->delete();
                $response['error'] = false;
                $response['message'] = "Advertisement Deleted successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }
    public function interested_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',
            'type' => 'required'


        ]);
        if (!$validator->fails()) {
            $current_user = Auth::user()->id;

            $interested_user = InterestedUser::where('customer_id', $current_user)->where('property_id', $request->property_id);

            if ($request->type == 1) {

                if (count($interested_user->get()) > 0) {
                    $response['error'] = false;
                    $response['message'] = "already added to interested users ";
                } else {
                    $interested_user = new InterestedUser();
                    $interested_user->property_id = $request->property_id;
                    $interested_user->customer_id = $current_user;
                    $interested_user->save();
                    $response['error'] = false;
                    $response['message'] = "Interested Users added successfully";
                }
            }
            if ($request->type == 0) {

                if (count($interested_user->get()) == 0) {
                    $response['error'] = false;
                    $response['message'] = "No data found to delete";
                } else {
                    $interested_user->delete();

                    $response['error'] = false;
                    $response['message'] = "Interested Users removed  successfully";
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function user_interested_property(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;

        $current_user = Auth::user()->id;


        $favourite = InterestedUser::where('customer_id', $current_user)->select('property_id')->get();
        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }
        $property_details = Property::whereIn('id', $arr)->with('category:id,category')->with('parameters');
        $result = $property_details->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();


        $total = $result->count();

        if (!$result->isEmpty()) {
            foreach ($property_details as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_languages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_code' => 'required',
        ]);

        if (!$validator->fails()) {
            $language = Language::where('code', $request->language_code)->first();

            if ($language) {
                if ($request->web_language_file) {
                    $json_file_path = public_path('web_languages/' . $request->language_code . '.json');
                } else {
                    $json_file_path = public_path('languages/' . $request->language_code . '.json');
                }

                if (file_exists($json_file_path)) {
                    $json_string = file_get_contents($json_file_path);
                    $json_data = json_decode($json_string);

                    if ($json_data !== null) {
                        $language->file_name = $json_data;
                        $response['error'] = false;
                        $response['message'] = "Data Fetch Successfully";
                        $response['data'] = $language;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Invalid JSON format in the language file";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Language file not found";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Language not found";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }

        return response()->json($response);
    }
    public function getPaymentTransactionDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_type' => 'nullable|string|in:online payment,bank transfer,free'
            ]);
            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }
            // Get offset and limit
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;
            // Get logged in user id
            $loggedInUserId = Auth::user()->id;

            // Get payment query
            $paymentQuery = PaymentTransaction::where('user_id', $loggedInUserId)
            // Filter by payment type if provided
            ->when($request->payment_type, function ($query) use ($request) {
                $query->where('payment_type', $request->payment_type);
            });
            // Get total count of filtered results
            $total = $paymentQuery->clone()->count();
            // Get paginated results
            $result = $paymentQuery->with('package:id,name,price')->orderBy('created_at','DESC')->skip($offset)->take($limit)->get();

            if (count($result)) {
                ApiResponseService::successResponse("Data Fetch Successfully", $result, array('total' => $total));
            } else {
                ApiResponseService::errorResponse("No data found!");
            }
        } catch (Exception $e) {
            ApiResponseService::errorResponse($e->getMessage());
        }
    }



    public function paypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try{
            DB::beginTransaction();

            $currentUser = Auth::user();
            $package = Package::where(['id' => $request->package_id, 'status' => 1])->first();
            if(collect($package)->isEmpty()){
                ApiResponseService::validationError("Packge not found");
            }

            //Add Payment Data to Payment Transactions Table
            $paymentTransactionData = PaymentTransaction::create([
                'user_id'         => $currentUser->id,
                'package_id'      => $package->id,
                'amount'          => $package->price,
                'payment_gateway' => "Paypal",
                'payment_status'  => 'pending',
                'order_id'        => null,
                'payment_type'    => 'online payment'
            ]);
            $returnURL = url('api/app_payment_status?error=false&payment_transaction_id='.$paymentTransactionData->id);
            $cancelURL = url('api/app_payment_status?error=true&payment_transaction_id='.$paymentTransactionData->id);
            $notifyURL = url('webhook/paypal');

            $paypal = new Paypal();
            // Get current user ID from the session
            $paypal->add_field('return', $returnURL);
            $paypal->add_field('cancel_return', $cancelURL);
            $paypal->add_field('notify_url', $notifyURL);
            $custom_data = $paymentTransactionData->id;

            // // Add fields to paypal form
            $paypal->add_field('item_name', "package");
            $paypal->add_field('custom_id', json_encode($custom_data));
            $paypal->add_field('custom', ($custom_data));
            $paypal->add_field('amount', $request->amount);

            DB::commit();
            // Render paypal form
            $paypal->paypal_auto_form();
        } catch(Exception $e){
            DB::rollBack();
            ApiResponseService::errorResponse();
        }
    }
    public function app_payment_status(Request $request)
    {
        $paypalInfo = $request->all();
        $paymentTransactionId = $request->payment_transaction_id;
        // Get Web URL
        $webURL = system_setting('web_url') ?? null;
        if (isset($paypalInfo) && !empty($paypalInfo) && isset($paypalInfo['payment_status']) && !empty($paypalInfo['payment_status'])){
            if($paypalInfo['payment_status'] == "Completed") {
                $webWithStatusURL = $webURL.'/payment/success';
                $response['error'] = false;
                $response['message'] = "Your Purchase Package Activate Within 10 Minutes ";
                $response['data'] = $paypalInfo['txn_id'];
            } elseif ($paypalInfo['payment_status'] == "Authorized") {
                $webWithStatusURL = $webURL.'/payment/success';
                $response['error'] = false;
                $response['message'] = "Your payment has been Authorized successfully. We will capture your transaction within 30 minutes, once we process your order. After successful capture Ads wil be credited automatically.";
                $response['data'] = $paypalInfo;
            } else {
                PaymentTransaction::where('id', $paymentTransactionId)->update(['payment_status' => 'failed']);
                $webWithStatusURL = $webURL.'/payment/fail';
                $response['error'] = true;
                $response['message'] = "Payment Cancelled / Declined ";
                $response['data'] = !empty($paypalInfo) ? $paypalInfo : "";
            }
        }else{
            PaymentTransaction::where('id', $paymentTransactionId)->update(['payment_status' => 'failed']);
            $webWithStatusURL = $webURL.'/payment/fail';
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
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
        // return (response()->json($response));
    }
    public function get_payment_settings(Request $request)
    {
        $payment_settings = Setting::select('type', 'data')->whereIn('type', ['paypal_business_id', 'sandbox_mode', 'paypal_gateway', 'razor_key', 'razor_secret', 'razorpay_gateway', 'paystack_public_key', 'paystack_secret_key', 'paystack_currency', 'paystack_gateway', 'stripe_publishable_key', 'stripe_currency', 'stripe_gateway', 'stripe_secret_key','flutterwave_status', 'bank_transfer_status'])->get();
        foreach ($payment_settings as $setting) {
            if ($setting->type === 'stripe_secret_key') {
                $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                $encryptedData = '';
                if (openssl_public_encrypt($setting->data, $encryptedData, $publicKey)) {
                    $setting->data = base64_encode($encryptedData);
                }
            }
        }

        if (count($payment_settings)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $payment_settings;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return (response()->json($response));
    }
    public function send_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'property_id' => 'required',
            'file' => 'nullable|mimes:png,jpg,jpeg,pdf,doc,docx|max:2024',
            'audio' => 'nullable|mimes:mpeg,m4a,mp3,mp4|max:5024'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $customer = Customer::select('id', 'name', 'profile')->with(['usertokens' => function ($q) {
            $q->select('fcm_id', 'id', 'customer_id');
        }])->find($request->receiver_id);
        if(collect($customer)->isNotEmpty()){
            $senderBlockedReciever = BlockedChatUser::where(['by_user_id' => $request->sender_id, 'user_id' => $request->receiver_id])->count();
            if($senderBlockedReciever){
                ApiResponseService::validationError("You have blocked user");
            }
            $recieverBlockedSender = BlockedChatUser::where(['by_user_id' => $request->receiver_id, 'user_id' => $request->sender_id])->count();
            if($recieverBlockedSender){
                ApiResponseService::validationError("You are blocked by user");
            }
        }else{
            $senderBlockedReciever = BlockedChatUser::where(['by_user_id' => $request->sender_id, 'admin' => 1])->count();
            if($senderBlockedReciever){
                ApiResponseService::validationError("You have blocked admin");
            }
            $recieverBlockedSender = BlockedChatUser::where(['by_admin' => 1, 'user_id' => $request->sender_id])->count();
            if($recieverBlockedSender){
                ApiResponseService::validationError("You are blocked by admin");
            }
        }

        $fcm_id = array();
        $chat = new Chats();
        $chat->sender_id = $request->sender_id;
        $chat->receiver_id = $request->receiver_id;
        $chat->property_id = $request->property_id;
        $chat->message = $request->message;

        $destinationPath = public_path('images') . config('global.CHAT_FILE');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        // Files upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $chat->file = $fileName;
        } else {
            $chat->file = '';
        }

        $audiodestinationPath = public_path('images') . config('global.CHAT_AUDIO');
        if (!is_dir($audiodestinationPath)) {
            mkdir($audiodestinationPath, 0777, true);
        }
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
            $file->move($audiodestinationPath, $fileName);
            $chat->audio = $fileName;
        } else {
            $chat->audio = '';
        }
        $chat->save();

        if ($customer) {
            foreach ($customer->usertokens as $usertokens) {
                array_push($fcm_id, $usertokens->fcm_id);
            }
            $username = $customer->name;
        } else {

            $user_data = User::select('fcm_id', 'name')->get();
            $username = "Admin";
            foreach ($user_data as $user) {
                array_push($fcm_id, $user->fcm_id);
            }
        }
        $senderUser = Customer::select('fcm_id', 'name', 'profile')->find($request->sender_id);
        if ($senderUser) {
            $profile = $senderUser->profile;
        } else {
            $profile = "";
        }

        $Property = Property::find($request->property_id);






        $chat_message_type = "";

        if (!empty($request->file('audio'))) {
            $chat_message_type = "audio";
        } else if (!empty($request->file('file')) && $request->message == "") {
            $chat_message_type = "file";
        } else if (!empty($request->file('file')) && $request->message != "") {
            $chat_message_type = "file_and_text";
        } else if (empty($request->file('file')) && $request->message != "" && empty($request->file('audio'))) {
            $chat_message_type = "text";
        } else {
            $chat_message_type = "text";
        }


        // Get UnRead Messages Count
        $unreadMessagesCount = Chats::where(['property_id' => $request->property_id, 'receiver_id' => $request->sender_id, 'is_read' => false])->count();


        $fcmMsg = array(
            'title' => 'Message',
            'message' => $request->message,
            'type' => 'chat',
            'body' => $request->message,
            'sender_id' => $request->sender_id,
            'sender_name' => $senderUser->name ?? 'User',
            'sender_profile' => $senderUser->profile ?? '',
            'receiver_id' => $request->receiver_id,
            'file' => $chat->file,
            'username' => $username,
            'user_profile' => $profile,
            'audio' => $chat->audio,
            'date' => $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'sound' => 'default',
            'time_ago' => $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true),
            'property_id' => (string)$Property->id,
            'property_title_image' => $Property->title_image,
            'title' => $Property->title,
            'chat_message_type' => $chat_message_type,
            'created_at' => Carbon::parse($chat->created_at)->toIso8601ZuluString(),
            'unread_messages_count' => (string)$unreadMessagesCount
        );

        $send = send_push_notification($fcm_id, $fcmMsg);
        $response['error'] = false;
        $response['message'] = "Data Store Successfully";
        $response['id'] = $chat->id;
        // $response['data'] = $send;
        return (response()->json($response));
    }
    public function get_messages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',
            'user_id' => 'required'

        ]);
        if (!$validator->fails()) {
            $currentUser = Auth::user();
            $userId = $request->user_id;

            // update is_read to true for the current user
            Chats::where(['property_id' => $request->property_id, 'receiver_id' => $currentUser->id, 'is_read' => false])
                ->update(['is_read' => true]);

            $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
            $page = $request->page ?? 1; // Get the current page from the query string, or default to 1
            $chat = Chats::where('property_id', $request->property_id)
                ->where(function ($query) use ($currentUser, $userId) {
                    $query->where(function ($query) use ($currentUser, $userId) {
                        $query->where(['sender_id' => $currentUser->id, 'receiver_id' => $userId])
                            ->orWhere(['sender_id' => $userId, 'receiver_id' => $currentUser->id]);
                    });
                })
                ->orderBy('created_at', 'DESC')
                ->paginate($perPage, ['*'], 'page', $page);

            // You can then pass the $chat object to your view to display the paginated results.




            $chat_message_type = "";
            if ($chat) {


                $chat->map(function ($chat) use ($chat_message_type, $currentUser) {
                    if (!empty($chat->audio)) {
                        $chat_message_type = "audio";
                    } else if (!empty($chat->file) && $chat->message == "") {
                        $chat_message_type = "file";
                    } else if (!empty($chat->file) && $chat->message != "") {
                        $chat_message_type = "file_and_text";
                    } else if (empty($chat->file) && !empty($chat->message) && empty($chat->audio)) {
                        $chat_message_type = "text";
                    } else {
                        $chat_message_type = "text";
                    }
                    $chat['chat_message_type'] = $chat_message_type;
                    $chat['user_profile'] = $currentUser->profile;
                    $chat['time_ago'] = $chat->created_at->diffForHumans();
                });

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total_page'] = $chat->lastPage();
                $response['data'] = $chat;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function get_chats(Request $request)
    {
        $current_user = Auth::user()->id;
        $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
        $page = $request->page ?? 1;

        $adminData = User::where('type',0)->select('id','name','profile')->first();

        $chat = Chats::with(['sender', 'receiver'])->with('property')
           ->select('id', 'sender_id', 'receiver_id', 'property_id', 'created_at',
                DB::raw('LEAST(sender_id, receiver_id) as user1_id'),
                DB::raw('GREATEST(sender_id, receiver_id) as user2_id'),
                DB::raw('COUNT(CASE WHEN receiver_id = '.$current_user.' AND is_read = 0 THEN 1 END) AS unread_count')
            )
            ->where(function($query) use ($current_user) {
                $query->where('sender_id', $current_user)
                    ->orWhere('receiver_id', $current_user);
            })
            ->orderBy('id', 'desc')
            ->groupBy('user1_id', 'user2_id', 'property_id')
            ->paginate($perPage, ['*'], 'page', $page);

        if (!$chat->isEmpty()) {

            $rows = array();

            $count = 1;

            $response['total_page'] = $chat->lastPage();

            foreach ($chat as $key => $row) {
                $tempRow = array();
                $tempRow['property_id'] = $row->property_id;
                $tempRow['title'] = $row->property->title;
                $tempRow['title_image'] = $row->property->title_image;
                $tempRow['date'] = $row->created_at;
                $tempRow['property_id'] = $row->property_id;
                $tempRow['unread_count'] = $row->unread_count;
                if (!$row->receiver || !$row->sender) {
                    $user = Customer::where('id', $row->sender_id)->orWhere('id', $row->receiver_id)->select('id')->first();

                    $isBlockedByMe = false;
                    $isBlockedByUser = false;

                    $blockedByMe = BlockedChatUser::where('by_user_id', $user->id)
                        ->where('admin', 1)
                        ->exists();

                    $blockedByAdmin = BlockedChatUser::where('by_admin', 1)
                        ->where('user_id', $user->id)
                        ->exists();
                    $tempRow['is_blocked_by_me'] = $blockedByMe;
                    $tempRow['is_blocked_by_user'] = $blockedByAdmin;


                    $tempRow['user_id'] = 0;
                    $tempRow['name'] = "Admin";
                    $tempRow['profile'] = !empty($adminData->getRawOriginal('profile')) ? $adminData->profile : url('assets/images/faces/2.jpg');

                    // $tempRow['fcm_id'] = $row->receiver->fcm_id;
                } else {

                    $isBlockedByMe = false;
                    $isBlockedByUser = false;
                    if ($row->sender->id == $current_user) {
                        $isBlockedByMe = BlockedChatUser::where('by_user_id', $current_user)
                            ->where('user_id', $row->receiver->id)
                            ->exists();

                        $isBlockedByUser = BlockedChatUser::where('by_user_id', $row->receiver->id)
                            ->where('user_id', $current_user)
                            ->exists();

                        $tempRow['is_blocked_by_me'] = $isBlockedByMe;
                        $tempRow['is_blocked_by_user'] = $isBlockedByUser;

                        $tempRow['user_id'] = $row->receiver->id;
                        $tempRow['name'] = $row->receiver->name;
                        $tempRow['profile'] = $row->receiver->profile;
                        $tempRow['fcm_id'] = $row->receiver->fcm_id;
                    }
                    if ($row->receiver->id == $current_user) {

                        $isBlockedByMe = BlockedChatUser::where('by_user_id', $current_user)
                            ->where('user_id', $row->sender->id)
                            ->exists();

                        $isBlockedByUser = BlockedChatUser::where('by_user_id', $row->sender->id)
                            ->where('user_id', $current_user)
                            ->exists();

                        $tempRow['is_blocked_by_me'] = $isBlockedByMe;
                        $tempRow['is_blocked_by_user'] = $isBlockedByUser;

                        $tempRow['user_id'] = $row->sender->id;
                        $tempRow['name'] = $row->sender->name;
                        $tempRow['profile'] = $row->sender->profile;
                        $tempRow['fcm_id'] = $row->sender->fcm_id;
                    }
                }
                $rows[] = $tempRow;
                $count++;
            }


            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_nearby_properties(Request $request)
    {
        $latitude = $request->has('latitude') ? $request->latitude : null;
        $longitude = $request->has('longitude') ? $request->longitude : null;
        $radius = $request->has('radius') ? $request->radius : null;

        // Create reusable property mapper function
        $propertyMapper = function($propertyData) {
            $propertyData->promoted = $propertyData->is_promoted;
            $propertyData->property_type = $propertyData->propery_type;
            $propertyData->parameters = $propertyData->parameters;
            $propertyData->is_premium = $propertyData->is_premium == 1;
            return $propertyData;
        };

        // Base property query that will be reused
        $propertyQuery = Property::select(
            'id', 'slug_id', 'category_id', 'city', 'state', 'country',
            'price', 'propery_type', 'title', 'title_image', 'is_premium',
            'address', 'rentduration', 'latitude', 'longitude'
        )
        ->with('category:id,slug_id,image,category')
        ->where(['status' => 1, 'request_status' => 'approved'])
        ->whereIn('propery_type', [0, 1]);

        if($latitude && $longitude){
            if($radius){
                // Create a query with Haversine formula
                $propertyQuery->
                    selectRaw("
                    (6371 * acos(cos(radians($latitude))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians($longitude))
                    + sin(radians($latitude))
                    * sin(radians(latitude)))) AS distance")
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->having('distance', '<', $radius);
            }else{
                $$propertyQuery->where(['latitude' => $latitude, 'longitude' => $longitude]);
            }
        }


        // Check the city and state params and query the params according to it
        if (isset($request->city) || isset($request->state)) {
            $propertyQuery->where(function ($query) use ($request) {
                $query->where('state', 'LIKE', "%{$request->state}%")
                    ->orWhere('city', 'LIKE', "%{$request->city}%");
            });
        }

        // Check the type params and query the params according to it
        if (isset($request->type)) {
            $propertyQuery->where('propery_type', $request->type);
        }

        // Get Final Data
        $propertiesData = $propertyQuery->get()->map($propertyMapper);

        // Pass data as json
        if (collect($propertiesData)->isNotEmpty()) {
            $response['error'] = false;
            $response['data'] = $propertiesData;
        } else {
            $response['error'] = false;
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function update_property_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:2,3',
            'property_id' => 'required|exists:propertys,id'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $property = Property::find($request->property_id);

            if($property->getRawOriginal('propery_type') == 0 && $request->status != 2){
                ApiResponseService::validationError("You can only change sell property to sold");
            }
            else if($property->getRawOriginal('propery_type') == 1 && $request->status != 3){
                ApiResponseService::validationError("You can only change rent property to rented");
            }
            else if($property->getRawOriginal('propery_type') != 0 && $property->getRawOriginal('propery_type') != 1){
                ApiResponseService::validationError("You can only change status of sell and rent properties");
            }
            $property->propery_type = $request->status;
            $property->save();
            $response['error'] = false;
            $response['message'] = "Data updated Successfully";
            return response()->json($response);
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }
    public function getCitiesData(Request $request)
    {
        // Get Offset and Limit from payload request
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $city_arr = array();
        $citiesQuery = CityImage::where('status',1)->withCount(['property' => function ($query) {
            $query->whereIn('propery_type',[0,1])->where(['status' => 1, 'request_status' => 'approved']);
        }])->having('property_count', '>', 0);
        $totalData = $citiesQuery->clone()->count();
        $citiesData = $citiesQuery->clone()->orderBy('property_count','DESC')->skip($offset)->take($limit)->get();
        foreach ($citiesData as $city) {
            if (!empty($city->getRawOriginal('image'))) {
                $url = $city->image;
                $relativePath = parse_url($url, PHP_URL_PATH);
                if (file_exists(public_path()  . $relativePath)) {
                    array_push($city_arr, ['City' => $city->city, 'Count' => $city->property_count, 'image' => $city->image]);
                    continue;
                }
            }
            $resultArray = $this->getUnsplashData($city);
            array_push($city_arr, $resultArray);
        }
        $response['error'] = false;
        $response['data'] = $city_arr;
        $response['total'] = $totalData;
        $response['message'] = "Data Fetched Successfully";

        return response()->json($response);
    }

    public function get_facilities(Request $request)
    {
        $facilities = OutdoorFacilities::query();

        // if (isset($request->search) && !empty($request->search)) {
        //     $search = $request->search;
        //     $facilities->where('category', 'LIKE', "%$search%");
        // }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $facilities->where('id', '=', $id);
        }
        $total = $facilities->clone()->count();
        $result = $facilities->clone()->get();


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_report_reasons(Request $request)
    {
        $report_reason = report_reasons::query();

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $report_reason->where('id', '=', $id);
        }
        $result = $report_reason->clone()->get();

        $total = $report_reason->clone()->count();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function add_reports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason_id' => 'required',
            'property_id' => 'required',



        ]);
        $current_user = Auth::user()->id;
        if (!$validator->fails()) {
            $report_count = user_reports::where('property_id', $request->property_id)->where('customer_id', $current_user)->get();
            if (!count($report_count)) {
                $report_reason = new user_reports();
                $report_reason->reason_id = $request->reason_id ? $request->reason_id : 0;
                $report_reason->property_id = $request->property_id;
                $report_reason->customer_id = $current_user;
                $report_reason->other_message = $request->other_message ? $request->other_message : '';



                $report_reason->save();


                $response['error'] = false;
                $response['message'] = "Report Submited Successfully";
            } else {
                $response['error'] = false;
                $response['message'] = "Already Reported";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    public function delete_chat_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try{
            // Get Customer IDs

            // Get FCM IDs
            $fcmId = Usertokens::select('fcm_id')->where('customer_id', $request->receiver_id)->pluck('fcm_id')->toArray();

            if (isset($request->message_id)) {
                $chat = Chats::find($request->message_id);
                if ($chat) {
                    if (!empty($fcmId)) {
                        $registrationIDs = array_filter($fcmId);
                        $fcmMsg = array(
                            'title' => "Delete Chat Message",
                            'message' => "Message Deleted Successfully",
                            "image" => '',
                            'type' => 'delete_message',
                            'message_id' => $request->message_id,
                            'body' => 'Message Deleted Successfully',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',

                        );
                        send_push_notification($registrationIDs, $fcmMsg, 1);
                    }
                    $chat->delete();
                    ApiResponseService::successResponse("Message Deleted Successfully");
                }else{
                    ApiResponseService::validationError("No data found");
                }
            }else if (isset($request->sender_id) && isset($request->receiver_id) && isset($request->property_id)) {

                $user_chat = Chats::where('property_id', $request->property_id)
                    ->where(function ($query) use ($request) {
                        $query->where('sender_id', $request->sender_id)
                            ->orWhere('receiver_id', $request->receiver_id);
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->where('sender_id', $request->receiver_id)
                            ->orWhere('receiver_id', $request->sender_id);
                    });
                if (count($user_chat->get())) {

                    $user_chat->delete();
                    ApiResponseService::successResponse("chat deleted successfully");
                } else {
                    ApiResponseService::validationError("No data found");
                }
            } else {
                ApiResponseService::validationError("No data found");
            }
        } catch(Exception $e){
            ApiResponseService::errorResponse();
        }
    }
    public function get_user_recommendation(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $current_user = Auth::user()->id;


        $user_interest = UserInterest::where('user_id', $current_user)->first();
        if (collect($user_interest)->isNotEmpty()) {

            $property = Property::with('customer')->with('user')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where(['status' => 1, 'request_status' => 'approved']);


            $property_type = $request->property_type;
            if ($user_interest->category_ids != '') {

                $category_ids = explode(',', $user_interest->category_ids);

                $property = $property->whereIn('category_id', $category_ids);
            }

            if ($user_interest->price_range != '') {

                $max_price = explode(',', $user_interest->price_range)[1];

                $min_price = explode(',', $user_interest->price_range)[0];

                if (isset($max_price) && isset($min_price)) {
                    $min_price = floatval($min_price);
                    $max_price = floatval($max_price);

                    $property = $property->where(function ($query) use ($min_price, $max_price) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_price])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_price]);
                    });
                }
            }


            if ($user_interest->city != '') {
                $city = $user_interest->city;
                $property = $property->where('city', $city);
            }
            if ($user_interest->property_type != '') {
                $property_type = explode(',',  $user_interest->property_type);
            }
            if ($user_interest->outdoor_facilitiy_ids != '') {


                $outdoor_facilitiy_ids = explode(',', $user_interest->outdoor_facilitiy_ids);
                $property = $property->whereHas('assignfacilities.outdoorfacilities', function ($q) use ($outdoor_facilitiy_ids) {
                    $q->whereIn('id', $outdoor_facilitiy_ids);
                });
            }



            if (isset($property_type)) {
                if (count($property_type) == 2) {
                    $property_type = $property->where(function ($query) use ($property_type) {
                        $query->where('propery_type', $property_type[0])->orWhere('propery_type', $property_type[1]);
                    });
                } else {
                    if (isset($property_type[0])  &&  $property_type[0] == 0) {

                        $property = $property->where('propery_type', $property_type[0]);
                    }
                    if (isset($property_type[0])  &&  $property_type[0] == 1) {

                        $property = $property->where('propery_type', $property_type[0]);
                    }
                }
            }



            $total = $property->get()->count();

            $result = $property->skip($offset)->take($limit)->get();
            $property_details = get_property_details($result, $current_user, true);

            if (!empty($result)) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $property_details;
            } else {

                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return ($response);
    }
    public function contct_us(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        if (!$validator->fails()) {

            $contactrequest = new Contactrequests();
            $contactrequest->first_name = $request->first_name;
            $contactrequest->last_name = $request->last_name;
            $contactrequest->email = $request->email;
            $contactrequest->subject = $request->subject;
            $contactrequest->message = $request->message;
            $contactrequest->save();
            $response['error'] = false;
            $response['message'] = "Contact Request Send successfully";
        } else {


            $response['error'] = true;
            $response['message'] =  $validator->errors()->first();
        }
        return response()->json($response);
    }
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $current_user = Auth::user()->id;

            $secret_key = system_setting('stripe_secret_key');

            $stripe_currency = system_setting('stripe_currency');
            $package = Package::find($request->package_id);

            $data = [
                'amount' => ((int)($package['price'])) * 100,
                'currency' => $stripe_currency,
                'description' => $request->description ?? $package->name,
                'payment_method_types[]' => $request->payment_method,
                'metadata' => [
                    'userId' => $current_user,
                    'packageId' => $request->package_id,
                ],
                'shipping' => null
            ];
            $headers = [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $response = Http::withHeaders($headers)->asForm()->post('https://api.stripe.com/v1/payment_intents', $data);
            $responseData = $response->json();
            return response()->json([
                'data' => $responseData,
                'message' => 'Intent created.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing the payment.',
            ], 500);
        }
    }
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paymentIntentId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {

            $secret_key = system_setting('stripe_secret_key');
            $headers = [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $response = Http::withHeaders($headers)
                ->get("https://api.stripe.com/v1/payment_intents/{$request->paymentIntentId}");
            $responseData = $response->json();
            $statusOfTransaction = $responseData['status'];
            if ($statusOfTransaction == 'succeeded') {
                return response()->json([
                    'message' => 'Transaction successful',
                    'success' => true,
                    'status' => $statusOfTransaction,
                ]);
            } elseif ($statusOfTransaction == 'pending' || $statusOfTransaction == 'captured') {
                return response()->json([
                    'message' => 'Transaction pending',
                    'success' => true,
                    'status' => $statusOfTransaction,
                ]);
            } else {
                return response()->json([
                    'message' => 'Transaction failed',
                    'success' => false,
                    'status' => $statusOfTransaction,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing the payment.',
            ], 500);
        }
    }
    public function delete_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:propertys,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            DB::beginTransaction();
            Property::findOrFail($request->id)->delete();
            DB::commit();
            $response['error'] = false;
            $response['message'] =  'Delete Successfully';
            return response()->json($response);
        }catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    public function assign_package(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'product_id' => 'required_if:in_app,true',
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            $loggedInUserId = Auth::user()->id;

            if ($request->in_app == 'true' || $request->in_app === true) {
                $package = Package::where('ios_product_id', $request->product_id)->first();
            } else {
                $package = Package::where('id',$request->package_id)->first();
                if($package->package_type == 'paid'){
                    ApiResponseService::validationError("Package is paid cannot assign directly");
                }
            }
            if (collect($package)->isNotEmpty()) {
                DB::beginTransaction();
                // Assign Package to user
                $userPackage = UserPackage::create([
                    'package_id'  => $package->id,
                    'user_id'     => $loggedInUserId,
                    'start_date'  => Carbon::now(),
                    'end_date'    => $package->package_type == "unlimited" ? null : Carbon::now()->addHours($package->duration),
                ]);

                // Create Payment Transaction
                PaymentTransaction::create([
                    'user_id' => $loggedInUserId,
                    'package_id' => $package->id,
                    'amount' => 0,
                    'payment_gateway' => null,
                    'payment_type' => 'free',
                    'payment_status' => 'success',
                    'order_id' => Str::uuid(),
                    'transaction_id' => Str::uuid()
                ]);

                // Assign limited count feature to user with limits
                $packageFeatures = PackageFeature::where(['package_id' => $package->id, 'limit_type' => 'limited'])->get();
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
                DB::commit();
                ApiResponseService::successResponse("Package Purchased Successfully");
            } else {
                ApiResponseService::validationError("Package Not Found");
            }
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function get_app_settings(Request $request)
    {
        $result =  Setting::select('type', 'data')->whereIn('type', ['app_home_screen', 'placeholder_logo', 'light_tertiary', 'light_secondary', 'light_primary', 'dark_tertiary', 'dark_secondary', 'dark_primary'])->get();


        $tempRow = [];

        if (($request->user_id) != "") {
            update_subscription($request->user_id);

            $customer_data = Customer::find($request->user_id);
            if ($customer_data) {
                if ($customer_data->isActive == 0) {

                    $tempRow['is_active'] = false;
                } else {
                    $tempRow['is_active'] = true;
                }
            }
        }



        foreach ($result as $row) {
            $tempRow[$row->type] = $row->data;

            if ($row->type == 'app_home_screen' || $row->type == "placeholder_logo") {

                $tempRow[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
            }
        }

        $response['error'] = false;
        $response['data'] = $tempRow;
        return response()->json($response);
    }
    public function get_seo_settings(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $seo_settings = SeoSettings::select('id', 'page', 'image', 'title', 'description', 'keywords');


        if (isset($request->page) && !empty($request->page)) {

            $seo_settings->where('page', 'LIKE', "%$request->page%");
        } else {
            $seo_settings->where('page', 'LIKE', "%homepage%");
        }

        $total = $seo_settings->count();
        $result = $seo_settings->skip($offset)->take($limit)->get();


        // $seo_settingsWithCount = Category::withCount('properties')->get();
        $rows = array();
        $count = 0;
        if (!$result->isEmpty()) {

            foreach ($result as $key => $row) {
                $tempRow['id'] = $row->id;
                $tempRow['page'] = $row->page;
                $tempRow['meta_image'] = $row->image;

                if ($row->page == "properties-city") {
                    $tempRow['meta_title'] = "[Your City]'s Finest:" . $row->title;
                    $tempRow['meta_description'] = "Discover the charm of living near [Your City]." . $row->description;
                } else {

                    $tempRow['meta_title'] = $row->title;
                    $tempRow['meta_description'] = $row->description;
                }
                $tempRow['meta_keywords'] = $row->keywords;

                $rows[] = $tempRow;

                $count++;
            }
        }


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";


            $response['total'] = $total;
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function getInterestedUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id' => 'required_without:slug_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->first(),
                ]);
            }

            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            if (isset($request->slug_id)) {
                $property = Property::where('slug_id', $request->slug_id)->first();
                $property_id = $property->id;
            } else {
                $property_id = $request->property_id;
            }

            $interestedUserQuery = InterestedUser::has('customer')->with('customer:id,name,profile,email,mobile')->where('property_id', $property_id);
            $totalData = $interestedUserQuery->clone()->count();
            $interestedData = $interestedUserQuery->take($limit)->skip($offset)->get();
            if(collect($interestedData)->isNotEmpty()){
                $data = $interestedData->pluck('customer');
                ApiResponseService::successResponse("Data Fetched Successfully",$data,['total' => $totalData]);
            }else{
                ApiResponseService::validationError("No Data Found");
            }
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }
    public function post_project(Request $request)
    {
        if ($request->has('id')) {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'title'         => 'required',
                'description'   => 'required',
                'image'         => 'required|file|max:3000|mimes:jpeg,png,jpg',
                'category_id'   => 'required',
                'city'          => 'required',
                'state'         => 'required',
                'country'       => 'required',
                'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                    // Regular expression to validate YouTube URLs
                    $youtubePattern = '/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/';

                    if (!preg_match($youtubePattern, $value)) {
                        return $fail("The Video Link must be a valid YouTube URL.");
                    }

                    // Transform youtu.be short URL to full YouTube URL for validation
                    if (strpos($value, 'youtu.be') !== false) {
                        $value = 'https://www.youtube.com/watch?v=' . substr(parse_url($value, PHP_URL_PATH), 1);
                    }

                    // Get the headers of the URL
                    $headers = @get_headers($value);

                    // Check if the URL is accessible
                    if (!$headers || strpos($headers[0], '200') === false) {
                        return $fail("The Video Link must be accessible.");
                    }
                }]
            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            DB::beginTransaction();
            if(!$request->id){
                HelperService::updatePackageLimit('project_list');
            }
            $slugData = (isset($request->slug_id) && !empty($request->slug_id)) ? $request->slug_id : $request->title;

            $currentUser = Auth::user()->id;
            if (!(isset($request->id))) {
                $project = new Projects();

                // Check the auto approve and verified user status and make project auto enable or disable
                $autoApproveStatus = $this->getAutoApproveStatus($currentUser);
                if($autoApproveStatus){
                    $project->status = 1;
                }else{
                    $project->status = 0;
                }
            } else {
                $project = Projects::where('added_by', $currentUser)->find($request->id);
                if (!$project) {
                    $response['error'] = false;
                    $response['message'] = 'Project Not Found ';
                }
                if(HelperService::getSettingData('auto_approve_edited_listings') == 0){
                    $project->request_status = 'pending';
                }
            }

            if ($request->category_id) {
                $project->category_id = $request->category_id;
            }
            if ($request->description) {
                $project->description = $request->description;
            }
            if ($request->location) {
                $project->location = $request->location;
            }
            if ($request->meta_title) {
                $project->meta_title = $request->meta_title;
            }
            if ($request->meta_description) {
                $project->meta_description = $request->meta_description;
            }
            if ($request->meta_keywords) {
                $project->meta_keywords = $request->meta_keywords;
            }
            $project->added_by = $currentUser;
            if ($request->country) {
                $project->country = $request->country;
            }
            if ($request->state) {
                $project->state = $request->state;
            }
            if ($request->city) {
                $project->city = $request->city;
            }
            if ($request->latitude) {
                $project->latitude = $request->latitude;
            }
            if ($request->longitude) {
                $project->longitude = $request->longitude;
            }
            if ($request->video_link) {
                $project->video_link = $request->video_link;
            }
            if ($request->type) {
                $project->type = $request->type;
            }
            if ($request->id) {
                if ($project->title !== $request->title) {
                    $title = !empty($request->title) ? $request->title : $project->title;
                    $project->title = $title;
                } else {
                    $title = $request->title;
                    $project->title = $title;
                }
                $project->slug_id = generateUniqueSlug($slugData, 4, null, $request->id);
                if ($request->hasFile('image')) {
                    $project->image = store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH');
                }

                if($request->has('meta_image')){
                    if($request->meta_image != $project->meta_image){
                        if (!empty($request->meta_image && $request->hasFile('meta_image'))) {
                            if (!empty($project->meta_image)) {
                                $url = $project->meta_image;
                                $relativePath = parse_url($url, PHP_URL_PATH);
                                if (file_exists(public_path()  . $relativePath)) {
                                    unlink(public_path()  . $relativePath);
                                }
                            }
                            $destinationPath = public_path('images') . config('global.PROJECT_SEO_IMG_PATH');
                            if (!is_dir($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $profile = $request->file('meta_image');
                            $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                            $profile->move($destinationPath, $imageName);
                            $project->meta_image = $imageName;
                        } else {
                            if (!empty($project->meta_image)) {
                                $url = $project->meta_image;
                                $relativePath = parse_url($url, PHP_URL_PATH);
                                if (file_exists(public_path()  . $relativePath)) {
                                    unlink(public_path()  . $relativePath);
                                }
                            }
                            $project->meta_image = null;
                        }
                    }
                }
                // if ($request->hasFile('meta_image')) {
                //     if ($project->meta_image) {
                //         unlink_image($project->meta_image);
                //     }
                //     $project->meta_image = store_image($request->file('meta_image'), 'PROJECT_SEO_IMG_PATH');
                // }else{
                //     if ($request->has('image')){
                //         if(!empty($request->meta_image)) {
                //             $url = $project->meta_image;
                //             $relativePath = parse_url($url, PHP_URL_PATH);
                //             if (file_exists(public_path()  . $relativePath)) {
                //                 unlink(public_path()  . $relativePath);
                //             }
                //         }
                //         $project->meta_image = null;
                //     }
                // }
            } else {
                $project->title = $request->title;
                $project->image = $request->hasFile('image') ? store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH') : '';
                $project->meta_image = $request->hasFile('meta_image') ? store_image($request->file('meta_image'), 'PROJECT_SEO_IMG_PATH') : '';
                $title = $request->title;
                $project->slug_id = generateUniqueSlug($slugData, 4);
            }

            $project->save();

            if ($request->remove_gallery_images) {
                $remove_gallery_images = explode(',', $request->remove_gallery_images);
                foreach ($remove_gallery_images as $key => $value) {
                    $gallary_images = ProjectDocuments::find($value);
                    unlink_image($gallary_images->name);
                    $gallary_images->delete();
                }
            }

            if ($request->remove_documents) {
                $remove_documents = explode(',', $request->remove_documents);
                foreach ($remove_documents as $key => $value) {
                    $gallary_images = ProjectDocuments::find($value);
                    unlink_image($gallary_images->name);
                    $gallary_images->delete();
                }
            }

            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $gallary_image = new ProjectDocuments();
                    $gallary_image->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $gallary_image->project_id = $project->id;
                    $gallary_image->type = 'image';
                    $gallary_image->save();
                }
            }

            if ($request->hasfile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $project_documents = new ProjectDocuments();
                    $project_documents->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_documents->project_id = $project->id;
                    $project_documents->type = 'doc';
                    $project_documents->save();
                }
            }

            if ($request->plans) {
                foreach ($request->plans as $key => $plan) {
                    if (isset($plan['id']) && $plan['id'] != '') {
                        $project_plans =  ProjectPlans::find($plan['id']);
                    } else {
                        $project_plans = new ProjectPlans();
                    }
                    if (isset($plan['document'])) {
                        $project_plans->document = store_image($plan['document'], 'PROJECT_DOCUMENT_PATH');
                    }
                    $project_plans->title = $plan['title'];
                    $project_plans->project_id = $project->id;
                    $project_plans->save();
                }
            }


            if ($request->remove_plans) {
                $remove_plans = explode(',', $request->remove_plans);
                foreach ($remove_plans as $key => $value) {
                    $project_plans = ProjectPlans::find($value);
                    unlink_image($project_plans->document);
                    $project_plans->delete();
                }
            }
            $result = Projects::with('customer')->with('gallary_images')->with('documents')->with('plans')->with('category:id,category,image')->where('id', $project->id)->get();

            DB::commit();
            $response['error'] = false;
            $response['message'] = isset($request->id) ? 'Project Updated Successfully' : 'Project Post Succssfully';
            $response['data'] = $result;
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    public function delete_project(Request $request)
    {
        $current_user = Auth::user()->id;

        $validator = Validator::make($request->all(), [

            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        $project = Projects::where('added_by', $current_user)->with('gallary_images')->with('documents')->with('plans')->find($request->id);

        if ($project) {
            foreach ($project->gallary_images as $row) {
                if ($project->title_image != '') {
                    unlink_image($row->title_image);
                }
                $gallary_image = ProjectDocuments::find($row->id);
                if ($gallary_image) {
                    if ($row->name != '') {

                        unlink_image($row->name);
                    }
                }
            }

            foreach ($project->documents as $row) {

                $project_documents = ProjectDocuments::find($row->id);
                if ($project_documents) {
                    if ($row->name != '') {

                        unlink_image($row->name);
                    }
                    $project_documents->delete();
                }
            }
            foreach ($project->plans as $row) {

                $project_plans = ProjectPlans::find($row->id);
                if ($project_plans) {
                    if ($row->name != '') {

                        unlink_image($row->document);
                    }
                    $project_plans->delete();
                }
            }
            $project->delete();
            $response['error'] = false;
            $response['message'] =  'Project Delete Successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not found';
        }
        return response()->json($response);
    }

    public function getUserPersonalisedInterest(Request $request)
    {
        try {
            // Get Current User's ID From Token
            $loggedInUserId = Auth::user()->id;
            $data = array();

            // Get User Interest Data on the basis of current User
            $userInterest = UserInterest::where('user_id', $loggedInUserId)->first();
            if(collect($userInterest)->isNotEmpty()){
                // Get Data
                $categoriesIds = !empty($userInterest->category_ids) ? explode(',', $userInterest->category_ids) : '';
                $priceRange = $userInterest->property_type != null ? explode(',', $userInterest->price_range) : '';
                $propertyType = $userInterest->property_type == 0 || $userInterest->property_type == 1 ? explode(',', $userInterest->property_type) : '';
                $outdoorFacilitiesIds = !empty($userInterest->outdoor_facilitiy_ids) ? explode(',', $userInterest->outdoor_facilitiy_ids) : '';
                $city = !empty($userInterest->city) ?  $userInterest->city : '';
                // Custom Data Array
                $data = array(
                    'user_id'               => $loggedInUserId,
                    'category_ids'          => $categoriesIds,
                    'price_range'           => $priceRange,
                    'property_type'         => $propertyType,
                    'outdoor_facilitiy_ids' => $outdoorFacilitiesIds,
                    'city'                  => $city,
                );
            }
            $response = array(
                'error' => false,
                'data' => $data,
                'message' => 'Data fetched Successfully'
            );


            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function storeUserPersonalisedInterest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get Current User's ID From Token
            $loggedInUserId = Auth::user()->id;

            // Get User Interest
            $userInterest = UserInterest::where('user_id', $loggedInUserId)->first();

            // If data Exists then update or else insert new data
            if (collect($userInterest)->isNotEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data Updated Successfully";
            } else {
                $userInterest = new UserInterest();
                $response['error'] = false;
                $response['message'] = "Data Store Successfully";
            }

            // Change the values
            $userInterest->user_id = $loggedInUserId;
            $userInterest->category_ids = (isset($request->category_ids) && !empty($request->category_ids)) ? $request->category_ids : "";
            $userInterest->outdoor_facilitiy_ids = (isset($request->outdoor_facilitiy_ids) && !empty($request->outdoor_facilitiy_ids)) ? $request->outdoor_facilitiy_ids : null;
            $userInterest->price_range = (isset($request->price_range) && !empty($request->price_range)) ? $request->price_range : "";
            $userInterest->city = (isset($request->city) && !empty($request->city)) ? $request->city : "";
            $userInterest->property_type = isset($request->property_type) && ($request->property_type == 0 || $request->property_type == 1) ? $request->property_type : "0,1";
            $userInterest->save();

            DB::commit();

            // Get Datas
            $categoriesIds = !empty($userInterest->category_ids) ? explode(',', $userInterest->category_ids) : '';
            $priceRange = !empty($userInterest->price_range) ? explode(',', $userInterest->price_range) : '';
            $propertyType = explode(',', $userInterest->property_type);
            $outdoorFacilitiesIds = !empty($userInterest->outdoor_facilitiy_ids) ? explode(',', $userInterest->outdoor_facilitiy_ids) : '';
            $city = !empty($userInterest->city) ?  $userInterest->city : '';

            // Custom Data Array
            $data = array(
                'user_id'               => $userInterest->user_id,
                'category_ids'          => $categoriesIds,
                'price_range'           => $priceRange,
                'property_type'         => $propertyType,
                'outdoor_facilitiy_ids' => $outdoorFacilitiesIds,
                'city'                  => $city,
            );
            $response['data'] = $data;

            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function deleteUserPersonalisedInterest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get Current User From Token
            $loggedInUserId = Auth::user()->id;

            // Get User Interest
            UserInterest::where('user_id', $loggedInUserId)->delete();
            DB::commit();
            $response = array(
                'error' => false,
                'message' => 'Data Deleted Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function removeAllPackages(Request $request)
    {
        try {
            DB::beginTransaction();

            $loggedInUserId = Auth::user()->id;

            // Cannot directly delete the payment transaction and user package because it has foreign key constraint
            $paymentTransaction = PaymentTransaction::where('user_id',$loggedInUserId)->get();
            foreach($paymentTransaction as $transaction){
                $transaction->bank_receipt_files()->delete();
                $transaction->delete();
            }
            $userPackage = UserPackage::where('user_id',$loggedInUserId)->get();
            foreach($userPackage as $package){
                $package->delete();
            }

            DB::commit();
            $response = array(
                'error' => false,
                'message' => 'Data Deleted Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getAddedProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_type' => 'nullable|in:0,1,2,3',
            'is_promoted' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            // Get Logged In User data
            $loggedInUserData = Auth::user();
            // Get Current Logged In User ID
            $loggedInUserID = $loggedInUserData->id;

            // when is_promoted is passed then show only property who has been featured (advertised)
            if($request->has('is_promoted') && $request->is_promoted == 1){
                // Create Advertisement Query which has Property Data
                $advertisementQuery = Advertisement::whereHas('property',function($query) use($loggedInUserID){
                    $query->where(['post_type' => 1, 'added_by' => $loggedInUserID]);
                })->with('property:id,category_id,slug_id,title,propery_type,city,state,country,price,title_image','property.category:id,category,image');

                // Get Total Advertisement Data
                $advertisementTotal = $advertisementQuery->clone()->count();

                // Get Advertisement Data with custom Data
                $advertisementData = $advertisementQuery->clone()->skip($offset)->take($limit)->orderBy('id','DESC')->get()->map(function($advertisement){
                    if(collect($advertisement->property)->isNotEmpty()){
                        $otherData = array();
                        $otherData['id'] = $advertisement->property->id;
                        $otherData['slug_id'] = $advertisement->property->slug_id;
                        $otherData['property_type'] = $advertisement->property->propery_type;
                        $otherData['title'] = $advertisement->property->title;
                        $otherData['city'] = $advertisement->property->city;
                        $otherData['state'] = $advertisement->property->state;
                        $otherData['country'] = $advertisement->property->country;
                        $otherData['price'] = $advertisement->property->price;
                        $otherData['title_image'] = $advertisement->property->title_image;
                        $otherData['advertisement_id'] = $advertisement->id;
                        $otherData['advertisement_status'] = $advertisement->status;
                        $otherData['advertisement_type'] = $advertisement->type;
                        $otherData['category'] = $advertisement->property->category;
                        unset($advertisement); // remove the original data
                        return $otherData; // return custom created data
                    }
                });
                $response = array(
                    'error' => false,
                    'data' => $advertisementData,
                    'total' => $advertisementTotal,
                    'message' => 'Data fetched Successfully'
                );
            }else{
                // Check the property's post is done by customer and added by logged in user
                $propertyQuery = Property::where(['post_type' => 1, 'added_by' => $loggedInUserID])
                    // When property type is passed in payload show data according property type that is sell or rent
                    ->when($request->filled('property_type'), function ($query) use ($request) {
                        return $query->where('propery_type', $request->property_type);
                    })
                    ->when($request->filled('id'), function ($query) use ($request) {
                        return $query->where('id', $request->id);
                    })
                    ->when($request->filled('slug_id'), function ($query) use ($request) {
                        return $query->where('slug_id', $request->slug_id);
                    })
                    ->when($request->filled('status'), function ($query) use($request){
                        // IF Status is passed and status has active (1) or deactive (0) or both
                        $statusData = explode(',',$request->status);
                        return $query->whereIn('status', $statusData)->where('request_status','approved');
                    })
                    ->when($request->filled('request_status'), function ($query) use($request){
                        // IF Request Status is passed and status has approved or rejected or pending or all
                        $requestAccessData = explode(',',$request->request_status);
                        return $query->whereIn('request_status',$requestAccessData);
                    })

                    // Pass the Property Data with Category and Advertisement Relation Data
                    ->with('category', 'advertisement', 'interested_users:id,property_id,customer_id','interested_users.customer:id,name,profile');

                // Get Total Views by Sum of total click of each property
                $totalViews = $propertyQuery->sum('total_click');

                // Get total properties
                $totalProperties = $propertyQuery->count();

                // Get the property data with extra data and changes :- is_premium, post_created and promoted
                $propertyData = $propertyQuery->skip($offset)->take($limit)->orderBy('id','DESC')->get()->map(function ($property) use ($loggedInUserData) {
                    // Add lastest Reject reason when request status is rejected
                    $property->reject_reason = (object)array();
                    if($property->request_status == 'rejected'){
                        $property->reject_reason = $property->reject_reason()->latest()->first();
                    }
                    $property->is_premium = $property->is_premium == 1 ? true : false;
                    $property->property_type = $property->propery_type;
                    $property->post_created = $property->created_at->diffForHumans();
                    $property->promoted = $property->is_promoted;
                    $property->parameters = $property->parameters;
                    $property->assign_facilities = $property->assign_facilities;
                    $property->is_feature_available = $property->is_feature_available;

                    // Interested Users
                    $interestedUsers = $property->interested_users;
                    unset($property->interested_users);
                    $property->interested_users = $interestedUsers->map(function($interestedUser){
                        unset($property->id);
                        unset($property->property_id);
                        unset($property->customer_id);
                        return $interestedUser->customer;
                    });

                    // Add User's Details
                    $property->customer_name = $loggedInUserData->name;
                    $property->email = $loggedInUserData->email;
                    $property->mobile = $loggedInUserData->mobile;
                    $property->profile = $loggedInUserData->profile;
                    return $property;
                });

                $response = array(
                    'error' => false,
                    'data' => $propertyData,
                    'total' => $totalProperties,
                    'total_views' => $totalViews,
                    'message' => 'Data fetched Successfully'
                );

                if($request->has('id')){
                    $getSimilarPropertiesQueryData = Property::where(['post_type' => 1, 'added_by' => $loggedInUserID])->where('id', '!=', $request->id)->select('id', 'slug_id', 'category_id', 'title', 'added_by', 'address', 'city', 'country', 'state', 'propery_type', 'price', 'created_at', 'title_image')->orderBy('id', 'desc')->limit(10)->get();

                    $getSimilarProperties = get_property_details($getSimilarPropertiesQueryData, $loggedInUserData);
                }
                else if($request->has('slug_id')){
                    $getSimilarPropertiesQueryData = Property::where(['post_type' => 1, 'added_by' => $loggedInUserID])->where('slug_id', '!=', $request->slug_id)->select('id', 'slug_id', 'category_id', 'title', 'added_by', 'address', 'city', 'country', 'state', 'propery_type', 'price', 'created_at', 'title_image')->orderBy('id', 'desc')->limit(10)->get();
                    $getSimilarProperties = get_property_details($getSimilarPropertiesQueryData, $loggedInUserData);
                }
                else{
                    $getSimilarProperties = array();
                }
                if($getSimilarProperties){
                    $response['similiar_properties'] = $getSimilarProperties;
                }
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    /**
     * Homepage Data API
     * Params :- None
     */
    public function homepageData(Request $request)
    {
        try {
            $latitude = $request->has('latitude') ? $request->latitude : null;
            $longitude = $request->has('longitude') ? $request->longitude : null;
            $radius = $request->has('radius') ? $request->radius : null;
            $homepageLocationDataAvailable = false;

            // Create reusable property mapper function
            $propertyMapper = function($propertyData) {
                $propertyData->promoted = $propertyData->is_promoted;
                $propertyData->property_type = $propertyData->propery_type;
                $propertyData->parameters = $propertyData->parameters;
                $propertyData->is_premium = $propertyData->is_premium == 1;
                return $propertyData;
            };

            // Base property query that will be reused
            $propertyBaseQuery = Property::select(
                'id', 'slug_id', 'category_id', 'city', 'state', 'country',
                'price', 'propery_type', 'title', 'title_image', 'is_premium',
                'address', 'rentduration', 'latitude', 'longitude'
            )
            ->with('category:id,slug_id,image,category')
            ->where(['status' => 1, 'request_status' => 'approved'])
            ->whereIn('propery_type', [0, 1]);
            if($latitude && $longitude){
                if($radius){
                    // Create a query with Haversine formula
                    $latlongBasedQuery = $propertyBaseQuery->clone()
                            ->selectRaw("
                            (6371 * acos(cos(radians($latitude))
                            * cos(radians(latitude))
                            * cos(radians(longitude) - radians($longitude))
                            + sin(radians($latitude))
                            * sin(radians(latitude)))) AS distance")
                        ->where('latitude', '!=', 0)
                        ->where('longitude', '!=', 0)
                        ->having('distance', '<', $radius);
                }else{
                    $latlongBasedQuery = $propertyBaseQuery->clone()->where(['latitude' => $latitude, 'longitude' => $longitude]);
                }

                $count = $latlongBasedQuery->clone()->count();
                if($count > 0){
                    $homepageLocationDataAvailable = true;
                    $locationBasedPropertyQuery = $latlongBasedQuery;
                }else{
                    $locationBasedPropertyQuery = $propertyBaseQuery;
                    $homepageLocationDataAvailable = false;
                }
            }else{
                $locationBasedPropertyQuery = $propertyBaseQuery;
                $homepageLocationDataAvailable = false;
            }

            // Base projects query that will be reused
            $projectsBaseQuery = Projects::select(
                'id', 'slug_id', 'city', 'state', 'country', 'title',
                'type', 'image', 'location', 'category_id', 'added_by', 'latitude', 'longitude'
            )
            ->where(['request_status' => 'approved', 'status' => 1])
            ->with([
                'category:id,slug_id,image,category',
                'gallary_images:id,project_id,name',
                'customer:id,name,profile,email,mobile'
            ]);
            if($latitude && $longitude){
                $latlongBasedQuery = $projectsBaseQuery->clone()->where(['latitude' => $latitude, 'longitude' => $longitude]);
                $count = $latlongBasedQuery->clone()->count();
                if($count > 0 || $homepageLocationDataAvailable == true){
                    $homepageLocationDataAvailable = true;
                    $projectsBaseQuery = $latlongBasedQuery;
                }else{
                    $projectsBaseQuery = $projectsBaseQuery;
                    $homepageLocationDataAvailable = false;
                }
            }else{
                $count = $projectsBaseQuery->clone()->count();
                $homepageLocationDataAvailable = false;
            }

            // Get homepage sections
            $sections = $this->getHomepageSections($latitude, $longitude, $propertyBaseQuery, $propertyMapper, $projectsBaseQuery, $homepageLocationDataAvailable, $locationBasedPropertyQuery);

            // Add slider data (not controlled by homepage sections)
            $slidersData = Slider::select('id', 'type', 'image', 'web_image', 'category_id', 'propertys_id', 'show_property_details', 'link')
                ->with([
                    'category' => function($query) {
                        $query->where('status', 1)->select('id', 'slug_id', 'category');
                    },
                    'property' => function($query) {
                        $query->whereIn('propery_type', [0, 1])
                            ->where(['status' => 1, 'request_status' => 'approved'])
                            ->select('id', 'slug_id', 'title', 'title_image', 'price', 'propery_type');
                    }
                ])
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($slider) {
                    $slider->slider_type = $slider->getRawOriginal('type');

                    // Filter out entries that don't meet our criteria
                    if ($slider->getRawOriginal('type') == 2 && empty($slider->category)) {
                        return null;
                    }

                    if ($slider->getRawOriginal('type') == 3) {
                        if (empty($slider->property)) {
                            return null;
                        }
                        $slider->property->parameters = $slider->property->parameters;
                    }

                    return $slider;
                })
                ->filter()
                ->values();

            $data = [
                'sections' => $sections,
                'slider_section' => $slidersData,
                'homepage_location_data_available' => $homepageLocationDataAvailable == true ? true : false,
            ];
            ApiResponseService::successResponse("Data fetched Successfully", $data);
        } catch (Exception $e) {
            ApiResponseService::errorResponse("Something Went Wrong");
        }
    }

    /**
     * Agent List API
     * Params :- limit and offset
     */
    public function getAgentList(Request $request)
    {
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;

            // if there is limit in request then have to do less by one so that to manage total data count with admin
            $limit = isset($request->limit) && !empty($request->limit) ? ($request->limit - 1) : 10;

            $latitude = $request->has('latitude') ? $request->latitude : null;
            $longitude = $request->has('longitude') ? $request->longitude : null;


            if(!empty($request->limit)){
                $agentsListQuery = Customer::select('id','name','email','profile','slug_id')->where(function($query) {
                    $query->where('isActive', 1);
                })
                ->where(function($query) use($latitude, $longitude) {
                    $query->whereHas('projects', function ($query) use($latitude, $longitude) {
                        $query->where('status', 1)->when($latitude && $longitude, function($query) use($latitude, $longitude){
                            $query->where('latitude', $latitude)->where('longitude', $longitude);
                        });
                    })->orWhereHas('property', function ($query) use($latitude, $longitude) {
                        $query->where(['status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                            $query->where('latitude', $latitude)->where('longitude', $longitude);
                        });
                    });
                })
                ->withCount([
                    'projects' => function ($query) use($latitude, $longitude) {
                        $query->where('status', 1)->when($latitude && $longitude, function($query) use($latitude, $longitude){
                            $query->where('latitude', $latitude)->where('longitude', $longitude);
                        });
                    },
                    'property' => function ($query) use($latitude, $longitude) {
                        $query->where(['status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                            $query->where('latitude', $latitude)->where('longitude', $longitude);
                        });
                    }
                ]);

                $agentListCount = $agentsListQuery->clone()->count();

                $agentListData = $agentsListQuery->clone()
                    ->get()
                    ->map(function ($customer) {
                        $customer->is_verified = $customer->is_user_verified;
                        $customer->total_count = $customer->projects_count + $customer->property_count;
                        $customer->is_admin = false;
                        return $customer;
                    })
                    ->filter(function ($customer) {
                        return $customer->projects_count > 0 || $customer->property_count > 0;
                    })
                    ->sortByDesc(function ($customer) {
                        return [$customer->is_verified, $customer->total_count];
                    })
                    ->skip($offset)
                    ->take($limit)
                    ->values(); // This line resets the array keys




                // Get admin List

                $adminEmail = system_setting('company_email');
                $adminData = array();
                $adminPropertiesCount = Property::where(['added_by' => 0, 'status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                    $query->where('latitude', $latitude)->where('longitude', $longitude);
                })->count();
                $adminProjectsCount = Projects::where(['is_admin_listing' => 1, 'status' => 1, 'request_status' => 'approved'])->when($latitude && $longitude, function($query) use($latitude, $longitude){
                    $query->where('latitude', $latitude)->where('longitude', $longitude);
                })->count();
                $totalCount = $adminPropertiesCount + $adminProjectsCount;

                $adminData = User::where('type',0)->select('id','name','profile')->first();

                $adminQuery = User::where('type',0)->select('id','slug_id')->first();
                if($adminQuery && ($adminPropertiesCount > 0 || $adminProjectsCount > 0)){
                    $adminData = array(
                        'id' => $adminQuery->id,
                        'name' => 'Admin',
                        'slug_id' => $adminQuery->slug_id,
                        'email' => !empty($adminEmail) ? $adminEmail : "",
                        'property_count' => $adminPropertiesCount,
                        'projects_count' => $adminProjectsCount,
                        'total_count' => $totalCount,
                        'is_verified' => true,
                        'profile' => !empty($adminData->getRawOriginal('profile')) ? $adminData->profile : url('assets/images/faces/2.jpg'),
                        'is_admin' => true
                    );
                    if($offset == 0){
                        $agentListData->prepend((object) $adminData);
                    }
                }

            }
            $response = array(
                'error' => false,
                'total' => $agentListCount ?? 0,
                'data' => $agentListData ?? array(),
                'message' => 'Data fetched Successfully'
            );

            return response()->json($response);

        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * Agent Properties API
     * Params :- id or slug_id, limit, offset and is_project
     */
    public function getAgentProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug_id' => 'required_without_all:id,is_admin',
            'is_projects' => 'nullable|in:1',
            'is_admin' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $response = array(
                'package_available' => false,
                'feature_available' => false,
                'limit_available' => false,
            );
            // Get Limit Status of premium properties feature
            if(Auth::guard('sanctum')){
                $response = HelperService::checkPackageLimit('premium_properties',true);
            }
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;
            $isAdminListing = false;

            if($request->has('is_admin') && $request->is_admin == 1){
                $addedBy = 0;
                $isAdminListing = true;
                $adminEmail = system_setting('company_email');
                $adminCompanyTel1 = system_setting('company_tel1');
                $customerData = array();
                $adminPropertiesCount = Property::where(['added_by' => 0,'status' => 1, 'request_status' => 'approved'])->count();
                $adminProjectsCount = Projects::where(['is_admin_listing' => 1,'status' => 1])->count();
                $totalCount = $adminPropertiesCount + $adminProjectsCount;

                $adminData = User::where('type',0)->select('id','name','profile')->first();

                $adminQuery = User::where('type',0)->select('id','slug_id')->first();
                if($adminQuery){
                    $customerData = array(
                        'id' => $adminQuery->id,
                        'name' => 'Admin',
                        'slug_id' => $adminQuery->slug_id,
                        'email' => !empty($adminEmail) ? $adminEmail : "",
                        'mobile' => !empty($adminCompanyTel1) ? $adminCompanyTel1 : "",
                        'property_count' => $adminPropertiesCount,
                        'projects_count' => $adminProjectsCount,
                        'total_count' => $totalCount,
                        'is_verify' => true,
                        'profile' => !empty($adminData->getRawOriginal('profile')) ? $adminData->profile : url('assets/images/faces/2.jpg')
                    );
                }
            }else{
                // Customer Query
                $customerQuery = Customer::select('id','slug_id','name','profile','mobile','email','address','city','country','state','facebook_id','twiiter_id as twitter_id','youtube_id','instagram_id','about_me', 'latitude', 'longitude')->where(function($query){
                    $query->where('isActive', 1);
                })->withCount(['projects' => function($query){
                    $query->where('status',1);
                }, 'property' => function($query) use($response){
                    if($response['package_available'] == true && $response['feature_available'] == true){
                        $query->where(['status' => 1, 'request_status' => 'approved']);
                    }else{
                        $query->where(['status' => 1, 'request_status' => 'approved', 'is_premium' => 0]);
                    }
                }]);
                // Check if id exists or slug id on the basis of get agent id
                if($request->has('id') && !empty($request->id)){
                    $addedBy = $request->id;
                    // Get Customer Data
                    $customerData = $customerQuery->clone()->where('id',$request->id)->first();
                    $addedBy = !empty($customerData) ? $customerData->id : "";
                }else if($request->has('slug_id')){
                    // Get Customer Data
                    $customerData = $customerQuery->clone()->where('slug_id',$request->slug_id)->first();
                    $addedBy = !empty($customerData) ? $customerData->id : "";
                }
                // Add Is User Verified Status in Customer Data
                !empty($customerData) ? $customerData->is_verify = $customerData->is_user_verified : "";
            }

            // if there is agent id then only get properties of it
            if(!empty($addedBy) || $addedBy == 0){

                if(($request->has('is_projects') && !empty($request->is_projects) && $request->is_projects == 1)){
                    $response = HelperService::checkPackageLimit('project_access',true);
                    $projectQuery = Projects::select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location','category_id','added_by');
                    if($isAdminListing == true){
                        $projectQuery = $projectQuery->clone()->where(['status' => 1, 'is_admin_listing' => 1]);
                    }else{
                        $projectQuery = $projectQuery->clone()->where(['status' => 1, 'request_status' => 'approved','added_by' => $addedBy]);
                    }
                    $totalProjects = $projectQuery->clone()->count();
                    $totalData = $totalProjects;
                    if($response['package_available'] == true && $response['feature_available'] == true){
                        $projectData = $projectQuery->clone()->with('gallary_images','category:id,slug_id,image,category')->skip($offset)->take($limit)->get();
                    }
                }else{
                    // Create a proeprty query
                    $propertiesQuery = Property::select('id', 'slug_id', 'city', 'state', 'category_id','country', 'price', 'propery_type', 'title', 'title_image', 'is_premium', 'address', 'added_by')
                        ->where(['status' => 1, 'request_status' => 'approved', 'added_by' => $addedBy]);

                    // Count total properties
                    $totalProperties = $propertiesQuery->clone()
                        ->when(($response['feature_available'] == false), function($query){
                            $query->where('is_premium', 0);
                        })->count();

                    // Count premium properties without the condition
                    $premiumPropertiesCount = Property::where(['status' => 1, 'request_status' => 'approved', 'added_by' => $addedBy, 'is_premium' => 1])->count();

                    // Get Propertis Data
                    $propertiesData = $propertiesQuery->clone()
                        ->when(($response['feature_available'] == false), function($query){
                            $query->where('is_premium', 0);
                        })
                        ->with('category:id,slug_id,image,category')
                        ->orderBy('is_premium', 'DESC')->skip($offset)->take($limit)->get()->map(function($property){
                            $property->property_type = $property->propery_type;
                            $property->parameters = $property->parameters;
                            $property->promoted = $property->is_promoted;
                            unset($property->propery_type);
                            return $property;
                        });
                    $totalData = $totalProperties;
                }
            }

            $response = array(
                'error'                     => false,
                'total'                     => $totalData ?? 0,
                'data' => array(
                    'customer_data'             => $customerData ?? array(),
                    'properties_data'           => $propertiesData ?? array(),
                    'projects_data'             => $projectData ?? array(),
                    'premium_properties_count'  => $premiumPropertiesCount ?? 0,
                    'package_available'         => $response['package_available'],
                    'feature_available'         => $response['feature_available'],

                ),
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getWebSettings(Request $request){
        try{
            // Types for web requirement only
            $types = array('company_name', 'currency_symbol', 'default_language', 'number_with_suffix', 'web_maintenance_mode', 'company_tel', 'company_tel2', 'system_version','web_favicon', 'web_logo', 'web_footer_logo', 'web_placeholder_logo', 'company_email', 'latitude', 'longitude', 'company_address', 'system_color', 'svg_clr', 'iframe_link', 'facebook_id', 'instagram_id', 'twitter_id', 'youtube_id', 'playstore_id', 'sell_background', 'appstore_id', 'category_background', 'web_maintenance_mod','seo_settings','company_tel1','place_api_key','stripe_publishable_key','paystack_public_key','sell_web_color','sell_web_background_color','rent_web_color','rent_web_background_color','about_us','terms_conditions','privacy_policy','number_with_otp_login','social_login','distance_option','otp_service_provider','text_property_submission','auto_approve', 'verification_required_for_user','allow_cookies', 'currency_code', 'bank_details','schema_for_deeplink','min_radius_range','max_radius_range');

            // Query the Types to Settings Table to get its data
            $result =  Setting::select('type', 'data')->whereIn('type',$types)->get();

            // Check the result data is not empty
            if(collect($result)->isNotEmpty()){
                $settingsData = array();

                // Loop on the result data
                foreach ($result as $row) {
                    // Change data according to conditions
                    if ($row->type == 'company_logo') {
                        // Add logo image with its url
                        $settingsData[$row->type] = url('/assets/images/logo/logo.png');
                    } else if ($row->type == 'seo_settings') {
                        // Change Value to Bool
                        $settingsData[$row->type] = $row->data == 1 ? true : false;
                    } else if ($row->type == 'allow_cookies') {
                        // Change Value to Bool
                        $settingsData[$row->type] = $row->data == 1 ? true : false;
                    } else if ($row->type == 'verification_required_for_user') {
                        // Change Value to Bool
                        $settingsData[$row->type] = $row->data == 1 ? true : false;
                    } else if ($row->type == 'web_favicon' || $row->type == 'web_logo' || $row->type == 'web_placeholder_logo' || $row->type == 'web_footer_logo') {
                        // Add Full URL to the specified type
                        $settingsData[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
                    } else if ($row->type == 'place_api_key') {
                        // Add Full URL to the specified type
                        $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                        $encryptedData = '';
                        if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {
                            $settingsData[$row->type] = base64_encode($encryptedData);
                        }else{
                            $settingsData[$row->type] = "";
                        }
                    } else if ($row->type == 'currency_code') {
                        // Change Value to Bool
                        $settingsData['selected_currency_data'] = HelperService::getCurrencyData($row->data);
                    } else if ($row->type == 'bank_details') {
                        // Change Value to Bool
                        $settingsData['bank_details'] = json_decode($row->data, true);
                    } else{
                        // add the data as it is in array
                        $settingsData[$row->type] = $row->data;
                    }
                }

                $user_data = User::find(1);
                $settingsData['admin_name'] = $user_data->name;
                $settingsData['admin_image'] = url('/assets/images/faces/2.jpg');
                $settingsData['demo_mode'] = env('DEMO_MODE');
                $settingsData['img_placeholder'] = url('/assets/images/placeholder.svg');

                // if Token is passed of current user.
                if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
                    $loggedInUserId = Auth::guard('sanctum')->user()->id;
                    update_subscription($loggedInUserId);

                    $checkVerifiedStatus = VerifyCustomer::where('user_id', $loggedInUserId)->first();
                    if(!empty($checkVerifiedStatus)){
                        $settingsData['verification_status'] = $checkVerifiedStatus->status;
                    }else{
                        $settingsData['verification_status'] = 'initial';
                    }

                    $customerDataQuery = Customer::select('id', 'subscription', 'is_premium', 'isActive');
                    $customerData = $customerDataQuery->clone()->find($loggedInUserId);

                    // Check Active of current User
                    if (collect($customerData)->isNotEmpty()){
                        $settingsData['is_active'] = $customerData->isActive == 1 ? true : false;
                    } else {
                        $settingsData['is_active'] = false;
                    }

                    // Check the subscription
                    if (collect($customerData)->isNotEmpty()) {
                        $settingsData['is_premium'] = $customerData->is_premium == 1 ? true : ($customerData->subscription == 1 ? true : false);
                        $settingsData['subscription'] = $customerData->subscription == 1 ? true : false;
                    } else {
                        $settingsData['is_premium'] = false;
                        $settingsData['subscription'] = false;
                    }

                }


                // Check the min_price and max_price
                $settingsData['min_price'] = DB::table('propertys')->selectRaw('MIN(price) as min_price')->value('min_price');
                $settingsData['max_price'] = DB::table('propertys')->selectRaw('MAX(price) as max_price')->value('max_price');

                // Check the features available
                $settingsData['features_available'] = array(
                    'premium_properties' => HelperService::checkPackageLimit('premium_properties',true)['feature_available'],
                    'project_access' => HelperService::checkPackageLimit('project_access',true)['feature_available'],
                );

                // Get Languages Data
                $language = Language::select('id', 'code', 'name')->get();
                $settingsData['languages'] = $language;

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $settingsData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getAppSettings(Request $request){
        try{
          $types = array('company_name', 'currency_symbol', 'ios_version', 'default_language', 'force_update', 'android_version', 'number_with_suffix', 'maintenance_mode', 'company_tel1', 'company_tel2', 'company_email', 'company_address', 'place_api_key', 'svg_clr', 'playstore_id', 'sell_background', 'appstore_id', 'show_admob_ads', 'android_banner_ad_id', 'ios_banner_ad_id', 'android_interstitial_ad_id', 'ios_interstitial_ad_id', 'android_native_ad_id', 'ios_native_ad_id', 'demo_mode', 'min_price', 'max_price','privacy_policy', 'terms_conditions','about_us','number_with_otp_login','social_login','distance_option','otp_service_provider','app_home_screen','placeholder_logo','light_tertiary','light_secondary','light_primary','dark_tertiary','dark_secondary','dark_primary','text_property_submission','auto_approve', 'verification_required_for_user', 'currency_code', 'bank_details','schema_for_deeplink','min_radius_range','max_radius_range','latitude','longitude');

            // Query the Types to Settings Table to get its data
            $result =  Setting::select('type', 'data')->whereIn('type',$types)->get();

            // Check the result data is not empty
            if(collect($result)->isNotEmpty()){
                $settingsData = array();

                // Loop on the result data
                foreach ($result as $row) {
                    if ($row->type == "place_api_key") {
                        $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                        $encryptedData = '';
                        if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {
                            $settingsData[$row->type] = base64_encode($encryptedData);
                        }
                    } else if ($row->type == 'default_language'){
                        // Add Code in Data
                        $settingsData[$row->type] = $row->data;

                        // Add Default language's name
                        $languageData = Language::where('code',$row->data)->first();
                        if(collect($languageData)->isNotEmpty()){
                            $settingsData['default_language_name'] = $languageData->name;
                            $settingsData['default_language_rtl'] = $languageData->rtl == 1 ? 1 : 0;
                        }else{
                            $settingsData['default_language_name'] = "";
                            $settingsData['default_language_rtl'] = 0;
                        }
                    } else if ($row->type == 'app_home_screen' || $row->type == "placeholder_logo") {
                        $settingsData[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
                    } else if ($row->type == 'verification_required_for_user') {
                        // Change Value to Bool
                        $settingsData[$row->type] = $row->data == 1 ? true : false;
                    } else if ($row->type == 'currency_code') {
                        // Change Value to Bool
                        $settingsData['selected_currency_data'] = HelperService::getCurrencyData($row->data);
                    } else if ($row->type == 'bank_details') {
                        // Change Value to Bool
                        $settingsData['bank_details'] = json_decode($row->data, true);
                    } else{
                        // add the data as it is in array
                        $settingsData[$row->type] = $row->data;
                    }
                }

                $settingsData['demo_mode'] = env('DEMO_MODE');
                // if Token is passed of current user.
                if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
                    $loggedInUserId = Auth::guard('sanctum')->user()->id;
                    update_subscription($loggedInUserId);


                    $checkVerifiedStatus = VerifyCustomer::where('user_id', $loggedInUserId)->first();
                    if(!empty($checkVerifiedStatus)){
                        $settingsData['verification_status'] = $checkVerifiedStatus->status;
                    }else{
                        $settingsData['verification_status'] = 'initial';
                    }

                    $customerDataQuery = Customer::select('id', 'subscription', 'is_premium', 'isActive');
                    $customerData = $customerDataQuery->clone()->find($loggedInUserId);

                    // Check Active of current User
                    if (collect($customerData)->isNotEmpty()){
                        $settingsData['is_active'] = $customerData->isActive == 1 ? true : false;
                    } else {
                        $settingsData['is_active'] = false;
                    }

                    // Check the subscription
                    if (collect($customerData)->isNotEmpty()) {
                        $settingsData['is_premium'] = $customerData->is_premium == 1 ? true : ($customerData->subscription == 1 ? true : false);
                        $settingsData['subscription'] = $customerData->subscription == 1 ? true : false;
                    } else {
                        $settingsData['is_premium'] = false;
                        $settingsData['subscription'] = false;
                    }

                }

                // Check the min_price and max_price
                $settingsData['min_price'] = DB::table('propertys')->selectRaw('MIN(price) as min_price')->value('min_price');
                $settingsData['max_price'] = DB::table('propertys')->selectRaw('MAX(price) as max_price')->value('max_price');

                // Check the features available
                $settingsData['features_available'] = array(
                    'premium_properties' => HelperService::checkPackageLimit('premium_properties',true)['feature_available'],
                    'project_access' => HelperService::checkPackageLimit('project_access',true)['feature_available'],
                );

                // Get Languages Data
                $language = Language::select('id', 'code', 'name')->get();
                $settingsData['languages'] = $language;

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $settingsData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function getLanguagesData(){
        try {
            $languageData = Language::select('id', 'code', 'name')->get();
            if(collect($languageData)->isNotEmpty()){
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $languageData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * Faq API
     * Params :- Limit and offset
     */
    public function getFaqData(Request $request)
    {
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            $faqsQuery = Faq::where('status',1);
            $totalData = $faqsQuery->clone()->count();
            $faqsData = $faqsQuery->clone()->select('id','question','answer')->orderBy('id','DESC')->skip($offset)->take($limit)->get();
            $response = array(
                'error' => false,
                'total' => $totalData ?? 0,
                'data' => $faqsData,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * beforeLogout API
     */
    public function beforeLogout(Request $request){
        try {
            if($request->has('fcm_id')){
                Usertokens::where(['fcm_id' => $request->fcm_id, 'customer_id' => $request->user()->id])->delete();
            }
            $response = array(
                'error' => false,
                'message' => 'Data Processed Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function getOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'number' => 'required_without:email|nullable',
            'email' => 'required_without:number|email|nullable|exists:customers,email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $otpRecordDB = NumberOtp::query();
            if($request->has('number') && !empty($request->number)){
                $requestNumber = $request->number; // Get data from Request
                $trimmedNumber = ltrim($requestNumber,'+'); // remove + from starting if exists
                $toNumber = "+".(string)$trimmedNumber; // Add + starting of number

                // Initialize empty array
                $dbData = array();

                // make an array of types for database query and get data from settings table
                $twilioCredentialsTypes = array('twilio_account_sid','twilio_auth_token','twilio_my_phone_number');
                $twilioCredentialsDB = Setting::select('type','data')->whereIn('type',$twilioCredentialsTypes)->get();

                // Loop the db result in such a way that type becomes key of array and data becomes its value in new array
                foreach ($twilioCredentialsDB as $value) {
                    $dbData[$value->type] = $value->data;
                }

                // Get Twilio credentials
                $sid = $dbData['twilio_account_sid'];
                $token = $dbData['twilio_auth_token'];
                $fromNumber = $dbData['twilio_my_phone_number'];

                // Instance Created of Twilio client with Twilio SID and token
                $client = new TwilioRestClient($sid, $token);

                // Validate phone number using Twilio Lookup API
                try {
                    $client->lookups->v1->phoneNumbers($toNumber)->fetch();
                } catch (RestException $e) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Invalid phone number.',
                    ]);
                }
                // Check if OTP already exists and is still valid
                $existingOtp = $otpRecordDB->clone()->where('number', $toNumber)->first();

            }else if ($request->has('email') && !empty($request->email)){
                $toEmail = $request->email;
                // Check if OTP already exists and is still valid
                $existingOtp = $otpRecordDB->clone()->where('email', $toEmail)->first();
            }else{
                ApiResponseService::errorResponse();
            }

            if ($existingOtp && now()->isBefore($existingOtp->expire_at)) {
                // OTP is still valid
                $otp = $existingOtp->otp;
            } else {
                // Generate a new OTP
                $otp = rand(123456, 999999);
                $expireAt = now()->addMinutes(10); // Set OTP expiry time

                if ($request->has('number') && !empty($request->number)){
                    // Update or create OTP entry in the database
                    NumberOtp::updateOrCreate(
                        ['number' => $toNumber],
                        ['otp' => $otp, 'expire_at' => $expireAt]
                    );

                    // Use the Client to make requests to the Twilio REST API
                    $client->messages->create(
                        // The number you'd like to send the message to
                        $toNumber,
                        [
                            // A Twilio phone number you purchased at https://console.twilio.com
                            'from' => $fromNumber,
                            // The body of the text message you'd like to send
                            'body' => "Here is the OTP: ".$otp.". It expires in 3 minutes."
                        ]
                    );
                    /** Note :- While using Trial accounts cannot send messages to unverified numbers, or purchase a Twilio number to send messages to unverified numbers.*/
                }else if ($request->has('email') && !empty($request->email)){
                    // Update or create OTP entry in the database
                    NumberOtp::updateOrCreate(
                        ['email' => $toEmail],
                        ['otp' => $otp, 'expire_at' => $expireAt]
                    );
                }else{
                    ApiResponseService::errorResponse();
                }
            }


            if($request->has('email') && !empty($request->email)){
                try {
                    // Get Data of email type
                    $emailTypeData = HelperService::getEmailTemplatesTypes("verify_mail");

                    // Email Template
                    $verifyEmailTemplateData = system_setting("verify_mail_template");
                    $variables = array(
                        'app_name' => env("APP_NAME") ?? "eBroker",
                        'otp' => $otp
                    );
                    if(empty($verifyEmailTemplateData)){
                        $verifyEmailTemplateData = "Your OTP is :- $otp";
                    }
                    $verifyEmailTemplate = HelperService::replaceEmailVariables($verifyEmailTemplateData,$variables);

                    $data = array(
                        'email_template' => $verifyEmailTemplate,
                        'email' => $toEmail,
                        'title' => $emailTypeData['title'],
                    );

                    HelperService::sendMail($data);
                } catch (Exception $e) {
                    if (Str::contains($e->getMessage(), [
                        'Failed',
                        'Mail',
                        'Mailer',
                        'MailManager',
                        "Connection could not be established"
                    ])) {
                        ApiResponseService::validationError("There is issue with mail configuration, kindly contact admin regarding this");
                    } else {
                        ApiResponseService::errorResponse();
                    }
                }
            }
            // Return success response
            return response()->json([
                'error' => false,
                'message' => 'OTP sent successfully!',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'number' => 'required_without:email|nullable',
            'email' => 'required_without:number|nullable',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $otpRecordDB = NumberOtp::query();
            if($request->has('number') && !empty($request->number)){
                $requestNumber = $request->number; // Get data from Request
                $trimmedNumber = ltrim($requestNumber,'+'); // remove + from starting if exists
                $toNumber = "+".(string)$trimmedNumber; // Add + starting of number

                // Fetch the OTP record from the database
                $otpRecord = $otpRecordDB->clone()->where('number',$toNumber)->first();
            }else if ($request->has('email') && !empty($request->email)){
                $toEmail = $request->email;
                // Fetch the OTP record from the database
                $otpRecord = $otpRecordDB->clone()->where('email',$toEmail)->first();
            }else{
                ApiResponseService::errorResponse();
            }
            $userOtp = $request->otp;

            if (!$otpRecord) {
                return response()->json([
                    'error' => true,
                    'message' => 'OTP not found.',
                ]);
            }

            // Check if the OTP is valid and not expired
            if ($otpRecord->otp == $userOtp && now()->isBefore($otpRecord->expire_at)) {

                if($request->has('number') && !empty($request->number)){
                    // Check the number and login type exists in user table
                    $user = Customer::where('mobile', $trimmedNumber)->where('logintype',1)->first();
                } else if ($request->has('email') && !empty($request->email)){
                    // Check the email and login type exists in user table
                    $user = Customer::where('email', $toEmail)->where('logintype',3)->first();
                }else{
                    ApiResponseService::errorResponse();
                }

                if(collect($user)->isNotEmpty()){
                    $authId = $user->auth_id;
                }else{
                    // Generate a unique identifier
                    $authId = Str::uuid()->toString();
                }
                if ($request->has('email') && !empty($request->email)){
                    // Check the email and login type exists in user table
                    $user->is_email_verified = true;
                    $user->save();
                }

                return response()->json([
                    'error' => false,
                    'message' => 'OTP verified successfully!',
                    'auth_id' => $authId
                ]);
            } else if ($otpRecord->otp != $userOtp){
                ApiResponseService::validationError("Invalid OTP.");
            } else if (now()->isAfter($otpRecord->expire_at)){
                ApiResponseService::validationError("OTP expired.");
            } else{
                ApiResponseService::errorResponse();
            }

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getPropertyList(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'offset'                        => 'nullable|numeric',
                'limit'                         => 'nullable|numeric',
                'get_all_premium_properties'    => 'nullable|in:1',
            ]);
            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }

            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            // Create a property query
            $propertyQuery = Property::whereIn('propery_type',[0,1])->where(function($query){
                return $query->where(['status' => 1, 'request_status' => 'approved']);
            });

            // If Property Type Passed
            $property_type = $request->property_type;  //0 : Sell 1:Rent
            if (isset($property_type) && (!empty($property_type) || $property_type == 0)) {
                $propertyQuery = $propertyQuery->clone()->where('propery_type', $property_type);
            }

            // If Category Id is Passed
            if ($request->has('category_id') && !empty($request->category_id)) {
                $propertyQuery = $propertyQuery->clone()->where('category_id', $request->category_id);
            }

            // If parameter id passed
            if ($request->has('parameter_id') && !empty($request->parameter_id)) {
                $parametersId = explode(",",$request->parameter_id);
                $propertyQuery = $propertyQuery->clone()->whereHas('assignParameter',function($query) use($parametersId){
                    $query->whereIn('parameter_id',$parametersId)->whereNotNull('value');
                });
            }

            // If Category Slug is Passed
            if ($request->has('category_slug_id') && !empty($request->category_slug_id)) {
                $categorySlugId = $request->category_slug_id;
                $propertyQuery = $propertyQuery->clone()->whereHas('category',function($query)use($categorySlugId){
                    $query->where('slug_id',$categorySlugId);
                });
            }

            // If Country is passed
            if ($request->has('country') && !empty($request->country)) {
                $propertyQuery = $propertyQuery->clone()->where('country', $request->country);
            }

            // If State is passed
            if ($request->has('state') && !empty($request->state)) {
                $propertyQuery = $propertyQuery->clone()->where('state', $request->state);
            }

            // If City is passed
            if ($request->has('city') && !empty($request->city)) {
                $propertyQuery = $propertyQuery->clone()->where('city', $request->city);
            }

            // If Max Price And Min Price passed
            if ($request->has('min_price') && !empty($request->min_price)) {
                $minPrice = $request->min_price;
                $propertyQuery = $propertyQuery->clone()->where('price','>=',$minPrice);
            }

            if (isset($request->max_price) && !empty($request->max_price)) {
                $maxPrice = $request->max_price;
                $propertyQuery = $propertyQuery->clone()->where('price','<=',$maxPrice);
            }

            // If Posted Since 0 or 1 is passed
            if ($request->has('posted_since')) {
                $posted_since = $request->posted_since;

                // 0 - Last Week (from today back to the same day last week)
                if ($posted_since == 0) {
                    $oneWeekAgo = Carbon::now()->subWeek()->startOfDay();
                    $today = Carbon::now()->endOfDay();
                    $propertyQuery = $propertyQuery->clone()->whereBetween('created_at', [$oneWeekAgo, $today]);
                }
                // 1 - Yesterday
                if ($posted_since == 1) {
                    $yesterdayDate = Carbon::yesterday();
                    $propertyQuery =  $propertyQuery->clone()->whereDate('created_at', $yesterdayDate);
                }
            }

            // Search the property
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $propertyQuery = $propertyQuery->clone()->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%$search%")
                        ->orWhere('address', 'LIKE', "%$search%")
                        ->orWhereHas('category', function ($query1) use ($search) {
                            $query1->where('category', 'LIKE', "%$search%");
                        });
                });
            }

            // IF Promoted Passed then show the data according to
            if ($request->has('promoted') && $request->promoted == 1) {
                $propertyQuery = $propertyQuery->clone()->whereHas('advertisement',function($query){
                    $query->where(['status' => 0, 'is_enable' => 1]);
                });
            }

            // If get_all_premium_properties is passed then show the data according to
            if ($request->has('get_all_premium_properties') && $request->get_all_premium_properties == 1) {
                $propertyQuery = $propertyQuery->clone()->where('is_premium',1);
            }

            // Get total properties
            $totalProperties = $propertyQuery->clone()->count();

            // If Most Viewed Passed then show the property data with Order by on Total Click Descending
            if($request->has('most_viewed') && $request->most_viewed == 1){
                $propertyQuery = $propertyQuery->clone()->orderBy('total_click', 'DESC');
            }
            // If Most Liked Passed then show the property data with Order by on Total Click Descending
            else if($request->has('most_liked') && $request->most_liked == 1){
                $propertyQuery = $propertyQuery->clone()->orderBy('favourite_count', 'DESC');
            }else{
                // If No Most Viewed or Most Liked Passed then show the property data with Order by on Id Descending
                $propertyQuery = $propertyQuery->clone()->orderBy('id', 'DESC');
            }

            // Get properties list data
            $propertiesData = $propertyQuery->clone()
            ->with('category:id,category,image,slug_id')
            ->select('id','slug_id','propery_type','title_image','category_id','title','price','city','state','country','rentduration','added_by','is_premium','latitude','longitude','total_click')
            ->withCount('favourite');

            // Latitude and Longitude
            if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
                if($request->has('radius') && !empty($request->radius)){

                    // Get the distance from the latitude and longitude
                    $propertyQuery = $propertyQuery->clone()->selectRaw("
                            (6371 * acos(cos(radians($request->latitude))
                            * cos(radians(latitude))
                            * cos(radians(longitude) - radians($request->longitude))
                            + sin(radians($request->latitude))
                            * sin(radians(latitude)))) AS distance")
                        ->where('latitude', '!=', 0)
                        ->where('longitude', '!=', 0)
                        ->having('distance', '<', $request->radius);
                }else{
                    $propertyQuery = $propertyQuery->clone()->where('latitude',$request->latitude)->where('longitude',$request->longitude);
                }
            }


            $propertiesData = $propertiesData->skip($offset)
            ->take($limit)
            ->get()
            ->map(function($property){
                $property->promoted = $property->is_promoted;
                $property->is_premium = $property->is_premium == 1 ? true : false;
                $property->property_type = $property->propery_type;
                $property->assign_facilities = $property->assign_facilities;
                $property->parameters = $property->parameters;
                unset($property->propery_type);
                return $property;
            });

            // Sort properties based on the promoted attribute
            $propertiesData = $propertiesData->sortByDesc(function ($property) {
                return $property->promoted;
            })->values()->filter();

            $response = array(
                'error' => false,
                'total' => $totalProperties,
                'data' => $propertiesData,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // public function getPropertyDetail(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'id'        => 'required_without:slug_id',
    //         'slug_id'   => 'required_without:id',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => $validator->errors()->first(),
    //         ]);
    //     }
    //     try{
    //         $propertyQuery = Property::where('status', 1)->whereIn('propery_type',array(0,1))->with(['customer','user', 'category:id,category,image,slug_id','favourite','interested_users' => function ($query){
    //             $query->has('customer')->with('customer:id,name');
    //         }]);

    //         if($request->has('id') && !empty($request->id)){
    //             $propertyQuery = $propertyQuery->clone()->where(['id' => $request->id,'status' => 1]);
    //         } else {
    //             $propertyQuery = $propertyQuery->clone()->where(['slug_id' => $request->slug_id,'status' => 1]);
    //         }
    //         $propertyData = $propertyQuery->first();

    //         if ($propertyData) {
    //             $propertyData->is_premium = $propertyData->is_premium == 1 ? true : false;
    //             $propertyData->property_type = $propertyData->propery_type;
    //             $propertyData->assign_facilities = $propertyData->assign_facilities;
    //             $propertyData->parameters = $propertyData->parameters;
    //             unset($propertyData->propery_type);
    //         }

    //         $response = array(
    //             'error' => false,
    //             'data' => $propertyData,
    //             'message' => 'Data fetched Successfully'
    //         );
    //         return response()->json($response);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function getAgentVerificationFormFields(Request $request){
        $data = VerifyCustomerForm::where('status','active')->with('form_fields_values:id,verify_customer_form_id,value')->select('id','name','field_type')->get();

        if (collect($data)->isNotEmpty()) {
            ApiResponseService::successResponse("Data Fetched Successfully",$data,array(),200);
        } else {
            ApiResponseService::successResponse("No data found!");
        }
    }

    public function getAgentVerificationFormValues(Request $request){
        $data = VerifyCustomer::where('user_id', Auth::user()->id)->with(['user' => function($query){
            $query->select('id', 'name', 'profile')->withCount(['property', 'projects']);
        }])->with(['verify_customer_values' => function($query){
            $query->with('verify_form:id,name,field_type','verify_form.form_fields_values:id,verify_customer_form_id,value')->select('id','verify_customer_id','verify_customer_form_id','value');
        }])->first();

        if (collect($data)->isNotEmpty()) {
            ApiResponseService::successResponse("Data Fetched Successfully",$data,array(),200);
        } else {
            ApiResponseService::successResponse("No data found!");
        }
    }

    public function applyAgentVerification(Request $request) {
        $validator = Validator::make($request->all(), [
            'form_fields'           => 'required|array',
            'form_fields.*.id'      => 'required|exists:verify_customer_forms,id',
            'form_fields.*.value'   => 'required',
        ], [
            'form_fields.*.id'      => ':positionth Form Field id is not valid',
            'form_fields.*.value'   => ':positionth Form Field Value is not valid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            DB::beginTransaction();

            // If Payload is empty then show Payload is empty
            if (empty($request->form_fields)) {
                ApiResponseService::validationError("Payload is empty");
            }

            // Update the status of Customer (User) to pending
            $verifyCustomer = VerifyCustomer::updateOrCreate(['user_id' => Auth::user()->id], ['status' => 'pending']);
            $addCustomerValues = array();

            // Loop on request data of form_fields
            foreach ($request->form_fields as $key => $form_fields) {
                if (isset($form_fields['value']) && !empty($form_fields['value'])) {
                    // Check the Value is File upload or not
                    if ($request->hasFile('form_fields.' . $key . '.value')) {
                        $file = $request->file('form_fields.' . $key . '.value'); // Get Request File
                        $allowedImageExtensions = ['jpg', 'jpeg', 'png']; // Allowed Images Extensions
                        $allowedDocumentExtensions = ['doc', 'docx', 'pdf', 'txt']; // Allowed Documentation Extensions
                        $extension = $file->getClientOriginalExtension(); // Get Extension
                        // Check the extension and verify with allowed images or documents extensions
                        if (in_array($extension, $allowedImageExtensions) || in_array($extension, $allowedDocumentExtensions)) {
                            // Get Old form value
                            $oldFormValue = VerifyCustomerValue::where(['verify_customer_id' => $verifyCustomer->id, 'verify_customer_form_id' => $form_fields['id']])->with('verify_form:id,field_type')->first();
                            if (!empty($oldFormValue)) {
                                unlink_image($oldFormValue->value);
                            }
                            // Upload the new file
                            $destinationPath = public_path('images') . config('global.AGENT_VERIFICATION_DOC_PATH');
                            if (!is_dir($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $imageName = microtime(true) . "." . $extension;
                            $file->move($destinationPath, $imageName);
                            $value = $imageName;
                        } else {
                            ApiResponseService::validationError("Invalid file type. Allowed types are: jpg, jpeg, png, doc, docx, pdf, txt");
                        }
                    } else {
                        // Check the value other than File Upload
                        $formFieldQueryData = VerifyCustomerForm::where('id', $form_fields['id'])->first();
                        if ($formFieldQueryData->field_type == 'radio' || $formFieldQueryData->field_type == 'dropdown') {
                            // IF Field Type is Radio or Dropdown, then check its value with database stored options
                            $checkValueExists = VerifyCustomerFormValue::where(['verify_customer_form_id' => $form_fields['id'], 'value' => $form_fields['value']])->first();
                            if (collect($checkValueExists)->isEmpty()) {
                                ApiResponseService::validationError("No Form Value Found");
                            }
                            $value = $form_fields['value'];
                        } else if ($formFieldQueryData->field_type == 'checkbox') {
                            // IF Field Type is Checkbox
                            $submittedValue = explode(',', $form_fields['value']); // Explode the Comma Separated Values
                            // Loop on the values and check its value with database stored options
                            foreach ($submittedValue as $key => $value) {
                                $checkValueExists = VerifyCustomerFormValue::where(['verify_customer_form_id' => $form_fields['id'], 'value' => $value])->first();
                                if (collect($checkValueExists)->isEmpty()) {
                                    ApiResponseService::validationError("No Form Value Found");
                                }
                            }
                            // Convert the value into json encode
                            $value = json_encode($form_fields['value']);
                        } else {
                            // Get Value as it is for other field types
                            $value = $form_fields['value'];
                        }
                    }
                    // Create an array to upsert data
                    $addCustomerValues[] = array(
                        'verify_customer_id'        => $verifyCustomer->id,
                        'verify_customer_form_id'   => $form_fields['id'],
                        'value'                     => $value,
                        'created_at'                => now(),
                        'updated_at'                => now()
                    );
                }
            }

            // If array is not empty then update or create in bulk
            if (!empty($addCustomerValues)) {
                VerifyCustomerValue::upsert($addCustomerValues, ['verify_customer_id', 'verify_customer_form_id'], ['value']);
            }


            // Send Notification to Admin
            $fcm_id = array();
            $user_data = User::select('fcm_id', 'name')->get();
            foreach ($user_data as $user) {
                array_push($fcm_id, $user->fcm_id);
            }

            if (!empty($fcm_id)) {
                $registrationIDs = $fcm_id;
                $fcmMsg = array(
                    'title' => 'Agent Verification Form Submitted',
                    'message' => 'Agent Verification Form Submitted',
                    'type' => 'agent_verification',
                    'body' => 'Agent Verification Form Submitted',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default'
                );
                send_push_notification($registrationIDs, $fcmMsg);
            }

            // Commit the changes and return response
            DB::commit();
            ApiResponseService::successResponse("Data Submitted Successfully");
        } catch (Exception $e) {
            DB::rollback();
            ApiResponseService::logErrorResponse($e, $e->getMessage(), 'Something Went Wrong');
        }
    }

    public function calculateMortgageCalculator(Request $request) {
        $validator = Validator::make($request->all(), [
            'down_payment' => 'nullable|lt:loan_amount',
            'show_all_details' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $loanAmount = $request->loan_amount; // Loan amount
            $downPayment = $request->down_payment; // Down payment
            $interestRate = $request->interest_rate; // Annual interest rate in percentage
            $loanTermYear = $request->loan_term_years; // Loan term in years
            $showAllDetails = 0;
            if($request->show_all_details == 1){
                if (Auth::guard('sanctum')->check()) {
                    $packageLimit = HelperService::checkPackageLimit('mortgage_calculator_detail');
                    if ($packageLimit == true) {
                        $showAllDetails = 1;
                    }
                }
            }

            $schedule = $this->mortgageCalculation($loanAmount, $downPayment, $interestRate, $loanTermYear, $showAllDetails);
            ApiResponseService::successResponse('Data Fetched Successfully',$schedule,[],200);
        } catch (Exception $e) {
            ApiResponseService::logErrorResponse($e, $e->getMessage(), 'Something Went Wrong');
        }
    }
    public function getProjectDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug_id' => 'required_without:id',
            'get_similar' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            HelperService::checkPackageLimit('project_access');
            $getSimilarProjects = array();
            $project = Projects::with('customer:id,name,profile,email,mobile,address,slug_id')
                ->with('gallary_images')
                ->with('documents')
                ->with('plans')
                ->with('category:id,category,image')
                ->where(function($query){
                    $query->where(['request_status' => 'approved','status' => 1]);
                });

            if ($request->get_similar == 1) {
                if($request->has('id') && !empty($request->id)){
                    $getSimilarProjects = $project->clone()->where('id', '!=', $request->id)->select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location','category_id','added_by','request_status')->get();
                }else if($request->has('slug_id') && !empty($request->slug_id)){
                    $getSimilarProjects = $project->clone()->where('slug_id', '!=', $request->slug_id)->select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location','category_id','added_by','request_status')->get();
                }
            }

            if ($request->id) {
                $project = $project->clone()->where('id',$request->id);
                HelperService::incrementTotalClick('project',$request->id);
            }

            if ($request->slug_id) {
                $project = $project->clone()->where('slug_id',$request->slug_id);
                HelperService::incrementTotalClick('project',null,$request->slug_id);
            }

            $total = $project->clone()->count();
            $data = $project->first();

            if (!empty($data) && $data->is_admin_listing == 1) {
                $adminCompanyTel1 = system_setting('company_tel1');
                $adminEmail = system_setting('company_email');
                $adminAddress = system_setting('company_address');
                $adminData = User::where('type', 0)->select('id', 'name', 'profile', 'slug_id')->first();

                // Create modified customer data
                $customCustomer = [
                    'id' => $adminData->id,
                    'name' => $adminData->name,
                    'slug_id' => $adminData->slug_id,
                    'profile' => !empty($adminData->getRawOriginal('profile')) ? $adminData->profile : url('assets/images/faces/2.jpg'),
                    'mobile' => !empty($adminCompanyTel1) ? $adminCompanyTel1 : "",
                    'email' => !empty($adminEmail) ? $adminEmail : "",
                    'address' => !empty($adminAddress) ? $adminAddress : "",
                ];

                // Force Laravel to include the modified customer data
                $data->setRelation('customer', (object) $customCustomer);
                $data->customer = (object) $customCustomer;
            }



            ApiResponseService::successResponse(
                "Data Fetch Successfully",
                $data,
                array(
                    'total' => $total,
                    'similar_projects' => $getSimilarProjects
                )
            );
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getAddedProjects(Request $request){
        try{
            $user = Auth::user();


            // Base query for selected columns
            $projectsQuery = Projects::where('added_by', $user->id)
            ->with('category:id,slug_id,image,category', 'gallary_images', 'customer:id,name,profile,email,mobile');

            // Check if either id or slug_id is provided
            if ($request->filled('id') || $request->filled('slug_id')) {
                $specificProjectsQuery = $projectsQuery->clone()
                    ->where(function($query) use($request){
                        $query->when($request->filled('id'), function ($query) use ($request) {
                            return $query->where('id', $request->id);
                        })
                        ->when($request->filled('slug_id'), function ($query) use ($request) {
                            return $query->orWhere('slug_id', $request->slug_id);
                        });
                    });
                    $data = $specificProjectsQuery->clone()->first();
                    if(collect($data)->isNotEmpty()){
                        $data = $data->toArray();
                        $data['created_at'] = Carbon::parse($data['created_at'])->diffForHumans();
                    }
                    // Get Similar Projects
                    if($request->has('id')){
                        $getSimilarProjects = $projectsQuery->clone()->where('id','!=',$request->id)->get()->map(function ($project) {
                            $array = $project->toArray();
                            $array['created_at'] = Carbon::parse($project->created_at)->diffForHumans();
                            return $array;
                        });
                    }
                    else if($request->has('slug_id')){
                        $getSimilarProjects = $projectsQuery->clone()->where('slug_id','!=',$request->slug_id)->get()->map(function($project){
                            $projectArray = $project->toArray(); // detach from Eloquent model
                            $projectArray['created_at'] = Carbon::parse($project->created_at)->diffForHumans();
                            return $projectArray;
                        });
                    }

                    ApiResponseService::successResponse("Data Fetched Successfully",$data,array('similar_projects' => $getSimilarProjects));
            } else {

                $offset = isset($request->offset) ? $request->offset : 0;
                $limit = isset($request->limit) ? $request->limit : 10;

                // If neither id nor slug_id is provided, use the base query for selected columns
                $projectsQuery = $projectsQuery->clone()
                        ->when($request->filled('request_status'), function ($query) use ($request) {
                            // IF Request Status is passed and status has approved or rejected or pending or all
                            $requestAccessData = explode(',', $request->request_status);
                            return $query->whereIn('request_status', $requestAccessData);
                        })->select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location', 'status', 'category_id', 'added_by', 'created_at', 'request_status');
                // Get Total
                $total = $projectsQuery->clone()->count();

                // Get Data
                $data = $projectsQuery->clone()->take($limit)->skip($offset)->get()->map(function($project){
                    $project->reject_reason = (object)array();
                    if($project->request_status == 'rejected'){
                        $project->reject_reason = $project->reject_reason()->select('id','project_id','reason','created_at')->latest()->first();
                    }
                    $projectArray = $project->toArray();
                    $projectArray['created_at'] = $project->created_at->diffForHumans();
                    return $projectArray;
                });
                ApiResponseService::successResponse("Data Fetched Successfully",$data,array('total' => $total));
            }
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getProjects(Request $request){
        try{
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;
            $latitude = $request->has('latitude') ? $request->latitude : null;
            $longitude = $request->has('longitude') ? $request->longitude : null;
            $radius = $request->has('radius') ? $request->radius : null;

            // Query
            $projectsQuery = Projects::where(['request_status' => 'approved','status' => 1])
                        ->with('category:id,slug_id,image,category', 'gallary_images', 'customer:id,name,profile,email,mobile,slug_id')
                        ->select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'status', 'location','category_id','added_by','is_admin_listing','request_status')
                        ->when($latitude && $longitude, function($query) use($latitude, $longitude, $radius){
                            if($radius && !empty($radius)){
                                $query->selectRaw("
                                    (6371 * acos(cos(radians($latitude))
                                    * cos(radians(latitude))
                                    * cos(radians(longitude) - radians($longitude))
                                    + sin(radians($latitude))
                                    * sin(radians(latitude)))) AS distance")
                                    ->where('latitude', '!=', 0)
                                    ->where('longitude', '!=', 0)
                                    ->having('distance', '<', $radius);
                            }else{
                                $query->where(['latitude' => $latitude, 'longitude' => $longitude]);
                            }
                        })
                        ->when($request->filled('get_featured') && $request->get_featured == 1, function ($query) use ($request) {
                            return $query->whereHas('advertisement', function ($query) {
                                $query->where('for', 'project')->where('status', 0)->where('is_enable', 1);
                            });
                        });

            $postedSince = $request->posted_since;
            if (isset($postedSince)) {
                // 0: last_week   1: yesterday
                if ($postedSince == 0) {
                    $projectsQuery = $projectsQuery->clone()->whereBetween(
                        'created_at',
                        [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]
                    );
                }
                if ($postedSince == 1) {
                    $projectsQuery =  $projectsQuery->clone()->whereDate('created_at', Carbon::yesterday());
                }
            }

            // Get Total
            $total = $projectsQuery->clone()->count();

            // Get Admin Company Details
            $adminCompanyTel1 = system_setting('company_tel1');
            $adminEmail = system_setting('company_email');
            $adminUser = User::where('id',1)->select('id','slug_id')->first();

            // Get Data
            $data = $projectsQuery->clone()->take($limit)->skip($offset)->get()->map(function($project) use($adminCompanyTel1,$adminEmail,$adminUser){
                // Check if listing is by admin then add admin details in customer
                if ($project->is_admin_listing == true) {
                    unset($project->customer);
                    $project->customer = array(
                        'name' => "Admin",
                        'email' => $adminEmail,
                        'mobile' => $adminCompanyTel1,
                        'slug_id' => $adminUser->slug_id
                    );
                }
                return $project;
            });
            ApiResponseService::successResponse("Data Fetched Successfully",$data,array('total' => $total));
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function flutterwave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required'
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $packageId = $request->package_id;
            $package = Package::where(['id' => $packageId, 'status' => 1])->first(); // Get price data from the database
            if(collect($package)->isEmpty()){
                ApiResponseService::validationError("Package Not Found");
            }

            $currentUser = Auth::user();
            $currentUserName = $currentUser->name ?? null;
            $currentUserEmail = $currentUser->email ?? null;
            $currentUserNumber = $currentUser->mobile ?? null;

            $currencySymbol = HelperService::getSettingData('flutterwave_currency');

            $reference = Flutterwave::generateReference(); //This generates a payment reference

            //Add Payment Data to Payment Transactions Table
            $paymentTransactionData = PaymentTransaction::create([
                'user_id'         => $currentUser->id,
                'package_id'      => $package->id,
                'amount'          => $package->price,
                'payment_gateway' => "Flutterwave",
                'payment_status'  => 'pending',
                'order_id'        => $reference,
                'payment_type'    => 'online payment'
            ]);

            // Enter the details of the payment
            $data = [
                'payment_options' => 'card,banktransfer',
                'amount' => $package->price,
                'email' => $currentUserEmail,
                'tx_ref' => $reference,
                'currency' => $currencySymbol,
                'redirect_url' => URL::to('api/flutterwave-payment-status'),
                'customer' => [
                    'email' => $currentUserEmail,
                    "phone_number" => $currentUserNumber,
                    "name" => $currentUserName
                ],
                "meta" => [
                    "payment_transaction_id" => $paymentTransactionData->id,
                ]
            ];

            $payment = Flutterwave::initializePayment($data);

            if (empty($payment) || $payment['status'] !== 'success') {
                ApiResponseService::validationError("Payment Failed");
            }else{
                DB::commit();
                ApiResponseService::successResponse("Data Fetched Successfully",$payment);
            }
        } catch (Exception $e) {
            DB::rollBack();
            ApiResponseService::errorResponse();
        }
    }

    public function flutterwavePaymentStatus(Request $request)
    {
        $flutterwavePaymentInfo = $request->all();
        // Get Web URL
        $webURL = system_setting('web_url') ?? null;
        if (isset($flutterwavePaymentInfo) && !empty($flutterwavePaymentInfo) && isset($flutterwavePaymentInfo['status']) && !empty($flutterwavePaymentInfo['status'])){
            if($flutterwavePaymentInfo['status'] == "successful") {
                $webWithStatusURL = $webURL.'/payment/success';
                $response['error'] = false;
                $response['message'] = "Your Purchase Package Activate Within 10 Minutes ";
                $response['data'] = $flutterwavePaymentInfo;
            } else {
                $trxRef = $flutterwavePaymentInfo['tx_ref'];
                PaymentTransaction::where('order_id',$trxRef)->update(['payment_status' => 'failed']);
                $webWithStatusURL = $webURL.'/payment/fail';
                $response['error'] = true;
                $response['message'] = "Payment Cancelled / Declined ";
                $response['data'] = !empty($flutterwavePaymentInfo) ? $flutterwavePaymentInfo : "";
            }
        }else{
            $webWithStatusURL = $webURL.'/payment/fail';
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
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
        // return (response()->json($response));
    }

    public function blockChatUser(Request $request) {
        $userId = Auth::user()->id;

        $validator = Validator::make($request->all(),[
            'to_user_id' => [
                'required_without:to_admin',
                'exists:customers,id',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($value == $userId) {
                        $fail('You cannot block yourself.');
                    }
                }
            ],
            'to_admin' => 'required_without:to_user_id|in:1',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            $blockUserData = array(
                'by_user_id' => $userId,
                'reason' => $request->reason ?? null
            );
            if($request->has('to_user_id') && !empty($request->to_user_id)){
                $ifExtryExists = BlockedChatUser::where(['by_user_id' => $userId,'user_id' => $request->to_user_id])->count();
                if($ifExtryExists){
                    ApiResponseService::validationError("User Already Blocked");
                }
                $blockUserData['user_id'] = $request->to_user_id;
            } else if($request->has('to_admin') && $request->to_admin == 1){
                $ifExtryExists = BlockedChatUser::where(['by_user_id' => $userId,'admin' => 1])->count();
                if($ifExtryExists){
                    ApiResponseService::validationError("Admin Already Blocked");
                }
                $blockUserData['admin'] = 1;
            }else{
                ApiResponseService::errorResponse("Something Went Wrong in API");
            }

            BlockedChatUser::create($blockUserData);
            ApiResponseService::successResponse("User Blocked Successfully");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function unBlockChatUser(Request $request){
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'to_user_id' => [
                'required_without:to_admin',
                'exists:customers,id',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($value == $userId) {
                        $fail('You cannot unblock yourself.');
                    }
                }
            ],
            'to_admin' => 'required_without:to_user_id|in:1',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            if($request->has('to_user_id') && !empty($request->to_user_id)){
                $blockedUserQuery = BlockedChatUser::where(['by_user_id' => $userId,'user_id' => $request->to_user_id]);
                $ifExtryExists = $blockedUserQuery->clone()->count();
                if(!$ifExtryExists){
                    ApiResponseService::validationError("No Blocked User Found");
                }
                $blockedUserQuery->delete();
            } else if($request->has('to_admin') && $request->to_admin == 1){
                $blockedUserQuery = BlockedChatUser::where(['by_user_id' => $userId,'user_id' => $request->to_user_id]);
                $ifExtryExists = $blockedUserQuery->count();
                if(!$ifExtryExists){
                    ApiResponseService::validationError("No Blocked User Found");
                }
                $blockedUserQuery->delete();
            }else{
                ApiResponseService::validationError("Something Went Wrong in API");
            }
            ApiResponseService::successResponse("User Unblocked Successfully");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getFacilitiesForFilter(Request $request){
        try {
            $parameters = parameter::get();
            ApiResponseService::successResponse("Data Fetched Successfully",$parameters);
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getPrivacyPolicy(){
        try {
            $privacyPolicy = system_setting("privacy_policy");
            ApiResponseService::successResponse("Data Fetched Successfully",!empty($privacyPolicy) ? $privacyPolicy : "");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getTermsAndConditions(){
        try {
            $termsAndConditions = system_setting("terms_conditions");
            ApiResponseService::successResponse("Data Fetched Successfully",!empty($termsAndConditions) ? $termsAndConditions : "");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function userRegister(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            're_password' => 'required|same:password',
            'mobile' => 'nullable',
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $customerExists = Customer::where(['email' => $request->email, 'logintype' => 3])->count();
            if($customerExists){
                ApiResponseService::validationError("User Already Exists");
            }

            $customerData = $request->except('pasword','re_password');
            $customerData = array_merge($customerData,array(
                'password' => Hash::make($request->password),
                'auth_id' => Str::uuid()->toString(),
                'slug_id' => generateUniqueSlug($request->name, 5),
                'notification' => 1,
                'isActive' => 1,
                'logintype' => 3,
                'mobile' => $request->has('mobile') && !empty($request->mobile) ? $request->mobile : null,
            ));
            Customer::create($customerData);


            // Check if OTP already exists and is still valid
            $existingOtp = NumberOtp::where('email', $customerData['email'])->first();

            if ($existingOtp && now()->isBefore($existingOtp->expire_at)) {
                // OTP is still valid
                $otp = $existingOtp->otp;
            } else {
                // Generate a new OTP
                $otp = rand(123456, 999999);
                $expireAt = now()->addMinutes(10); // Set OTP expiry time

                // Update or create OTP entry in the database
                NumberOtp::updateOrCreate(
                    ['email' => $customerData['email']],
                    ['otp' => $otp, 'expire_at' => $expireAt]
                );
            }

            /** Register Mail */
            // Get Data of email type
            $emailTypeData = HelperService::getEmailTemplatesTypes("welcome_mail");

            // Email Template
            $welcomeEmailTemplateData = system_setting($emailTypeData['type']);
            $appName = env("APP_NAME") ?? "eBroker";
            $variables = array(
                'app_name' => $appName,
                'user_name' => !empty($request->name) ? $request->name : "$appName User",
                'email' => $request->email,
            );
            if(empty($welcomeEmailTemplateData)){
                $welcomeEmailTemplateData = "Welcome to $appName";
            }
            $welcomeEmailTemplate = HelperService::replaceEmailVariables($welcomeEmailTemplateData,$variables);

            $data = array(
                'email_template' => $welcomeEmailTemplate,
                'email' => $request->email,
                'title' => $emailTypeData['title'],
            );
            HelperService::sendMail($data,true);

            /** Send OTP mail for verification */
            // Get Data of email type
            $emailTypeData = HelperService::getEmailTemplatesTypes("verify_mail");

            // Email Template
            $propertyFeatureStatusTemplateData = system_setting($emailTypeData['type']);
            $appName = env("APP_NAME") ?? "eBroker";
            $variables = array(
                'app_name' => $appName,
                'otp' => $otp
            );
            if(empty($propertyFeatureStatusTemplateData)){
                $propertyFeatureStatusTemplateData = "Your OTP :- ".$otp;
            }
            $propertyFeatureStatusTemplate = HelperService::replaceEmailVariables($propertyFeatureStatusTemplateData,$variables);

            $data = array(
                'email_template' => $propertyFeatureStatusTemplate,
                'email' => $request->email,
                'title' => $emailTypeData['title'],
            );
            HelperService::sendMail($data);
            DB::commit();
            ApiResponseService::successResponse('User Registered Successfully');
        } catch (Exception $e) {
            DB::rollback();
            if (Str::contains($e->getMessage(), [
                'Failed',
                'Mail',
                'Mailer',
                'MailManager',
                "Connection could not be established"
            ])) {
                ApiResponseService::validationError("There is issue with mail configuration, kindly contact admin regarding this");
            } else {
                ApiResponseService::errorResponse();
            }
        }
    }

    public function changePropertyStatus(Request $request) {
        $validator = Validator::make($request->all(),[
            'property_id' => 'required|exists:propertys,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            // Get Query Data of property based on property id
            $propertyQueryData = Property::find($request->property_id);
            if($propertyQueryData->request_status != 'approved'){
                ApiResponseService::validationError("Property is not approved");
            }
            // update user status
            $propertyQueryData->status = $request->status == 1 ? 1 : 0;
            $propertyQueryData->save();
            ApiResponseService::successResponse("Data Updated Successfully");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try{
            $isUserExists = Customer::where(['email' => $request->email, 'logintype' => 3])->count();
            if($isUserExists){
                $token = HelperService::generateToken();
                HelperService::storeToken($request->email,$token);

                $rootAdminUrl = env("APP_URL") ?? FacadesRequest::root();
                $trimmedEmail = ltrim($rootAdminUrl,'/'); // remove / from starting if exists
                $link = $trimmedEmail."/reset-password?token=".$token;
                $data = array(
                    'email' => $request->email,
                    'link' => $link
                );

                // Get Data of email type
                $emailTypeData = HelperService::getEmailTemplatesTypes("reset_password");

                // Email Template
                $verifyEmailTemplateData = system_setting("password_reset_mail_template");
                $variables = array(
                    'app_name' => env("APP_NAME") ?? "eBroker",
                    'email' => $request->email,
                    'link' => $link
                );
                if(empty($verifyEmailTemplateData)){
                    $verifyEmailTemplateData = "Your reset password link is :- $link";
                }
                $verifyEmailTemplate = HelperService::replaceEmailVariables($verifyEmailTemplateData,$variables);

                $data = array(
                    'email_template' => $verifyEmailTemplate,
                    'email' => $request->email,
                    'title' => $emailTypeData['title'],
                );
                HelperService::sendMail($data);
                ApiResponseService::successResponse('Reset link sent to your mail successfully');
            }else{
                ApiResponseService::validationError("No User Found");
            }
        } catch (Exception $e) {
            if (Str::contains($e->getMessage(), [
                'Failed',
                'Mail',
                'Mailer',
                'MailManager',
                "Connection could not be established"
            ])) {
                ApiResponseService::validationError("There is issue with mail configuration, kindly contact admin regarding this");
            } else {
                ApiResponseService::errorResponse();
            }
        }
    }

    public function generateRazorpayOrderId(Request $request){
        $validator = Validator::make($request->all(),[
            'package_id' => 'required|exists:packages,id'
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            $packageData = Package::findOrFail($request->package_id);

            $razorPayApiKey = system_setting('razor_key');
            $razorPaySecretKey = system_setting('razor_secret');
            $currencyCode = system_setting('currency_code');
            $supportedCurrencies = array('AED','ALL','AMD','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BZD','CAD','CHF','CLP','CNY','COP','CRC','CUP','CVE','CZK','DJF','DKK','DOP','DZD','EGP','ETB','EUR','FJD','GBP','GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','ISK','JMD','JOD','JPY','KES','KGS','KHR','KMF','KRW','KWD','KYD','KZT','LAK','LKR','LRD','LSL','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SCR','SEK','SGD','SLL','SOS','SSP','SVC','SZL','THB','TND','TRY','TTD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VND','VUV','XAF','XCD','XOF','XPF','YER','ZAR','ZMW');

            if(empty($razorPayApiKey) || empty($razorPaySecretKey)){
                ApiResponseService::validationError("Payment Configuration is invalid, contact admin regarding this");
            }

            if($packageData->price == 0){
                ApiResponseService::validationError("Package is Free, no need to proceed for payment");
            }

            if(!empty($currencyCode)) {
                if(!in_array($currencyCode,$supportedCurrencies)){
                    ApiResponseService::validationError("Currency Selected in system is not supported");
                }
            }else{
                ApiResponseService::validationError("No Currency data available");
            }

            $api = new Api($razorPayApiKey, $razorPaySecretKey);


            $orderData = [
                'receipt'         => Str::uuid(),
                'amount'          => $packageData->price * 100, // Amount in paise, i.e., 50000 paise = ₹500
                'currency'        => $currencyCode,
                'payment_capture' => 1, // 1 for automatic capture, 0 for manual capture
                'notes' => array(
                    'user_id' => Auth::user()->id,
                    'package_id' => $request->package_id
                )
            ];

            $order = $api->order->create($orderData);
            $data = array(
                'order_id' => $order->id,
                'amount'   => $orderData['amount'],
                'currency' => $orderData['currency']
            );
            ApiResponseService::successResponse("Order Created Successfully",$data);
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function changeProjectStatus(Request $request) {
        $validator = Validator::make($request->all(),[
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            $loggedInUserID = Auth::user()->id;
            // Get Query Data of project based on project id
            $projectQuery = Projects::where('id',$request->project_id);
            $projectQueryData = $projectQuery->firstOrFail();
            if($projectQueryData->added_by != $loggedInUserID){
                ApiResponseService::validationError("Cannot change the status of project owned by others");
            }
            if($projectQueryData->request_status != 'approved'){
                ApiResponseService::validationError("Project is not approved");
            }
            // update user status
            $projectQuery->update(['status' => $request->status == 1 ? 1 : 0]);
            ApiResponseService::successResponse("Data Updated Successfully");
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getFeatures(Request $request){
        try {
            $features = Feature::where('status',1)->get();
            ApiResponseService::successResponse("Data Fetched Successfully",$features);
        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }
    public function getPackages(Request $request){
        $validator = Validator::make($request->all(),[
            'platform_type' => 'nullable|in:ios',
        ]);

        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            $packageQuery = Package::query();
            $filteredPackageQuery = $packageQuery->clone()->when($request->has('platform_type') && $request->platform_type == 'ios', function($query) {
                $query->whereNotNull('ios_product_id')->orWhere('package_type','free');
            });

            $auth = Auth::guard('sanctum');
            $getActivePackages = array();
            $getAllActivePackageIds = array();

            if($auth->check()) {
                $userId = $auth->user()->id;
                $getAllActivePackageIds = HelperService::getAllActivePackageIds($userId);

                if (!empty($getAllActivePackageIds)) {
                    $getActivePackages = $packageQuery->clone()->withTrashed()->whereIn('id', $getAllActivePackageIds)
                        ->with(['package_features' => function($query) use ($userId){
                            $query->with(['feature', 'user_package_limits' => function($subQuery) use($userId){
                                $subQuery->whereHas('user_package',function($userQuery) use($userId){
                                    $userQuery->where('user_id',$userId)->orderBy('id','desc');
                                });
                            }]);
                        },'user_packages' => function ($query) use ($userId) {
                            $query->where('user_id', $userId)->orderBy('id','desc');
                        }])
                        ->get()
                        ->map(function ($package) {
                            return [
                                'id'                        => $package->id,
                                'name'                      => $package->name,
                                'package_type'              => $package->package_type,
                                'ios_product_id'            => $package->ios_product_id,
                                'price'                     => $package->price,
                                'duration'                  => $package->duration,
                                'start_date'                => $package->user_packages[0]->start_date,
                                'end_date'                  => $package->user_packages[0]->end_date,
                                'created_at'                => $package->created_at,
                                'package_status'            => $package->package_payment_status,
                                'payment_transaction_id'    => $package->payment_transaction_id,
                                'features'                  => $package->package_features->map(function ($package_feature) {
                                    $usedLimit = !empty($package_feature->user_package_limits && !empty($package_feature->user_package_limits[0])) ? $package_feature->user_package_limits[0]->used_limit : null;
                                    $totalLimit = !empty($package_feature->user_package_limits && !empty($package_feature->user_package_limits[0])) ? $package_feature->user_package_limits[0]->total_limit : null;
                                    return [
                                        'id'            => $package_feature->feature->id,
                                        'name'          => $package_feature->feature->name,
                                        'limit_type'    => $package_feature->limit_type,
                                        'limit'         => $package_feature->limit,
                                        'used_limit'    => $usedLimit,
                                        'total_limit'   => $totalLimit
                                    ];
                                }),
                                'is_active' => 1
                            ];
                        });
                }
            }

            $getOtherPackagesQuery = $filteredPackageQuery->clone()->where('status', 1)
                ->whereHas('package_features', function($query) {
                    $query->whereHas('feature', function ($query) {
                        $query->where('status', 1);
                    });
                });

            if (!empty($getAllActivePackageIds)) {
                $getOtherPackagesQuery = $getOtherPackagesQuery->whereNotIn('id', $getAllActivePackageIds);
            }

            $getOtherPackageData = $getOtherPackagesQuery->with(['package_features' => function ($query) {
                $query->whereHas('feature', function ($query) {
                    $query->where('status', 1);
                });
            }])
            ->get()
            ->map(function ($package) {
                return [
                    'id'                        => $package->id,
                    'name'                      => $package->name,
                    'package_type'              => $package->package_type,
                    'price'                     => $package->price,
                    'ios_product_id'            => $package->ios_product_id,
                    'duration'                  => $package->duration,
                    'created_at'                => $package->created_at,
                    'package_status'            => $package->package_payment_status,
                    'payment_transaction_id'    => $package->payment_transaction_id,
                    'features'                  => $package->package_features->map(function ($package_feature) {
                        return [
                            'id'                => $package_feature->feature->id,
                            'name'              => $package_feature->feature->name,
                            'limit_type'        => $package_feature->limit_type,
                            'limit'             => $package_feature->limit,
                        ];
                    }),
                ];
            });
            $features = Feature::get();

            ApiResponseService::successResponse("Data Fetched Successfully", $getOtherPackageData, [
                'active_packages' => $getActivePackages,
                'all_features'    => $features
            ]);

        } catch (Exception $e) {
            ApiResponseService::errorResponse();
        }
    }

    public function getPaymentIntent(Request $request) {
        $validator = Validator::make($request->all(), [
            'package_id'        => 'required',
            'platform_type'     => 'required|in:app,web'
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $paymentSettings = HelperService::getActivePaymentDetails();
            if(empty($paymentSettings)){
                ApiResponseService::validationError("None of payment method is activated");
            }

            $package = Package::where(['id' => $request->package_id, 'package_type' => 'paid'])->first();
            if(empty($package)){
                ApiResponseService::validationError("No paid package found");
            }

            $purchasedPackage = UserPackage::where(['user_id' => Auth::user()->id, 'package_id' => $request->package_id])->onlyActive()->first();
            if (!empty($purchasedPackage)) {
                ApiResponseService::validationError("You already have purchased this package");
            }

            //Add Payment Data to Payment Transactions Table
            $paymentTransactionData = PaymentTransaction::create([
                'user_id'         => Auth::user()->id,
                'package_id'      => $request->package_id,
                'package_id'      => $package->id,
                'amount'          => $package->price,
                'payment_gateway' => Str::ucfirst($paymentSettings['payment_method']),
                'payment_status'  => 'pending',
                'order_id'        => null,
                'payment_type'    => 'online payment'
            ]);


            $paymentIntent = PaymentService::create($paymentSettings)->createAndFormatPaymentIntent(round($package->price, 2), [
                'payment_transaction_id' => $paymentTransactionData->id,
                'package_id'             => $package->id,
                'user_id'                => Auth::user()->id,
                'email'                  => Auth::user()->email,
                'platform_type'          => $request->platform_type,
                'description'            => $request->description ?? $package->name,
                'user_name'              => Auth::user()->name ?? "",
                'address_line1'          => Auth::user()->address ?? "",
                'address_city'           => Auth::user()->city ?? "",
            ]);
            $paymentTransactionData->update(['order_id' => $paymentIntent['id']]);

            $paymentTransactionData = PaymentTransaction::findOrFail($paymentTransactionData->id);
            // Custom Array to Show as response
            $paymentGatewayDetails = array(
                ...$paymentIntent,
                'payment_transaction_id' => $paymentTransactionData->id,
            );

            DB::commit();
            ApiResponseService::successResponse("", ["payment_intent" => $paymentGatewayDetails, "payment_transaction" => $paymentTransactionData]);
        } catch (Throwable $e) {
            DB::rollBack();
            ApiResponseService::logErrorResponse($e);
            ApiResponseService::errorResponse();
        }
    }

    public function makePaymentTransactionFail(Request $request){
        $validator = Validator::make($request->all(), [
            'payment_transaction_id' => 'required|exists:payment_transactions,id',
        ]);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            PaymentTransaction::where('id', $request->payment_transaction_id)->update(['payment_status' => 'failed']);
            ApiResponseService::successResponse("Data Updated Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ApiResponseService::logErrorResponse($e);
            ApiResponseService::errorResponse();
        }
    }

    public function checkPackageLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:property_list,property_feature,project_list,project_feature,mortgage_calculator_detail,premium_properties,project_access',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }
        try {
            $data = HelperService::checkPackageLimit($request->type,true);
            return ApiResponseService::successResponse('Data Fetched Successfully', $data);
        } catch (Exception $e) {
            return ApiResponseService::errorResponse();
        }
    }

    /** Get Property And Project Featured */
    public function getFeaturedData(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:property,project',
            ]);

            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;
            $loggedInUserID = Auth::user()->id;
            $advertisementQuery = Advertisement::select('id', 'status', 'start_date', 'end_date', 'property_id', 'project_id');
            if ($request->type == 'property') {
                $advertisementQuery->whereHas('property', function($query) use($loggedInUserID) {
                    $query->where(['post_type' => 1, 'added_by' => $loggedInUserID]);
                })->with('property:id,category_id,slug_id,title,propery_type,city,state,country,price,title_image','property.category:id,category,image');
            } else {
                $advertisementQuery->whereHas('project', function($query) use($loggedInUserID) {
                    $query->where(['added_by' => $loggedInUserID]);
                })->with('project:id,category_id,slug_id,title,type,city,state,country,image','project.category:id,category,image');
            }

            $total = $advertisementQuery->count();
            $data = $advertisementQuery->take($limit)->skip($offset)->orderBy('id', 'DESC')->get();

            ApiResponseService::successResponse("Data Fetched Successfully",$data, array('total' => $total));
        } catch (Exception $e) {
            return ApiResponseService::errorResponse();
        }
    }

    public function initiateBankTransaction(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:packages,id',
                'file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:3072',
            ]);

            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }

            DB::beginTransaction();
            $loggedInUserId = Auth::user()->id;
            $packageData = Package::findOrFail($request->package_id);

            // Check for free packages to not allowed
            if($packageData->package_type == 'free'){
                ApiResponseService::validationError("No paid package found");
            }

            // Check if user has already paid for this package
            $paymentTransaction = PaymentTransaction::where(['user_id' => $loggedInUserId, 'package_id' => $packageData->id])->first();
            if(!empty($paymentTransaction) && ($paymentTransaction->payment_status == 'pending' || $paymentTransaction->payment_status == 'rejected' || $paymentTransaction->payment_status == 'review'))  {
                ApiResponseService::validationError("Transaction is not completed");
            }

            $paymentTransactionData = PaymentTransaction::create([
                'user_id'         => $loggedInUserId,
                'package_id'      => $packageData->id,
                'amount'          => $packageData->price,
                'payment_gateway' => null,
                'payment_status'  => 'review',
                'order_id'        => Str::uuid(),
                'payment_type'    => 'bank transfer'
            ]);

            // Upload File
            $file = $request->file('file');
            $file = store_image($file, 'BANK_RECEIPT_FILE_PATH');
            if(empty($file)){
                ApiResponseService::validationError("File Upload Failed");
            }

            // Create Bank Receipt File
            $bankReceiptFile = BankReceiptFile::create([
                'payment_transaction_id' => $paymentTransactionData->id,
                'file' => $file,
            ]);
            $paymentTransactionData['bank_receipt_file'] = $bankReceiptFile->file;

            // Get Bank Details
            $bankDetailsFieldsQuery = system_setting('bank_details');
            if(isset($bankDetailsFieldsQuery) && !empty($bankDetailsFieldsQuery)){
                $bankDetailsFields = json_decode($bankDetailsFieldsQuery, true);
            }else{
                $bankDetailsFields = [];
            }
            DB::commit();

            ResponseService::successResponse('Transaction Initiated Successfully',$paymentTransactionData, array('bank_details' => $bankDetailsFields));
        } catch (Exception $e){
            DB::rollback();
            ApiResponseService::errorResponse();
        }
    }

    public function uploadBankReceiptFile(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'payment_transaction_id' => 'required',
                'file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:3072',
            ]);

            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }

            // Check Payment Transaction
            $paymentTransaction = PaymentTransaction::findOrFail($request->payment_transaction_id);
            if(empty($paymentTransaction)){
                ApiResponseService::validationError("Payment Transaction Not Found");
            }

            // Check Payment Transaction Status
            if($paymentTransaction->payment_status == 'review'){
                ApiResponseService::validationError("Your transaction is already in review");
            }

            PaymentTransaction::where('id', $request->payment_transaction_id)->update(['payment_status' => 'review']);

            // Upload File
            $file = $request->file('file');
            $file = store_image($file, 'BANK_RECEIPT_FILE_PATH');
            if(empty($file)){
                ApiResponseService::validationError("File Upload Failed");
            }

            // Create Bank Receipt File
            $bankReceiptFile = BankReceiptFile::create([
                'payment_transaction_id' => $request->payment_transaction_id,
                'file' => $file,
            ]);

            ApiResponseService::successResponse("File Uploaded Successfully", $bankReceiptFile);
        }catch(Exception $e){
            ApiResponseService::errorResponse();
        }
    }

    public function getPaymentReceipt(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'payment_transaction_id' => 'required|exists:payment_transactions,id',
            ]);

            if ($validator->fails()) {
                ApiResponseService::validationError($validator->errors()->first());
            }

            $loggedInUserId = Auth::user()->id;
            $payment = PaymentTransaction::with(
                'package:id,name,duration,package_type',
                'customer:id,name,email,mobile'
            )->without('customer.tokens')->findOrFail($request->payment_transaction_id);
            if($payment->user_id != $loggedInUserId){
                ApiResponseService::validationError("You are not authorized to view this receipt");
            }

            // Only allow viewing receipts for successful payments
            if ($payment->payment_status !== 'success') {
                ApiResponseService::validationError("Receipt is only available for successful payments");
            }
            $receiptService = new PaymentReceiptService();
            return $receiptService->generateHTML($payment);
        }catch(Exception $e){
            ApiResponseService::errorResponse();
        }
    }


    public function compareProperties(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'source_property_id' => 'required|exists:propertys,id',
                'target_property_id' => 'required|exists:propertys,id',
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            $sourcePropertyId = $request->source_property_id;
            $targetPropertyId = $request->target_property_id;


            $propertyBaseQuery = Property::where(['status' => 1, 'request_status' => 'approved'])->select('id', 'category_id', 'title', 'city', 'state', 'country', 'address', 'price', 'propery_type', 'total_click', 'rentduration','is_premium','title_image');
            $sourceProperty = $propertyBaseQuery->clone()->where('id', $sourcePropertyId)->first();
            $targetProperty = $propertyBaseQuery->clone()->where('id', $targetPropertyId)->first();
            if(empty($sourceProperty)){
                return ApiResponseService::errorResponse('Source property not found');
            }
            if(empty($targetProperty)){
                return ApiResponseService::errorResponse('Target property not found');
            }


            if($sourceProperty->category_id != $targetProperty->category_id){
                return ApiResponseService::errorResponse('Properties are not in the same category');
            }
            if($sourceProperty->id == $targetProperty->id){
                return ApiResponseService::errorResponse('Source and target properties cannot be the same');
            }
            if($sourceProperty->is_premium == 1){
                if (collect(Auth::guard('sanctum')->user())->isEmpty()) {
                    return ApiResponseService::errorResponse('Source property is a premium property');
                }else{
                    $data = HelperService::checkPackageLimit('property_feature', true);
                    if(($data['package_available'] == false || $data['feature_available'] == false) && $data['limit_available'] == false){
                        ApiResponseService::validationError("Source property is a premium property", $data);
                    }
                }
            }
            if($targetProperty->is_premium == 1){
                if (collect(Auth::guard('sanctum')->user())->isEmpty()) {
                    return ApiResponseService::errorResponse('Target property is a premium property');
                }else{
                    $data = HelperService::checkPackageLimit('property_feature', true);
                    if(($data['package_available'] == false || $data['feature_available'] == false) && $data['limit_available'] == false    ){
                        ApiResponseService::validationError("Target property is a premium property", $data);
                    }
                }
            }

            if (!$sourceProperty || !$targetProperty) {
                return ApiResponseService::errorResponse('One or both properties not found');
            }

            $sourcePropertyData = $this->getPropertyData($sourceProperty);
            $targetPropertyData = $this->getPropertyData($targetProperty);
            $data = array(
                'source_property' => $sourcePropertyData,
                'target_property' => $targetPropertyData
            );
            return ApiResponseService::successResponse("Properties compared successfully", $data);
        } catch (Exception $e) {
            return ApiResponseService::errorResponse($e->getMessage());
        }
    }
    public function getAllSimilarProperties(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'property_id' => 'required|exists:propertys,id',
                'search' => 'nullable|string',
                'offset' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 20;
            $getRequestProperty = Property::findOrFail($request->property_id);

            $getAllSimilarProperties = Property::where('id', '!=', $request->property_id)
            ->whereIn('propery_type', [0, 1])
            ->where(['status' => 1, 'request_status' => 'approved', 'category_id' => $getRequestProperty->category_id])
            ->select(
                'id', 'slug_id', 'category_id', 'city', 'state', 'country',
                'price', 'propery_type', 'title', 'title_image', 'is_premium',
                'address', 'rentduration', 'latitude', 'longitude'
            )
            ->with('category:id,slug_id,image,category')
            ->when($request->has('search'), function($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%');
            })
            ->when($request->has('offset'), function($query) use ($offset) {
                $query->offset($offset);
            })
            ->when($request->has('limit'), function($query) use ($limit) {
                $query->limit($limit);
            })
            ->get()
            ->map(function($propertyData){
                $propertyData->promoted = $propertyData->is_promoted;
                $propertyData->property_type = $propertyData->propery_type;
                $propertyData->parameters = $propertyData->parameters;
                $propertyData->is_premium = $propertyData->is_premium == 1;
                return $propertyData;
            });
            return ApiResponseService::successResponse("Similar properties fetched successfully", $getAllSimilarProperties);
        } catch (Exception $e) {
            return ApiResponseService::errorResponse($e->getMessage());
        }
    }

    public function deepLink(Request $request){
        try{
            $data = HelperService::getMultipleSettingData(['company_name','playstore_id','appstore_id']);
            $appName = $data['company_name'] ?? 'ebroker';
            $customerPlayStoreUrl = $data['playstore_id'] ?? 'https://play.google.com/store/apps/details?id=com.ebroker.ebroker';
            $customerAppStoreUrl = $data['appstore_id'] ?? 'https://apps.apple.com/app/id1564818806';
            return view('settings.deep-link', compact('appName', 'customerPlayStoreUrl', 'customerAppStoreUrl'));
        }catch(Exception $e){
            return ApiResponseService::errorResponse($e->getMessage());
        }
    }


    /*****************************************************************************************************************************************
     * Functions
     *****************************************************************************************************************************************
    */


    function getUnsplashData($cityData){
        $apiKey = env('UNSPLASH_API_KEY');
        $query = $cityData->city;
        $apiUrl = "https://api.unsplash.com/search/photos/?query=$query";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Client-ID ' . $apiKey,
        ]);
        $unsplashResponse = curl_exec($ch);

        curl_close($ch);

        $unsplashData = json_decode($unsplashResponse, true);
        // Check if the response contains data
        if (isset($unsplashData['results'])) {
            $results = $unsplashData['results'];

            // Initialize the image URL
            $imageUrl = '';

            // Loop through the results and get the first image URL
            foreach ($results as $result) {
                $imageUrl = $result['urls']['regular'];
                break; // Stop after getting the first image URL
            }
            if ($imageUrl != "") {
                return array('City' => $cityData->city, 'Count' => $cityData->property_count, 'image' => $imageUrl);
            }
        }
        return array('City' => $cityData->city, 'Count' => $cityData->property_count, 'image' => "");
    }

    public function getAutoApproveStatus($loggedInUserId){
        // Check auto approve is on and is user is verified or not
        $autoApproveSettingStatus = system_setting('auto_approve');
        $autoApproveStatus = false;
        if($autoApproveSettingStatus == 1){
            $userData = Customer::where('id', $loggedInUserId)->first();
            $autoApproveStatus = $userData->is_user_verified ? true : false;
        }

        return $autoApproveStatus;
    }
    function roundArrayValues($array,$pointsValue) {
        return array_map(function($item) use($pointsValue){
            if (is_array($item)) {
                return $this->roundArrayValues($item,$pointsValue); // Recursive call
            }
            return is_numeric($item) ? round($item, $pointsValue) : $item; // Base Case
        }, $array);
    }


    function mortgageCalculation($loanAmount, $downPayment, $interestRate, $loanTermYear, $showAllDetails) {
        if ($downPayment > 0) {
            $downPayment = (int)$downPayment;
            $loanAmount = $loanAmount - $downPayment;
        }

        // Convert annual interest rate to monthly interest rate
        $monthlyInterestRate = ($interestRate / 100) / 12;

        // Convert loan term in years to months
        $loanTermMonths = $loanTermYear * 12;

        // Calculate monthly payment
        $monthlyPayment = $loanAmount * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $loanTermMonths)) / (pow(1 + $monthlyInterestRate, $loanTermMonths) - 1);

        // Initialize an array to store the mortgage schedule
        $schedule = [];
        $schedule['main_total'] = array();

        // Initialize main totals
        $mainTotal = [
            'principal_amount' => $loanAmount,
            'down_payment' => $downPayment,
            'payable_interest' => 0,
            'monthly_emi' => $monthlyPayment,
            'total_amount' => 0,
        ];

        // Get current year and month
        $currentYear = date('Y');
        $currentMonth = date('n');

        // Initialize the remaining balance
        $remainingBalance = $loanAmount;

        // Loop through each month
        for ($i = 0; $i < $loanTermMonths; $i++) {
            $month = ($currentMonth + $i) % 12; // Ensure month wraps around by using modulo 12, so it does not exceed 12
            $year = $currentYear + floor(($currentMonth + $i - 1) / 12); // Calculate the year by incrementing when months exceed December

            // Correct month format
            $month = $month === 0 ? 12 : $month;

            // Calculate interest and principal
            $interest = $remainingBalance * $monthlyInterestRate;
            $principal = $monthlyPayment - $interest;
            $remainingBalance -= $principal;

            // Ensure remaining balance is not negative
            if ($remainingBalance < 0) {
                $remainingBalance = 0;
            }

            // Update yearly totals
            if ($showAllDetails && !isset($schedule['yearly_totals'][$year])) {
                $schedule['yearly_totals'][$year] = [
                    'year' => $year,
                    'monthly_emi' => 0,
                    'principal_amount' => 0,
                    'interest_paid' => 0,
                    'remaining_balance' => $remainingBalance,
                    'monthly_totals' => []
                ];
            }

            if ($showAllDetails) {
                $schedule['yearly_totals'][$year]['interest_paid'] += $interest;
                $schedule['yearly_totals'][$year]['principal_amount'] += $principal;

                // Store monthly totals
                $schedule['yearly_totals'][$year]['monthly_totals'][] = [
                    'month' => strtolower(date('F', mktime(0, 0, 0, $month, 1, $year))),
                    'principal_amount' => $principal,
                    'payable_interest' => $interest,
                    'remaining_balance' => $remainingBalance
                ];
            }

            // Update main total
            $mainTotal['payable_interest'] += $interest;
        }

        // Re-index the year totals array index, year used as index
        if ($showAllDetails) {
            $schedule['yearly_totals'] = array_values($schedule['yearly_totals']);
        }else{
            $schedule['yearly_totals'] = array();
        }

        // Calculate the total amount by addition of principle amount and total payable_interest
        $mainTotal['total_amount'] = $mainTotal['principal_amount'] + $mainTotal['payable_interest'];

        // Add Main Total in Schedule Variable
        $schedule['main_total'] = $mainTotal;

        // Round off values for display
        $schedule['main_total'] = $this->roundArrayValues($schedule['main_total'],2);
        $schedule['yearly_totals'] = $this->roundArrayValues($schedule['yearly_totals'],0);

        // Return the mortgage schedule
        return $schedule;
    }

    /**
     * Get homepage sections based on latitude and longitude also
     * @param float $latitude
     * @param float $longitude
     * @param Builder $propertyBaseQuery
     * @param Builder $projectsBaseQuery
     * @param Closure $propertyMapper
     * @param boolean $homepageLocationDataAvailable
     * @param Builder $locationBasedPropertyQuery
     * @return array
    */

    public function getHomepageSections($latitude, $longitude, $propertyBaseQuery, $propertyMapper, $projectsBaseQuery, $homepageLocationDataAvailable, $locationBasedPropertyQuery){
        $sections = [];
        $homepageSections = HomepageSection::where('is_active', 1)
                ->orderBy('sort_order')
                ->get();
        // Build homepageData array based on active sections
        foreach ($homepageSections as $section) {
            switch ($section->section_type) {
                case config('constants.HOMEPAGE_SECTION_TYPES.FAQS_SECTION.TYPE'):
                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => Faq::select('id', 'question', 'answer')
                            ->where('status', 1)
                            ->orderBy('id', 'DESC')
                            ->limit(5)
                            ->get()
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.PROJECTS_SECTION.TYPE'):
                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $projectsBaseQuery->clone()->inRandomOrder()->limit(12)->get()
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROJECTS_SECTION.TYPE'):
                    $featuredProjectData = $projectsBaseQuery->clone()
                        ->whereHas('advertisement', function($query) {
                            $query->where(['is_enable' => 1, 'status' => 0]);
                        })
                        ->inRandomOrder()
                        ->limit(12)
                        ->get();

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $featuredProjectData
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.CATEGORIES_SECTION.TYPE'):
                    $categoriesData = Category::select('id', 'category', 'image', 'slug_id')
                        ->where('status', 1)
                        ->whereHas('properties', function($query) {
                            $query->where(['status' => 1, 'request_status' => 'approved']);
                        })
                        ->withCount(['properties' => function($query) {
                            $query->where(['status' => 1, 'request_status' => 'approved']);
                        }])
                        ->limit(12)
                        ->get();

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $categoriesData
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.ARTICLES_SECTION.TYPE'):
                    $articlesData = Article::select('id', 'slug_id', 'category_id', 'title', 'description', 'image', 'created_at')
                        ->with('category:id,slug_id,image,category')
                        ->limit(5)
                        ->get();

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $articlesData
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.AGENTS_LIST_SECTION.TYPE'):
                    $agentsData = Customer::select('id', 'name', 'email', 'profile', 'slug_id')
                        ->withCount([
                            'projects' => function($query) {
                                $query->where(['status' => 1, 'request_status' => 'approved']);
                            },
                            'property' => function($query) {
                                $query->where(['status' => 1, 'request_status' => 'approved']);
                            }
                        ])
                        ->where('isActive', 1)
                        ->get()
                        ->map(function($customer) {
                            $customer->is_verified = $customer->is_user_verified;
                            $customer->total_count = $customer->projects_count + $customer->property_count;
                            $customer->is_admin = false;
                            return $customer;
                        })
                        ->filter(function($customer) {
                            return $customer->projects_count > 0 || $customer->property_count > 0;
                        })
                        ->sortByDesc(function($customer) {
                            return [$customer->is_verified, $customer->total_count];
                        })
                        ->values()
                        ->take(12);

                    // Add admin user if they have properties or projects
                    $adminEmail = system_setting('company_email');
                    $adminPropertyQuery = Property::where(['added_by' => 0, 'status' => 1, 'request_status' => 'approved']);
                    $adminProjectQuery = Projects::where(['is_admin_listing' => 1, 'status' => 1]);

                    if($latitude && $longitude){
                        if($homepageLocationDataAvailable){
                            $adminPropertiesCount = $adminPropertyQuery->where(['latitude' => $latitude, 'longitude' => $longitude])->count();
                            $adminProjectsCount = $adminProjectQuery->where(['latitude' => $latitude, 'longitude' => $longitude])->count();
                        }else{
                            $adminPropertiesCount = $adminPropertyQuery->count();
                            $adminProjectsCount = $adminProjectQuery->count();
                        }
                    } else {
                        $adminPropertiesCount = $adminPropertyQuery->count();
                        $adminProjectsCount = $adminProjectQuery->count();
                    }

                    if ($adminPropertiesCount > 0 || $adminProjectsCount > 0) {
                        $adminQuery = User::where('type', 0)->select('id', 'slug_id', 'profile')->first();
                        if ($adminQuery) {
                            $adminData = [
                                'id' => $adminQuery->id,
                                'name' => 'Admin',
                                'slug_id' => $adminQuery->slug_id,
                                'email' => !empty($adminEmail) ? $adminEmail : "",
                                'property_count' => $adminPropertiesCount,
                                'projects_count' => $adminProjectsCount,
                                'total_count' => $adminPropertiesCount + $adminProjectsCount,
                                'is_verified' => true,
                                'profile' => !empty($adminQuery->getRawOriginal('profile')) ? $adminQuery->profile : url('assets/images/faces/2.jpg'),
                                'is_admin' => true
                            ];
                            $agentsData->prepend((object)$adminData);
                        }
                    }

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $agentsData
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROPERTIES_SECTION.TYPE'):
                    $featuredSection = $locationBasedPropertyQuery->clone()
                        ->whereHas('advertisement', function($subQuery) {
                            $subQuery->where(['is_enable' => 1, 'status' => 0])
                                ->whereNot('type', 'Slider');
                        })
                        ->inRandomOrder()
                        ->limit(12)
                        ->get()
                        ->map($propertyMapper);

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $featuredSection
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.MOST_LIKED_PROPERTIES_SECTION.TYPE'):
                    $mostLikedProperties = $locationBasedPropertyQuery->clone()
                        ->withCount('favourite')
                        ->orderBy('favourite_count', 'DESC')
                        ->limit(12)
                        ->get()
                        ->map($propertyMapper);

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $mostLikedProperties
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.MOST_VIEWED_PROPERTIES_SECTION.TYPE'):
                    $mostViewedProperties = $locationBasedPropertyQuery->clone()
                        ->orderBy('total_click', 'DESC')
                        ->limit(12)
                        ->get()
                        ->map($propertyMapper);

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $mostViewedProperties
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.PREMIUM_PROPERTIES_SECTION.TYPE'):
                    $premiumPropertiesSection = $locationBasedPropertyQuery->clone()
                        ->where('is_premium', 1)
                        ->inRandomOrder()
                        ->limit(12)
                        ->get()
                        ->map($propertyMapper);

                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $premiumPropertiesSection
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.NEARBY_PROPERTIES_SECTION.TYPE'):
                    if(Auth::guard('sanctum')->check()){
                        $loggedInUser = Auth::guard('sanctum')->user();
                        $cityOfUser = $loggedInUser->city;
                        $nearbySection = $propertyBaseQuery->clone()
                            ->where('city', $cityOfUser)
                            ->inRandomOrder()
                            ->limit(12)
                            ->get()
                            ->map($propertyMapper);
                    }
                    $sections[] = [
                        'type' => $section->section_type,
                        'title' => $section->title,
                        'data' => $nearbySection ?? []
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.USER_RECOMMENDATIONS_SECTION.TYPE'):
                    if(Auth::guard('sanctum')->check()){
                        $loggedInUser = Auth::guard('sanctum')->user();
                        $userInterestData = UserInterest::where('user_id', $loggedInUser->id)->first();

                        if ($userInterestData) {
                            $userRecommendationQuery = $propertyBaseQuery->clone();

                            // Apply category filters
                            if (!empty($userInterestData->category_ids)) {
                                $categoryIds = explode(',', $userInterestData->category_ids);
                                $userRecommendationQuery->whereIn('category_id', $categoryIds);
                            }

                            // Apply price range filters
                            if (!empty($userInterestData->price_range)) {
                                $priceRange = explode(',', $userInterestData->price_range);
                                if (count($priceRange) >= 2) {
                                    $minPrice = floatval($priceRange[0]);
                                    $maxPrice = floatval($priceRange[1]);
                                    $userRecommendationQuery->whereRaw("CAST(price AS DECIMAL(10, 2)) BETWEEN ? AND ?", [$minPrice, $maxPrice]);
                                }
                            }

                            // Apply city filter
                            if (!empty($userInterestData->city)) {
                                $userRecommendationQuery->where('city', $userInterestData->city);
                            }

                            // Apply property type filter
                            if (!empty($userInterestData->property_type) || $userInterestData->property_type == '0') {
                                $propertyType = explode(',', $userInterestData->property_type);
                                $userRecommendationQuery->whereIn('propery_type', $propertyType);
                            }

                            // Apply outdoor facilities filter
                            if (!empty($userInterestData->outdoor_facilitiy_ids)) {
                                $outdoorFacilityIds = explode(',', $userInterestData->outdoor_facilitiy_ids);
                                $userRecommendationQuery->whereHas('assignfacilities.outdoorfacilities', function($q) use ($outdoorFacilityIds) {
                                    $q->whereIn('id', $outdoorFacilityIds);
                                });
                            }

                            // Get recommendations
                            $userRecommendations = $userRecommendationQuery
                                ->inRandomOrder()
                                ->limit(12)
                                ->get()
                            ->map($propertyMapper);
                        }
                    }
                    $sections[] = [
                        'type' => config('constants.HOMEPAGE_SECTION_TYPES.USER_RECOMMENDATIONS_SECTION.TYPE'),
                        'title' => $section->title,
                        'data' => $userRecommendations ?? []
                    ];
                    break;

                case config('constants.HOMEPAGE_SECTION_TYPES.PROPERTIES_BY_CITIES_SECTION.TYPE'):
                    $citiesData = CityImage::where('status',1)->withCount(['property' => function ($query) {
                        $query->whereIn('propery_type',[0,1])->where(['status' => 1, 'request_status' => 'approved']);
                    }])->having('property_count', '>', 0)->orderBy('property_count','DESC')->limit(12)->get();
                    $propertiesByCities = [];
                    foreach ($citiesData as $city) {
                        if (!empty($city->getRawOriginal('image'))) {
                            $url = $city->image;
                            $relativePath = parse_url($url, PHP_URL_PATH);
                            if (file_exists(public_path()  . $relativePath)) {
                                array_push($propertiesByCities, ['City' => $city->city, 'Count' => $city->property_count, 'image' => $city->image]);
                                continue;
                            }
                        }
                        $resultArray = $this->getUnsplashData($city);
                        array_push($propertiesByCities, $resultArray);
                    }
                    $sections[] = [
                        'type' => config('constants.HOMEPAGE_SECTION_TYPES.PROPERTIES_BY_CITIES_SECTION.TYPE'),
                        'title' => $section->title,
                        'data' => $propertiesByCities ?? []
                    ];
                    break;
            }
        }
        return $sections;
    }

    /**
     * Get the data of a property
     */
    function getPropertyData($property){
        $propertyData = [
            'id' => $property->id,
            'title' => $property->title,
            'city' => $property->city,
            'state' => $property->state,
            'country' => $property->country,
            'is_premium' => $property->is_premium,
            'title_image' => $property->title_image,
            'address' => $property->address,
            'created_at' => $property->created_at,
            'price' => $property->price,
            'rentduration' => !empty($property->rentduration) ? $property->rentduration : null,
            'property_type' => $property->propery_type,
            'total_likes' => $property->favourite()->count(),
            'total_views' => $property->total_click,
            'facilities' => $property->parameters,
            'near_by_places' => $property->assign_facilities,
        ];
        return $propertyData;
    }
    /************************************************************************************************************************ */



    // Temp API
    public function removeAccountTemp(Request $request){
        try {
            Customer::where(['email' => $request->email, 'logintype' => 3])->delete();
            ApiResponseService::successResponse("Done");
        } catch (\Throwable $th) {
            ApiResponseService::errorResponse("Issue");
        }
    }

}

