<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\CityImagesController;
use App\Http\Controllers\SeoSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportReasonController;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\PackageFeatureController;
use App\Http\Controllers\HomepageSectionController;
use App\Http\Controllers\OutdoorFacilityController;
use App\Http\Controllers\PropertysInquiryController;
use App\Http\Controllers\VerifyCustomerFormController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('customer-privacy-policy', [SettingController::class, 'show_privacy_policy'])->name('customer-privacy-policy');


Route::get('customer-terms-conditions', [SettingController::class, 'show_terms_conditions'])->name('customer-terms-conditions');


Auth::routes();

Route::get('privacypolicy', [HomeController::class, 'privacy_policy']);
Route::post('/webhook/razorpay', [WebhookController::class, 'razorpay']);
Route::post('/webhook/paystack', [WebhookController::class, 'paystack']);
Route::post('/webhook/paypal', [WebhookController::class, 'paypal']);
Route::post('/webhook/stripe', [WebhookController::class, 'stripe']);
Route::post('/webhook/flutterwave', [WebhookController::class, 'flutterwave'])->name('webhook.flutterwave');

Route::get('response/paystack/success', [WebhookController::class, 'paystackSuccessCallback'])->name('paystack.success');
Route::get('response/paystack/success/web', [SettingController::class, 'paystackPaymentSuccess'])->name('paystack.success.web');
Route::get('response/paystack/cancel', [SettingController::class, 'paystackPaymentCancel'])->name('paystack.cancel');

Route::group(['prefix' => 'install'], static function () {
    Route::get('purchase-code', [InstallerController::class, 'purchaseCodeIndex'])->name('install.purchase-code.index');
    Route::post('purchase-code', [InstallerController::class, 'checkPurchaseCode'])->name('install.purchase-code.post');
});



