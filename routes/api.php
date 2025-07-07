<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*********************************************************************** */
/** Property */
Route::get('get_nearby_properties', [ApiController::class, 'get_nearby_properties']);
Route::get('compare-properties', [ApiController::class, 'compareProperties']);
Route::get('get-cities-data', [ApiController::class, 'getCitiesData']);
/*********************************************************************** */

/** Users */
Route::post('user_signup', [ApiController::class, 'user_signup']);
Route::post('user-register', [ApiController::class, 'userRegister']);

Route::get('forgot-password', [ApiController::class, 'forgotPassword']);
/*********************************************************************** */

/** Others */
Route::post('contct_us', [ApiController::class, 'contct_us']);
Route::get('get-slider', [ApiController::class, 'getSlider']);
Route::get('get_facilities', [ApiController::class, 'get_facilities']);
Route::get('get_seo_settings', [ApiController::class, 'get_seo_settings']);
Route::get('get_report_reasons', [ApiController::class, 'get_report_reasons']);
/*********************************************************************** */

/** Extra */
Route::get('get_articles', [ApiController::class, 'get_articles']);
Route::get('get_categories', [ApiController::class, 'get_categories']);
Route::get('get_languages', [ApiController::class, 'get_languages']);
/*********************************************************************** */

/** Only Declared */
Route::match(array('GET', 'POST'),'app_payment_status', [ApiController::class, 'app_payment_status']);
Route::match(array('GET', 'POST'),'flutterwave-payment-status', [ApiController::class, 'flutterwavePaymentStatus']);
/*********************************************************************** */

/** Confirmation needed */
Route::get('get_advertisement', [ApiController::class, 'get_advertisement']);
Route::post('mortgage_calc', [ApiController::class, 'mortgage_calc']);

Route::get('get_app_settings', [ApiController::class, 'get_app_settings']);
/*********************************************************************** */

/** Authenticated APIS */
Route::group(['middleware' => ['auth:sanctum']], function () {
    /*********************************************************************** */
    /** Property */
    Route::post('post_property', [ApiController::class, 'post_property']);
    Route::post('update_post_property', [ApiController::class, 'update_post_property']);
    Route::post('update_property_status', [ApiController::class, 'update_property_status']);
    Route::post('delete_property', [ApiController::class, 'delete_property']);
    Route::post('interested_users', [ApiController::class, 'interested_users']);
    Route::post('change-property-status', [ApiController::class, 'changePropertyStatus']);
    Route::get('get_favourite_property', [ApiController::class, 'get_favourite_property']);
    Route::get('get_property_inquiry', [ApiController::class, 'get_property_inquiry']);
    Route::get('get-added-properties',[ApiController::class,'getAddedProperties']);
    /*********************************************************************** */

    /** Users */
    Route::post('update_profile', [ApiController::class, 'update_profile']);
    Route::post('delete_user', [ApiController::class, 'delete_user']);
    Route::post('before-logout', [ApiController::class, 'beforeLogout']);
    Route::get('get-user-data', [ApiController::class, 'getUserData']);
    Route::get('get_user_recommendation', [ApiController::class, 'get_user_recommendation']);
    /*********************************************************************** */

    /** Chat */
    Route::post('send_message', [ApiController::class, 'send_message']);
    Route::post('delete_chat_message', [ApiController::class, 'delete_chat_message']);
    Route::post('block-user',[ApiController::class,'blockChatUser']);
    Route::post('unblock-user',[ApiController::class,'unBlockChatUser']);
    Route::get('get_messages', [ApiController::class, 'get_messages']);
    Route::get('get_chats', [ApiController::class, 'get_chats']);
    /*********************************************************************** */

    /** Package */
    Route::post('assign_package', [ApiController::class, 'assign_package']);
    Route::get('check-package-limit', [ApiController::class, 'checkPackageLimit']);
    Route::delete('remove-all-packages', [ApiController::class, 'removeAllPackages']);
    /*********************************************************************** */

    /** Agents */
    Route::get('get-agent-verification-form-fields', [ApiController::class, 'getAgentVerificationFormFields']);
    Route::get('get-agent-verification-form-values', [ApiController::class, 'getAgentVerificationFormValues']);
    Route::post('apply-agent-verification', [ApiController::class, 'applyAgentVerification']);
    /*********************************************************************** */

    /** Others */

    // Payment
    Route::post('flutterwave', [ApiController::class, 'flutterwave']);
    Route::post('createPaymentIntent', [ApiController::class, 'createPaymentIntent']);
    Route::post('confirmPayment', [ApiController::class, 'confirmPayment']);
    Route::get('get_payment_settings', [ApiController::class, 'get_payment_settings']);
    Route::get('get_payment_details', [ApiController::class, 'getPaymentTransactionDetails']);
    Route::get('paypal', [ApiController::class, 'paypal']);
    Route::post('generate-razorpay-orderid', [ApiController::class, 'generateRazorpayOrderId']);

    Route::post('create-payment-intent',[ApiController::class, 'getPaymentIntent']);
    Route::post('payment-transaction-fail', [ApiController::class, 'makePaymentTransactionFail']);

    // Payment Receipt
    Route::get('get-payment-receipt', [ApiController::class, 'getPaymentReceipt']);

    // Bank Transfer Apis
    Route::post('initiate-bank-transfer',[ApiController::class,'initiateBankTransaction']);
    Route::post('upload-bank-receipt-file',[ApiController::class,'uploadBankReceiptFile']);
    // Other's APIs
    Route::get('get_notification_list', [ApiController::class, 'get_notification_list']);
    /*********************************************************************** */

    /** Personalised Interest */
    Route::get('personalised-fields', [ApiController::class, 'getUserPersonalisedInterest']);
    Route::post('personalised-fields', [ApiController::class, 'storeUserPersonalisedInterest']);
    Route::delete('personalised-fields', [ApiController::class, 'deleteUserPersonalisedInterest']);
    /*********************************************************************** */

    /** Extra */
    Route::post('store_advertisement', [ApiController::class, 'store_advertisement']);
    Route::post('post_project', [ApiController::class, 'post_project']);
    Route::post('delete_project', [ApiController::class, 'delete_project']);
    Route::get('get_interested_users', [ApiController::class, 'getInterestedUsers']);
    /*********************************************************************** */

    /** Confirmation needed */
    Route::post('remove_post_images', [ApiController::class, 'remove_post_images']);
    Route::post('set_property_inquiry', [ApiController::class, 'set_property_inquiry']);
    Route::post('add_favourite', [ApiController::class, 'add_favourite']);
    Route::post('delete_favourite', [ApiController::class, 'delete_favourite']);
    Route::post('user_purchase_package', [ApiController::class, 'user_purchase_package']);
    Route::post('delete_advertisement', [ApiController::class, 'delete_advertisement']);
    Route::post('delete_inquiry', [ApiController::class, 'delete_inquiry']);
    Route::post('user_interested_property', [ApiController::class, 'user_interested_property']);
    Route::post('add_reports', [ApiController::class, 'add_reports']);
    Route::post('add_edit_user_interest', [ApiController::class, 'add_edit_user_interest']);
    /*********************************************************************** */


    /** Projects */
    Route::get('get-added-projects', [ApiController::class, 'getAddedProjects']);
    Route::get('get-project-detail', [ApiController::class, 'getProjectDetail']);
    Route::post('change-project-status', [ApiController::class, 'changeProjectStatus']);
    /*********************************************************************** */

    /** General Apis */
    Route::get('get-featured-data',[ApiController::class, 'getFeaturedData']);
});


/** Using Auth guard sanctum for get the data with or without authentication */

/** Property */
Route::get('get_property', [ApiController::class, 'get_property']);
Route::get('get-property-list', [ApiController::class, 'getPropertyList']);
Route::get('get-facilities-for-filter',[ApiController::class,'getFacilitiesForFilter']);
Route::get('get-all-similar-properties',[ApiController::class, 'getAllSimilarProperties']);
/*********************************************************************** */

/** Projects */
Route::get('get-projects', [ApiController::class, 'getProjects']);
/*********************************************************************** */

/** User */
Route::get('get-otp', [ApiController::class, 'getOtp']);
Route::get('verify-otp', [ApiController::class, 'verifyOtp']);
/*********************************************************************** */

/** Package */
// Route::get('get_package', [ApiController::class, 'get_package']);
Route::get('get-package', [ApiController::class, 'getPackages']);
Route::get('get-features', [ApiController::class, 'getFeatures']);
/*********************************************************************** */

/** Agents */
Route::get('agent-list', [ApiController::class, 'getAgentList']);
Route::get('agent-properties', [ApiController::class, 'getAgentProperties']);
/*********************************************************************** */

/** Settings */
Route::get('web-settings', [ApiController::class, 'getWebSettings']);
Route::get('app-settings', [ApiController::class, 'getAppSettings']);
/*********************************************************************** */

/** Mortgage Calculator */
Route::get('mortgage-calculator', [ApiController::class, 'calculateMortgageCalculator']);
/*********************************************************************** */

/** Extra */
Route::post('get_system_settings', [ApiController::class, 'get_system_settings']);
Route::get('homepage-data', [ApiController::class, 'homepageData']);
Route::get('faqs', [ApiController::class, 'getFaqData']);
Route::get('privacy-policy', [ApiController::class, 'getPrivacyPolicy']);
Route::get('terms-conditions', [ApiController::class, 'getTermsAndConditions']);


// Deep Link
Route::get('deep-link', [ApiController::class, 'deepLink']);

// Temp
Route::get('remove-account-temp', [ApiController::class, 'removeAccountTemp']);
/*********************************************************************** */