Route::middleware(['language'])->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    });
    Route::middleware(['auth', 'checklogin'])->group(function () {
        Route::get('render_svg', [HomeController::class, 'render_svg'])->name('render_svg');
        Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'blank_dashboard'])->name('dashboard');
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('about-us', [SettingController::class, 'index']);
        Route::get('privacy-policy', [SettingController::class, 'index']);
        Route::get('terms-conditions', [SettingController::class, 'index']);
        Route::get('system-settings', [SettingController::class, 'systemSettingsIndex']);
        Route::get('firebase_settings', [SettingController::class, 'index']);
        Route::get('app-settings', [SettingController::class, 'appSettingsIndex']);
        Route::get('web-settings', [SettingController::class, 'webSettingsIndex']);
        Route::get('system-version', [SettingController::class, 'index']);
        Route::post('firebase-settings', [SettingController::class, 'firebase_settings']);
        Route::post('app-settings', [SettingController::class, 'app_settings']);
        Route::get('system-version', [SettingController::class, 'system_version']);
        Route::post('web-settings', [SettingController::class, 'web_settings']);
        Route::get('notification-settings', [SettingController::class, 'notificationSettingIndex'])->name('notification-setting-index');
        Route::post('notification-settings', [SettingController::class, 'notificationSettingStore'])->name('notification-setting-store');

        /** Email Settings */
        // Configuration
        Route::get('email-configurations', [SettingController::class, 'emailConfigurationsIndex'])->name('email-configurations-index');
        Route::post('email-configurations', [SettingController::class, 'emailConfigurationsStore'])->name('email-configurations-store');

        // Templates
        Route::get('email-templates', [SettingController::class, 'emailTemplatesIndex'])->name('email-templates.index');
        Route::get('modify-mail-templates/{type}', [SettingController::class, 'modifyMailTemplateIndex'])->name('modify-mail-templates.index');

        Route::get('email-templates-list', [SettingController::class, 'emailTemplatesList'])->name('email-templates.list');
        Route::post('email-templates', [SettingController::class, 'emailTemplatesStore'])->name('email-templates.store');

        // Verify
        Route::post('verify-email-config', [SettingController::class, 'verifyEmailConfig'])->name('verify-email-config');
        /** End Email Settings */

        Route::post('system-version-setting', [SettingController::class, 'system_version_setting']);

        /// START :: HOME ROUTE
        Route::get('change-password', [App\Http\Controllers\HomeController::class, 'change_password'])->name('changepassword');
        Route::post('check-password', [App\Http\Controllers\HomeController::class, 'check_password'])->name('checkpassword');
        Route::post('store-password', [App\Http\Controllers\HomeController::class, 'store_password'])->name('changepassword.store');
        Route::get('changeprofile', [HomeController::class, 'changeprofile'])->name('changeprofile');
        Route::post('updateprofile', [HomeController::class, 'update_profile'])->name('updateprofile');
        Route::post('firebase_messaging_settings', [HomeController::class, 'firebase_messaging_settings'])->name('firebase_messaging_settings');

        /// END :: HOME ROUTE

        /// START :: SETTINGS ROUTE

        Route::post('settings', [SettingController::class, 'settings']);
        Route::post('store-settings', [SettingController::class, 'system_settings'])->name('store-settings');
        /// END :: SETTINGS ROUTE

        /// START :: LANGUAGES ROUTE


        Route::resource('language', LanguageController::class);
        Route::get('language_list', [LanguageController::class, 'show']);
        Route::post('language_update', [LanguageController::class, 'update'])->name('language_update');
        Route::get('language-destory/{id}', [LanguageController::class, 'destroy'])->name('language.destroy');
        Route::get('set-language/{lang}', [LanguageController::class, 'set_language']);
        Route::get('download-panel-file', [LanguageController::class, 'downloadPanelFile'])->name('download-panel-file');
        Route::get('download-app-file', [LanguageController::class, 'downloadAppFile'])->name('download-app-file');
        Route::get('download-web-file', [LanguageController::class, 'downloadWebFile'])->name('download-web-file');

        /// END :: LANGUAGES ROUTE

        /// START :: PAYMENT ROUTE

        Route::get('payment-list', [PaymentController::class, 'paymentList'])->name('payment.list');
        Route::get('payment', [PaymentController::class, 'index'])->name('payment.index');
        Route::post('payment-status', [PaymentController::class, 'updateStatus'])->name('payment.status');
        Route::get('payment-receipt/{id}/view', [PaymentController::class, 'viewReceipt'])->name('payment.receipt.view');
        /// END :: PAYMENT ROUTE

        /// START :: USER ROUTE

        Route::resource('users', UserController::class);
        Route::post('users-update', [UserController::class, 'update']);
        Route::post('users-reset-password', [UserController::class, 'resetpassword']);
        Route::get('userList', [UserController::class, 'userList']);
        Route::get('get_users_inquiries', [UserController::class, 'users_inquiries']);
        Route::get('users_inquiries', [UserController::class, function () {
            return view('users.users_inquiries');
        }]);
        Route::get('destroy_contact_request/{id}', [UserController::class, 'destroy_contact_request'])->name('destroy_contact_request');




        /// END :: PAYMENT ROUTE

        /// START :: PAYMENT ROUTE

        Route::resource('customer', CustomersController::class);
        Route::get('customerList', [CustomersController::class, 'customerList']);
        Route::post('customerstatus', [CustomersController::class, 'update'])->name('customer.customerstatus');
        /// END :: CUSTOMER ROUTE

        /// START :: SLIDER ROUTE

        Route::resource('slider', SliderController::class);
        // Route::post('slider-order', [SliderController::class, 'update'])->name('slider.slider-order');
        Route::get('slider-destroy/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::get('sliderList', [SliderController::class, 'sliderList']);
        /// END :: SLIDER ROUTE

        /// START :: ARTICLE ROUTE

        Route::resource('article', ArticleController::class);
        Route::get('article_list', [ArticleController::class, 'show'])->name('article_list');
        Route::get('add_article', [ArticleController::class, 'create'])->name('add_article');
        Route::delete('article-destroy/{id}', [ArticleController::class, 'destroy'])->name('article.destroy');
        Route::post('article/generate-slug', [ArticleController::class, 'generateAndCheckSlug'])->name('article.generate-slug');
        /// END :: ARTICLE ROUTE

        /// START :: ADVERTISEMENT ROUTE

        Route::resource('featured_properties', AdvertisementController::class);
        Route::get('featured_properties_list', [AdvertisementController::class, 'show']);
        Route::post('featured_properties_status', [AdvertisementController::class, 'updateStatus'])->name('featured_properties.update-advertisement-status');
        Route::post('adv-status-update', [AdvertisementController::class, 'update'])->name('adv-status-update');
        /// END :: ADVERTISEMENT ROUTE

        /// START :: PACKAGE ROUTE

        Route::post('package-features/status-update', [PackageFeatureController::class, 'updateStatus'])->name('package-features.status-update');
        Route::resource('package-features',PackageFeatureController::class);

        Route::post('package-status', [PackageController::class, 'updatestatus'])->name('package.updatestatus');
        Route::get('user-packages', [PackageController::class,'userPackageIndex'])->name('user-packages.index');
        Route::get('user-package-list', [PackageController::class, 'getUserPackageList'])->name('user-packages.list');
        Route::resource('package', PackageController::class);


        /// END :: PACKAGE ROUTE


        /// START :: CATEGORY ROUTE
        Route::resource('categories', CategoryController::class);
        Route::get('categoriesList', [CategoryController::class, 'categoryList']);
        Route::post('categories-update', [CategoryController::class, 'update']);
        Route::post('categorystatus', [CategoryController::class, 'updateCategory'])->name('categorystatus');
        Route::post('category/generate-slug', [CategoryController::class, 'generateAndCheckSlug'])->name('category.generate-slug');
        /// END :: CATEGORYW ROUTE


        /// START :: PARAMETER FACILITY ROUTE

        Route::resource('parameters', ParameterController::class);
        Route::get('parameter-list', [ParameterController::class, 'show']);
        Route::post('parameter-update', [ParameterController::class, 'update']);
        /// END :: PARAMETER FACILITY ROUTE

        /// START :: OUTDOOR FACILITY ROUTE
        Route::resource('outdoor_facilities', OutdoorFacilityController::class);
        Route::get('facility-list', [OutdoorFacilityController::class, 'show']);
        Route::post('facility-update', [OutdoorFacilityController::class, 'update']);
        Route::get('facility-delete/{id}', [OutdoorFacilityController::class, 'destroy'])->name('outdoor_facilities.destroy');
        /// END :: OUTDOOR FACILITY ROUTE


        /// START :: PROPERTY ROUTE

        Route::prefix('property')->group(function () {
            Route::post('generate-slug', [PropertController::class, 'generateAndCheckSlug'])->name('property.generate-slug');
            Route::delete('remove-threeD-image/{id}', [PropertController::class, 'removeThreeDImage'])->name('property.remove-threeD-image');
            Route::post('property-documents', [PropertController::class, 'removeDocument'])->name('property.remove-documents');
        });

        Route::resource('property', PropertController::class);
        Route::get('getPropertyList', [PropertController::class, 'getPropertyList']);
        Route::post('updatepropertystatus', [PropertController::class, 'updateStatus'])->name('updatepropertystatus');
        Route::post('property-gallery', [PropertController::class, 'removeGalleryImage'])->name('property.removeGalleryImage');
        Route::get('get-state-by-country', [PropertController::class, 'getStatesByCountry'])->name('property.getStatesByCountry');
        Route::get('property-destroy/{id}', [PropertController::class, 'destroy'])->name('property.destroy');
        Route::get('getFeaturedPropertyList', [PropertController::class, 'getFeaturedPropertyList']);
        Route::post('updateaccessability', [PropertController::class, 'updateaccessability'])->name('updateaccessability');
        Route::post('update-property-request-status', [PropertController::class, 'updateRequestStatus'])->name('update-property-request-status');

        Route::get('updateFCMID', [UserController::class, 'updateFCMID']);
        /// END :: PROPERTY ROUTE


        /// START :: PROPERTY INQUIRY
        Route::resource('property-inquiry', PropertysInquiryController::class);
        Route::get('getPropertyInquiryList', [PropertysInquiryController::class, 'getPropertyInquiryList']);
        Route::post('property-inquiry-status', [PropertysInquiryController::class, 'updateStatus'])->name('property-inquiry.updateStatus');
        /// ENND :: PROPERTY INQUIRY

        /// START :: REPORTREASON
        Route::resource('report-reasons', ReportReasonController::class);
        Route::get('report-reasons-list', [ReportReasonController::class, 'show']);
        Route::post('report-reasons-update', [ReportReasonController::class, 'update']);
        Route::get('report-reasons-destroy/{id}', [ReportReasonController::class, 'destroy'])->name('reasons.destroy');
        Route::get('users_reports', [ReportReasonController::class, 'users_reports']);
        Route::get('user_reports_list', [ReportReasonController::class, 'user_reports_list']);
        /// END :: REPORTREASON

        Route::resource('property-inquiry', PropertysInquiryController::class);


        /// START :: CHAT ROUTE

        Route::get('get-chat-list', [ChatController::class, 'getChats'])->name('get-chat-list');
        Route::post('store_chat', [ChatController::class, 'store']);
        Route::get('getAllMessage', [ChatController::class, 'getAllMessage']);
        Route::post('block-user/{c_id}', [ChatController::class,'blockUser'])->name('block-user');
        Route::post('unblock-user/{c_id}', [ChatController::class,'unBlockUser'])->name('unblock-user');
        /// END :: CHAT ROUTE


        /// START :: NOTIFICATION
        Route::resource('notification', NotificationController::class);
        Route::get('notificationList', [NotificationController::class, 'notificationList']);
        Route::get('notification-delete', [NotificationController::class, 'destroy']);
        Route::post('notification-multiple-delete', [NotificationController::class, 'multiple_delete']);
        /// END :: NOTIFICATION

        /// START :: PROJECT
        Route::post('project-generate-slug', [ProjectController::class, 'generateAndCheckSlug'])->name('project.generate-slug');
        Route::post('updateProjectStatus', [ProjectController::class, 'updateStatus'])->name('updateProjectStatus');
        Route::post('project-gallery', [ProjectController::class, 'removeGalleryImage'])->name('project.remove-gallary-images');
        Route::post('project-document', [ProjectController::class, 'removeDocument'])->name('project.remove-document');
        Route::delete('remove-project-floor/{id}', [ProjectController::class, 'removeFloorPlan'])->name('project.remove-floor-plan');
        Route::post('update-project-request-status', [ProjectController::class, 'updateRequestStatus'])->name('update-project-request-status');
        Route::resource('project', ProjectController::class);
        /// END :: PROJECT

        /// START :: SEO SETTINGS
        Route::resource('seo_settings', SeoSettingsController::class);
        Route::get('seo-settings-destroy/{id}', [SeoSettingsController::class, 'destroy'])->name('seo_settings.destroy');
        /// END :: SEO SETTINGS

        /// START :: FAQs
        Route::post('faq/status-update', [FaqController::class, 'statusUpdate'])->name('faqs.status-update');
        Route::resource('faqs', FaqController::class);
        /// END :: FAQs

        /// START :: City Images
        Route::post('city-images/status-update', [CityImagesController::class, 'statusUpdate'])->name('city-images.status-update');
        Route::resource('city-images', CityImagesController::class);
        /// END :: City Images

        /// START :: Homepage Sections
        Route::post('homepage-sections/status-update', [HomepageSectionController::class, 'statusUpdate'])->name('homepage-sections.status-update');
        Route::post('homepage-sections/update-order', [HomepageSectionController::class, 'updateOrder'])->name('homepage-sections.update-order');
        Route::resource('homepage-sections', HomepageSectionController::class);
        /// END :: Homepage Sections


        Route::get('calculator', function () {
            if (!has_permissions('read', 'calculator')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            return view('Calculator.calculator');
        });


        /// Start :: User Verification Form
        Route::prefix('verify-customer')->group(function(){
            Route::get('/custom-form', [VerifyCustomerFormController::class, 'verifyCustomerFormIndex'])->name('verify-customer.form');
            Route::post('/save-custom-form', [VerifyCustomerFormController::class, 'verifyCustomerFormStore'])->name('verify-customer-form.store');
            Route::get('/list-custom-form', [VerifyCustomerFormController::class, 'verifyCustomerFormShow'])->name('verify-customer-form.show');
            Route::post('/update-custom-form', [VerifyCustomerFormController::class, 'verifyCustomerFormUpdate'])->name('verify-customer-form.update');
            Route::post('/status-custom-form', [VerifyCustomerFormController::class, 'verifyCustomerFormStatus'])->name('verify-customer-form.status');
            Route::delete('/delete-custom-form/{id}', [VerifyCustomerFormController::class, 'verifyCustomerFormDestroy'])->name('verify-customer-form.delete');
        });

        Route::prefix('agent-verification')->group(function () {
            Route::get('/', [VerifyCustomerFormController::class, 'agentVerificationListIndex'])->name('agent-verification.index');
            Route::get('/list', [VerifyCustomerFormController::class, 'agentVerificationList'])->name('agent-verification.list');
            Route::get('/submitted-form/{id}', [VerifyCustomerFormController::class, 'getAgentSubmittedForm'])->name('agent-verification.show-form');
            Route::post('/update-verification-status', [VerifyCustomerFormController::class,'updateVerificationStatus'])->name('agent-verification.change-status');
            Route::post('/auto-approve-settings', [VerifyCustomerFormController::class,'autoApproveSettings'])->name('agent-verification.auto-approve');
            Route::post('/verification-required-for-user-settings', [VerifyCustomerFormController::class,'verificationRequiredForUserSettings'])->name('agent-verification.verification-required-for-user');
        });

    });

    Route::get('get-currency-symbol',[SettingController::class, 'getCurrencySymbol'])->name('get-currency-symbol');
    // Reset Password
    Route::get('reset-password',[CustomersController::class, 'resetPasswordIndex']);
    Route::post('change-password',[CustomersController::class, 'resetPassword'])->name('customer.reset-password');
});
Route::get('deep-link', function(){
    return view('settings.deep-link');
});
// Local Language Values for JS
Route::get('/js/lang', static function () {
    //    https://medium.com/@serhii.matrunchyk/using-laravel-localization-with-javascript-and-vuejs-23064d0c210e
    header('Content-Type: text/javascript');
    $labels = \Illuminate\Support\Facades\Cache::remember('lang.js', 3600, static function () {
        $lang = Session::get('locale') ?? 'en';
        $files = resource_path('lang/' . $lang . '.json');
        return File::get($files);
    });
    echo('window.trans = ' . $labels);
    exit();
})->name('assets.lang');


// Add New Migration Route
Route::get('migrate', function () {
    Artisan::call('migrate');
    $output = Artisan::output();
    echo nl2br($output); // Convert newlines to <br> for better readability in HTML
});

// Route::get('migrate-status', function () {
//     Artisan::call('migrate:status');
//     $output = Artisan::output();
//     echo nl2br($output); // Convert newlines to <br> for better readability in HTML
// });

// // Rollback last step Migration Route
// Route::get('/rollback', function () {
//     Artisan::call('migrate:rollback');
//     return redirect()->back();
// });

// Clear Config
Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('view:cache');
    return redirect()->back();
});

Route::get('/add-url', function(){
    $envUpdates = [
        'APP_URL' => Request::root(),
    ];
    updateEnv($envUpdates);
})->name('add-url-in-env');

Route::get('/seed-demo-data', function(){
    Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
    $output = Artisan::output();
    echo nl2br($output); // Convert newlines to <br> for better readability in HTML
});
