@extends('layouts.main')

@section('title')
    {{ __('System Settings') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>

            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">

            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        {{-- <form class="form" id="myForm" action="{{ url('set_settings') }}" data-parsley-validate method="POST" id="setting_form" enctype="multipart/form-data"> --}}
        {!! Form::open(['route' => 'store-settings', 'data-parsley-validate', 'class' => 'create-form', 'data-success-function'=> "formSuccessFunction",'enctype' => 'multipart/form-data']) !!}

            {{ csrf_field() }}
            <div class="form-group row">
                <div class="col-12">
                    <div class="card" style="height: 95%">

                        <div class="card-body">
                            <div class="divider pt-3">
                                <h6 class="divider-text">{{ __('Company Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    {{-- Company Name --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label center" for="company_name">{{ __('Company Name') }}</label>
                                        <input name="company_name" type="text" class="form-control" id="company_name" placeholder="{{ __('Company Name') }}" value="{{ isset($systemSettings['company_name']) && $systemSettings['company_name'] != '' ? $systemSettings['company_name'] : 'eBroker' }}">
                                    </div>

                                    {{-- Email --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label" for="email">{{ __('Email') }}</label>
                                        <input name="company_email" type="email" id="email" class="form-control" placeholder="{{ __('Email') }}" value="{{ isset($systemSettings['company_email']) && $systemSettings['company_email'] != '' ? $systemSettings['company_email'] : '' }}">
                                    </div>

                                    {{-- Contact Number 1 --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label" for="company-tel1">{{ __('Contact Number 1') }}</label>
                                        <input name="company_tel1" type="text" id="company-tel1" class="form-control" placeholder="{{ __('Contact Number 1') }}" value="{{ isset($systemSettings['company_tel1']) && $systemSettings['company_tel1'] != '' ? $systemSettings['company_tel1'] : '' }}">
                                    </div>

                                    {{-- Contact Number 2 --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label mt-1" for="company-tel2">{{ __('Contact Number 2') }}</label>
                                        <input name="company_tel2" type="text" id="company-tel2" class="form-control" placeholder="{{ __('Contact Number 2') }}" value="{{ isset($systemSettings['company_tel2']) && $systemSettings['company_tel2'] != '' ? $systemSettings['company_tel2'] : '' }}">
                                    </div>

                                    {{-- Latitude --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label" for="latitude">{{ __('Latitude') }}</label>
                                        <input name="latitude" type="text" id="latitude" class="form-control" placeholder="{{ __('Latitude') }}" value="{{ isset($systemSettings['latitude']) && $systemSettings['latitude'] != '' ? $systemSettings['latitude'] : '' }}">
                                    </div>

                                    {{-- Longitude --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-label mt-1" for="longitude">{{ __('Longitude') }}</label>
                                        <input name="longitude" type="text" id="longitude" class="form-control" placeholder="{{ __('Longitude') }}" value="{{ isset($systemSettings['longitude']) && $systemSettings['longitude'] != '' ? $systemSettings['longitude'] : '' }}">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <label class="form-label-mandatory" for="company-address">{{ __('Company Address') }}</label>
                                    <div class="col-sm-12">
                                        <textarea name="company_address" class="form-control" id="company_address" rows="3" placeholder="{{ __('Company Address') }}">{{ isset($systemSettings['company_address']) && $systemSettings['company_address'] != '' ? $systemSettings['company_address'] : '' }}</textarea>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="divider pt-3">
                                <h6 class="divider-text">{{ __('More Settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    {{-- Countries --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label" for="currency-code">{{ __('Currency Name') }}</label>
                                        <select id="currency-code" class="form-select form-control-sm select2" name="currency_code" required>
                                            <option value="">{{ __('Select Currency') }}</option>
                                            @if(!empty($listOfCurrencies))
                                                @foreach ($listOfCurrencies as $data)
                                                    <option value="{{ $data['currency_code'] }}">{{ $data['currency_name'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="hidden" id="url-for-currency-symbol" value="{{ route('get-currency-symbol') }}">
                                    </div>

                                    {{-- Currency Symbol --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label " for="curreny-symbol">{{ __('Currency Symbol') }}</label>
                                        <input name="currency_symbol" type="text" id="currency-symbol" class="form-control" placeholder="{{ __('Currency Symbol') }}" required maxlength="5" value="{{ isset($systemSettings['currency_symbol']) && $systemSettings['currency_symbol'] != '' ? $systemSettings['currency_symbol'] : '' }}">
                                    </div>

                                    {{-- Default Language --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label mt-1" for="default_language">{{ __('Default Language') }}</label>
                                        <select name="default_language" id="default_language" class="choosen-select form-select form-control-sm" required>
                                            @foreach ($languages as $row)
                                                {{ $row }}
                                                <option value="{{ $row->code }}"
                                                    {{ isset($systemSettings['default_language']) && $systemSettings['default_language'] == $row->code ? 'selected' : '' }}>
                                                    {{ $row->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Timezone --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label mt-1" for="timezone">{{ __('Timezone') }}</label>
                                        <select name="timezone" id="timezone" class="form-select form-control-sm select2" required>
                                            @php
                                                $utc = new DateTimeZone('UTC');
                                                $now = new DateTime('now', $utc);
                                            @endphp
                                            @foreach(DateTimeZone::listIdentifiers() as $timezone)
                                                @php
                                                    $tz = new DateTimeZone($timezone);
                                                    $offset = $tz->getOffset($now);
                                                    $offsetHours = abs(floor($offset / 3600));
                                                    $offsetMinutes = abs(floor(($offset % 3600) / 60));
                                                    $offsetString = ($offset < 0 ? '-' : '+') .
                                                                    str_pad($offsetHours, 2, '0', STR_PAD_LEFT) . ':' .
                                                                    str_pad($offsetMinutes, 2, '0', STR_PAD_LEFT);
                                                @endphp
                                                <option value="{{ $timezone }}" {{ isset($systemSettings['timezone']) && $systemSettings['timezone'] == $timezone ? 'selected' : '' }}>
                                                    {{ $timezone }} (UTC {{ $offsetString }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Min Radius Range --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 mt-1 form-label" for="min-radius-range">{{ __('Min Radius Range') }} <i class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('Minimum Radius Range for Homepage Location Data') }}"></i></label>
                                        <input name="min_radius_range" type="number" min="0" id="min-radius-range" class="form-control" placeholder="{{ __('Min Radius Range') }}" value="{{ isset($systemSettings['min_radius_range']) && $systemSettings['min_radius_range'] != '' ? $systemSettings['min_radius_range'] : '' }}" required>
                                    </div>

                                    {{-- Max Radius Range --}}
                                    <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                        <label class="col-sm-12 mt-1 form-label" for="max-radius-range">{{ __('Max Radius Range') }} <i class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('Maximum Radius Range for Homepage Location Data') }}"></i></label>
                                        <input name="max_radius_range" type="number" min="0" id="max-radius-range" class="form-control" placeholder="{{ __('Max Radius Range') }}" value="{{ isset($systemSettings['max_radius_range']) && $systemSettings['max_radius_range'] != '' ? $systemSettings['max_radius_range'] : '' }}" required>
                                    </div>

                                    <hr class="mt-4" style="">

                                    {{-- Unsplash API Key --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-12 form-label" for="unsplash-api-key">{{ __('Unsplash API Key') }}</label>
                                        <input name="unsplash_api_key" type="text" id="unsplash-api-key" class="form-control" placeholder="{{ __('Unsplash API Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['unsplash_api_key']) && $systemSettings['unsplash_api_key'] != '' ? $systemSettings['unsplash_api_key'] : '' ) : '****************************' ) : ( isset($systemSettings['unsplash_api_key']) && $systemSettings['unsplash_api_key'] != '' ? $systemSettings['unsplash_api_key'] : '' ))}}">
                                    </div>

                                    {{-- Place API Key --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-12 form-label" for="place-api-key">{{ __('Place API Key') }}</label>
                                        <input name="place_api_key" type="text" id="place-api-key" class="form-control" placeholder="{{ __('Place API Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['place_api_key']) && $systemSettings['place_api_key'] != '' ? $systemSettings['place_api_key'] : '' ) : '****************************' ) : ( isset($systemSettings['place_api_key']) && $systemSettings['place_api_key'] != '' ? $systemSettings['place_api_key'] : '' ))}}">
                                    </div>

                                    {{-- Playstore App link --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-12 form-label">{{ __('Playstore Id') }}</label>
                                        <input name="playstore_id" type="text" class="form-control" placeholder="{{ __('Playstore Id') }}" value="{{ isset($systemSettings['playstore_id']) && $systemSettings['playstore_id'] != '' ? $systemSettings['playstore_id'] : '' }}">
                                    </div>

                                    {{-- Appstore App link --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-12 form-label">{{ __('Appstore Id') }}</label>
                                        <input name="appstore_id" type="text" class="form-control" placeholder="{{ __('Appstore Id') }}" value="{{ isset($systemSettings['appstore_id']) && $systemSettings['appstore_id'] != '' ? $systemSettings['appstore_id'] : '' }}">
                                    </div>

                                    {{-- Number With Suffix --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-4 form-check-label" for="switch_number_with_suffix">{{ __('Number With Suffix') }}</label>
                                        <div class="col-sm-1 col-md-1 col-xs-12 ">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="number_with_suffix" id="number_with_suffix" value="{{ isset($systemSettings['number_with_suffix']) && $systemSettings['number_with_suffix'] != '' ? $systemSettings['number_with_suffix'] : 0 }}">
                                                <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['number_with_suffix']) && $systemSettings['number_with_suffix'] == '1' ? 'checked' : '' }} id="switch_number_with_suffix">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Change Icon Colors to theme Color --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-5 form-check-label" for="switch_svg_clr">{{ __('Change Icon Colors to theme Color ?') }}</label>
                                        <div class="col-sm-1">
                                            <div class="form-check form-switch ">
                                                <input type="hidden" name="svg_clr" id="svg_clr" value="{{ isset($systemSettings['svg_clr']) && $systemSettings['svg_clr'] != '' ? $systemSettings['svg_clr'] : 0 }}">
                                                <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['svg_clr']) && $systemSettings['svg_clr'] == '1' ? 'checked' : '' }} id="switch_svg_clr">
                                                <label class="form-check-label" for="switch_svg_clr"></label>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Distance Options --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label mt-3" for="distance-option">{{ __('Distance Options') }}</label>
                                        <select name="distance_option" id="distance-option" class="choosen-select form-select form-control-sm" required>
                                            <option  {{ isset($systemSettings['distance_option']) && $systemSettings['distance_option'] == 'km' ? 'selected' : '' }} value="km">{{__('Kilometers')}}</option>
                                            <option  {{ isset($systemSettings['distance_option']) && $systemSettings['distance_option'] == 'm' ? 'selected' : '' }} value="m">{{__('Meters')}}</option>
                                            <option  {{ isset($systemSettings['distance_option']) && $systemSettings['distance_option'] == 'mi' ? 'selected' : '' }} value="mi">{{__('Miles')}}</option>
                                            <option  {{ isset($systemSettings['distance_option']) && $systemSettings['distance_option'] == 'yd' ? 'selected' : '' }} value="yd">{{__('Yards')}}</option>
                                        </select>
                                    </div>

                                    {{-- System Color --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="col-sm-12 form-label mt-3">{{ __('System Color') }}</label>
                                        <input name="system_color" type="color" class="form-control" placeholder="{{ __('System Color') }}" value="{{ isset($systemSettings['system_color']) && $systemSettings['system_color'] != '' ? $systemSettings['system_color'] : '#087C7C' }}" id="systemColor">
                                        <input type="hidden" id="hiddenRGBA" name="rgb_color">
                                    </div>

                                    {{-- Web URL --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group">
                                        <label class="form-label mt-3">{{ __('Web URL') }}</label>
                                        <input name="web_url" id="web-url" type="text" class="form-control" placeholder="{{ __('Web URL') }}" value="{{ ( isset($systemSettings['web_url']) && $systemSettings['web_url'] != '' ? $systemSettings['web_url'] : '' )}}">
                                    </div>

                                    {{-- Text after property submission --}}
                                    <div class="col-sm-12 col-md-6 mt-2 form-group mandatory">
                                        <label class="col-sm-12 form-label mt-3">{{ __('Text after property submission') }}</label>
                                        <textarea name="text_property_submission" class="form-control" rows="2" placeholder="{{ __("Text after property submission") }}" required>{{ ( isset($systemSettings['text_property_submission']) && $systemSettings['text_property_submission'] != '' ? $systemSettings['text_property_submission'] : '' )}}</textarea>
                                    </div>


                                    {{-- Auto Approve Edited Listings --}}
                                    <div class="col-sm-12 col-md-6 mt-2">
                                        <label class="form-check-label">{{ __('Auto Approve Edited Listings') }} <i class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('Edited Listings will be automatically approved after editing') }}"></i></label>
                                        <div>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="auto_approve_edited_listings" id="auto_approve_edited_listings" value="{{ isset($systemSettings['auto_approve_edited_listings']) && $systemSettings['auto_approve_edited_listings'] != '' ? $systemSettings['auto_approve_edited_listings'] : 0 }}">
                                                <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['auto_approve_edited_listings']) && $systemSettings['auto_approve_edited_listings'] == '1' ? 'checked' : '' }} id="switch_auto_approve_edited_listings">
                                                <label class="form-check-label" for="switch_auto_approve_edited_listings"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Login Methods --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="divider pt-3">
                            <h6 class="divider-text">{{ __('Login Methods') }}</h6>
                        </div>
                        <div class="card-body row">

                            {{-- Number with OTP Login Toggle --}}
                            <div class="col-sm-12 col-md-6 mt-2 form-group mandatory">
                                <label class="form-check-label" for="number-with-otp-login-toggle">{{ __('Number with OTP Login') }}</label>
                                <div class="col-sm-1">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="number_with_otp_login" id="number-with-otp-login" value="{{ isset($systemSettings['number_with_otp_login']) && $systemSettings['number_with_otp_login'] == 1 ? 1 : 0 }}">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['number_with_otp_login']) && $systemSettings['number_with_otp_login'] == '1' ? 'checked' : '' }} id="number-with-otp-login-toggle">
                                        <label class="form-check-label" for="number-with-otp-login-toggle"></label>
                                    </div>
                                </div>
                            </div>

                            {{-- OTP Services Provider --}}
                            <div class="col-sm-12 col-md-6 mt-2 form-group mandatory" id="otp-services-provider-div" style="display: none">
                                <label class="col-sm-12 form-label-mandatory" for="otp-services-provider">{{ __('OTP Services Provider') }}</label>
                                <select name="otp_service_provider" id="otp-services-provider" class="choosen-select form-select form-control-sm">
                                    <option  {{ isset($systemSettings['otp_service_provider']) && $systemSettings['otp_service_provider'] == 'firebase' ? 'selected' : '' }} value="firebase">{{__('Firebase')}}</option>
                                    <option  {{ isset($systemSettings['otp_service_provider']) && $systemSettings['otp_service_provider'] == 'twilio' ? 'selected' : '' }} value="twilio">{{__('Twilio')}}</option>
                                </select>
                            </div>

                            <div class="col-12 mt-2 p-4 row bg-light rounded" id="twilio-sms-settings-div" style="display: none">
                                {{-- TWILIO --}}
                                <h5>{{ __('Twilio SMS Settings') }}</h5>

                                {{-- Account SID --}}
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="col-sm-12 form-label" for="twilio-account-sid">{{ __('Account SID') }}</label>
                                    <input name="twilio_account_sid" type="text" class="form-control twilio-account-settings" id="twilio-account-sid" placeholder="{{ __('Account SID') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['twilio_account_sid']) && $systemSettings['twilio_account_sid'] != '' ? $systemSettings['twilio_account_sid'] : '' ) : '****************************' ) : ( isset($systemSettings['twilio_account_sid']) && $systemSettings['twilio_account_sid'] != '' ? $systemSettings['twilio_account_sid'] : '' ))}}">
                                </div>

                                {{-- Auth Token --}}
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="col-sm-12 form-label" for="twilio-auth-token">{{ __('Auth Token') }}</label>
                                    <input name="twilio_auth_token" type="text" class="form-control twilio-account-settings" id="twilio-auth-token" placeholder="{{ __('Auth Token') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['twilio_auth_token']) && $systemSettings['twilio_auth_token'] != '' ? $systemSettings['twilio_auth_token'] : '' ) : '****************************' ) : ( isset($systemSettings['twilio_auth_token']) && $systemSettings['twilio_auth_token'] != '' ? $systemSettings['twilio_auth_token'] : '' ))}}">
                                </div>

                                {{-- My Twilio Phone Number --}}
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="col-sm-12 form-label" for="twilio-my-phone-number">{{ __('My Twilio Phone Number') }}</label>
                                    <input name="twilio_my_phone_number" type="text" class="form-control twilio-account-settings" id="twilio-my-phone-number" placeholder="{{ __('My Twilio Phone Number') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['twilio_my_phone_number']) && $systemSettings['twilio_my_phone_number'] != '' ? $systemSettings['twilio_my_phone_number'] : '' ) : '****************************' ) : ( isset($systemSettings['twilio_my_phone_number']) && $systemSettings['twilio_my_phone_number'] != '' ? $systemSettings['twilio_my_phone_number'] : '' ))}}">
                                </div>
                            </div>

                            {{-- Social Login Toggle --}}
                            <div class="col-sm-12 col-md-6 mt-2 form-group mandatory">
                                <label class="form-check-label" for="social-login-toggle">{{ __('Social Login') }}</label>
                                <div class="col-sm-1">
                                    <div class="form-check form-switch ">
                                        <input type="hidden" name="social_login" id="social-login" value="{{ isset($systemSettings['social_login']) && $systemSettings['social_login'] == 1 ? 1 : 0 }}">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['social_login']) && $systemSettings['social_login'] == '1' ? 'checked' : '' }} id="social-login-toggle">
                                        <label class="form-check-label mandatory" for="social-login-toggle"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">

                    {{-- Paypal Settings --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Paypal Setting') }}</h6>
                    </div>
                    <div class="form-group row">

                        {{-- Paypal Business ID --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paypal Business ID') }}</label>
                            <input name="paypal_business_id" type="text" class="form-control" placeholder="{{ __('Paypal Business ID') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['paypal_business_id']) && $systemSettings['paypal_business_id'] != '' ? $systemSettings['paypal_business_id'] : '' ) : '****************************' ) : ( isset($systemSettings['paypal_business_id']) && $systemSettings['paypal_business_id'] != '' ? $systemSettings['paypal_business_id'] : '' ))}}">
                        </div>

                        {{-- Paypal Webhook URL --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paypal Webhook URL') }}</label>
                            <input name="paypal_webhook_url" type="text" class="form-control" placeholder="{{ __('Paypal Webhook URL') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['paypal_webhook_url']) && $systemSettings['paypal_webhook_url'] != '' ? $systemSettings['paypal_webhook_url'] : '' ) : '****************************' ) : ( isset($systemSettings['paypal_webhook_url']) && $systemSettings['paypal_webhook_url'] != '' ? $systemSettings['paypal_webhook_url'] : '' ))}}">
                        </div>

                        {{-- Paypal Currency Symbol --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label">{{ __('Paypal Currency Symbol') }}</label>
                            <select name="paypal_currency" id="paypal_currency" class="choosen-select form-select form-control-sm">
                                @foreach ($paypalCurrencies as $key => $value)
                                    <option value={{ $key }} {{ isset($systemSettings['paypal_currency']) && $systemSettings['paypal_currency'] == $key ? 'selected' : '' }}> {{ $key }} - {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>


                        {{-- Status --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label" id='lbl_paypal'>{{ __('Enable') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="paypal_gateway" id="paypal_gateway" value="{{ isset($systemSettings['paypal_gateway']) && $systemSettings['paypal_gateway'] != '' ? $systemSettings['paypal_gateway'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op' {{ isset($systemSettings['paypal_gateway']) && $systemSettings['paypal_gateway'] == '1' ? 'checked' : '' }} id="switch_paypal_gateway">
                                    <label class="form-check-label" for="switch_paypal_gateway"></label>
                                </div>
                            </div>
                        </div>

                        {{-- Sandbox Mode --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label">{{ __('Sandbox Mode') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sandbox_mode" id="sandbox_mode" value="{{ isset($systemSettings['sandbox_mode']) && $systemSettings['sandbox_mode'] != '' ? $systemSettings['sandbox_mode'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['sandbox_mode']) && $systemSettings['sandbox_mode'] == '1' ? 'checked' : '' }} id="switch_sandbox_mode">
                                    <label class="form-check-label" for="switch_sandbox_mode"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Razorpay Setting --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Razorpay Setting') }}</h6>
                    </div>

                    <div class="form-group row">

                        {{-- Razorpay key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Razorpay key') }}</label>
                            <input name="razor_key" type="text" class="form-control" placeholder="{{ __('Razorpay Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['razor_key']) && $systemSettings['razor_key'] != '' ? $systemSettings['razor_key'] : '' ) : '****************************' ) : ( isset($systemSettings['razor_key']) && $systemSettings['razor_key'] != '' ? $systemSettings['razor_key'] : '' ))}}">
                        </div>

                        {{-- Razorpay Webhook URL --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Razorpay Webhook URL') }}</label>
                            <input name="razorpay_webhook_url" type="text" class="form-control" placeholder="{{ __('Razorpay Webhook URL') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['razorpay_webhook_url']) && $systemSettings['razorpay_webhook_url'] != '' ? $systemSettings['razorpay_webhook_url'] : '' ) : '****************************' ) : ( isset($systemSettings['razorpay_webhook_url']) && $systemSettings['razorpay_webhook_url'] != '' ? $systemSettings['razorpay_webhook_url'] : '' ))}}">
                        </div>

                        {{-- Razorpay Secret --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Razorpay Secret') }}</label>
                            <input name="razor_secret" type="text" class="form-control" placeholder="{{ __('Razorpay Secret') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['razor_secret']) && $systemSettings['razor_secret'] != '' ? $systemSettings['razor_secret'] : '' ) : '****************************' ) : ( isset($systemSettings['razor_secret']) && $systemSettings['razor_secret'] != '' ? $systemSettings['razor_secret'] : '' ))}}">
                        </div>

                        {{-- Status --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label" id='lbl_razorpay'>{{ __('Enable') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="razorpay_gateway" id="razorpay_gateway" value="{{ isset($systemSettings['razorpay_gateway']) && $systemSettings['razorpay_gateway'] != '' ? $systemSettings['razorpay_gateway'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op' {{ isset($systemSettings['razorpay_gateway']) && $systemSettings['razorpay_gateway'] == '1' ? 'checked' : '' }} id="switch_razorpay_gateway">
                                    <label class="form-check-label" for="switch_razorpay_gateway"></label>
                                </div>
                            </div>
                        </div>

                        {{-- Razorpay Webhook Secret --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Razorpay Webhook Secret') }}</label>
                            <input name="razor_webhook_secret" type="text" class="form-control" placeholder="{{ __('Razorpay Webhook Secret') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['razor_webhook_secret']) && $systemSettings['razor_webhook_secret'] != '' ? $systemSettings['razor_webhook_secret'] : '' ) : '****************************' ) : ( isset($systemSettings['razor_webhook_secret']) && $systemSettings['razor_webhook_secret'] != '' ? $systemSettings['razor_webhook_secret'] : '' ))}}">
                        </div>

                    </div>

                    {{-- Paystack Setting --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Paystack Setting') }}</h6>
                    </div>

                    <div class="form-group row">

                        {{-- Paystack Secret key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paystack Secret key') }}</label>
                            <input name="paystack_secret_key" type="text" class="form-control" placeholder="{{ __('Paystack Secret Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['paystack_secret_key']) && $systemSettings['paystack_secret_key'] != '' ? $systemSettings['paystack_secret_key'] : '' ) : '****************************' ) : ( isset($systemSettings['paystack_secret_key']) && $systemSettings['paystack_secret_key'] != '' ? $systemSettings['paystack_secret_key'] : '' ))}}">
                        </div>

                        {{-- Paystack Webhook URL --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paystack Webhook URL') }}</label>
                            <input name="paystack_webhook_url" type="text" class="form-control" placeholder="{{ __('Paystack Webhook URL') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['paystack_webhook_url']) && $systemSettings['paystack_webhook_url'] != '' ? $systemSettings['paystack_webhook_url'] : '' ) : '****************************' ) : ( isset($systemSettings['paystack_webhook_url']) && $systemSettings['paystack_webhook_url'] != '' ? $systemSettings['paystack_webhook_url'] : '' ))}}">
                        </div>

                        {{-- Paystack Currency Symbol --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paystack Currency Symbol') }}</label>
                            <select name="paystack_currency" id="paystack_currency" class="choosen-select form-select form-control-sm">
                                <option value="GHS" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'GHS' ? 'selected' : '' }}> GHS - Ghanaian Cedi</option>
                                <option value="KES" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'KES' ? 'selected' : '' }}> KES - Kenyan Shilling</option>
                                <option value="NGN" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'NGN' ? 'selected' : '' }}> NGN - Nigerian Naira</option>
                                <option value="USD" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'USD' ? 'selected' : '' }}> USD - United States Dollar</option>
                                <option value="XOF" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'XOF' ? 'selected' : '' }}> XOF - West African CFA Franc</option>
                                <option value="ZAR" {{ isset($systemSettings['paystack_currency']) && $systemSettings['paystack_currency'] == 'ZAR' ? 'selected' : '' }}> ZAR - South African Rand</option>
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label" id='lbl_paystack'>{{ __('Enable') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="paystack_gateway" id="paystack_gateway" value="{{ isset($systemSettings['paystack_gateway']) && $systemSettings['paystack_gateway'] != '' ? $systemSettings['paystack_gateway'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op' {{ isset($systemSettings['paystack_gateway']) && $systemSettings['paystack_gateway'] == '1' ? 'checked' : '' }} id="switch_paystack_gateway">
                                </div>
                            </div>
                        </div>

                        {{-- Paystack Public key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Paystack Public key') }}</label>
                            <input name="paystack_public_key" type="text" class="form-control" placeholder="{{ __('Paystack Public Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['paystack_public_key']) && $systemSettings['paystack_public_key'] != '' ? $systemSettings['paystack_public_key'] : '' ) : '****************************' ) : ( isset($systemSettings['paystack_public_key']) && $systemSettings['paystack_public_key'] != '' ? $systemSettings['paystack_public_key'] : '' ))}}">
                        </div>

                    </div>

                    {{-- Stripe Setting --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Stripe Setting') }}</h6>
                    </div>

                    <div class="form-group row">
                        {{-- Stripe publishable key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Stripe publishable key') }}</label>
                            <input name="stripe_publishable_key" type="text" class="form-control" placeholder="{{ __('Stripe publishable key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['stripe_publishable_key']) && $systemSettings['stripe_publishable_key'] != '' ? $systemSettings['stripe_publishable_key'] : '' ) : '****************************' ) : ( isset($systemSettings['stripe_publishable_key']) && $systemSettings['stripe_publishable_key'] != '' ? $systemSettings['stripe_publishable_key'] : '' ))}}">
                        </div>

                        {{-- Stripe Webhook URL --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Stripe Webhook URL') }}</label>
                            <input name="stripe_webhook_url" type="text" class="form-control" placeholder="{{ __('Stripe Webhook URL') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['stripe_webhook_url']) && $systemSettings['stripe_webhook_url'] != '' ? $systemSettings['stripe_webhook_url'] : '' ) : '****************************' ) : ( isset($systemSettings['stripe_webhook_url']) && $systemSettings['stripe_webhook_url'] != '' ? $systemSettings['stripe_webhook_url'] : '' ))}}">
                        </div>

                        {{-- Stripe Currency Symbol --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label">{{ __('Stripe Currency Symbol') }}</label>
                            <select name="stripe_currency" id="stripe_currency" class="choosen-select form-select form-control-sm">
                                @foreach ($stripe_currencies as $value)
                                <option value={{ $value }}
                                {{ isset($systemSettings['stripe_currency']) && $systemSettings['stripe_currency'] == $value ? 'selected' : '' }}>
                                {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label" id='lbl_stripe'>{{ __('Enable') }}</label>
                            <div>
                                <div class="form-check form-switch ">
                                    <input type="hidden" name="stripe_gateway" id="stripe_gateway" value="{{ isset($systemSettings['stripe_gateway']) && $systemSettings['stripe_gateway'] != '' ? $systemSettings['stripe_gateway'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op' {{ isset($systemSettings['stripe_gateway']) && $systemSettings['stripe_gateway'] == '1' ? 'checked' : '' }} id="switch_stripe_gateway">
                                </div>
                            </div>
                        </div>

                        {{-- Stripe Secret key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label-mandatory">{{ __('Stripe Secret key') }}</label>
                            <input name="stripe_secret_key" type="text" class="form-control" placeholder="{{ __('Stripe Secret key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['stripe_secret_key']) && $systemSettings['stripe_secret_key'] != '' ? $systemSettings['stripe_secret_key'] : '' ) : '****************************' ) : ( isset($systemSettings['stripe_secret_key']) && $systemSettings['stripe_secret_key'] != '' ? $systemSettings['stripe_secret_key'] : '' ))}}">
                        </div>

                        {{-- Stripe Secret key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label-mandatory">{{ __('Stripe Webhook Secret key') }}</label>
                            <input name="stripe_webhook_secret_key" type="text" class="form-control" placeholder="{{ __('Stripe Webhook Secret key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['stripe_webhook_secret_key']) && $systemSettings['stripe_webhook_secret_key'] != '' ? $systemSettings['stripe_webhook_secret_key'] : '' ) : '****************************' ) : ( isset($systemSettings['stripe_webhook_secret_key']) && $systemSettings['stripe_webhook_secret_key'] != '' ? $systemSettings['stripe_webhook_secret_key'] : '' ))}}">
                        </div>

                    </div>


                    {{-- Flutterwave Setting --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Flutterwave Setting') }}</h6>
                    </div>
                    <div class="form-group row">

                        {{-- Public Key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Public Key') }}</label>
                            <input name="flutterwave_public_key" type="text" class="form-control" placeholder="{{ __('Public Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['flutterwave_public_key']) && $systemSettings['flutterwave_public_key'] != '' ? $systemSettings['flutterwave_public_key'] : '' ) : '****************************' ) : ( isset($systemSettings['flutterwave_public_key']) && $systemSettings['flutterwave_public_key'] != '' ? $systemSettings['flutterwave_public_key'] : '' ))}}">
                        </div>

                        {{-- Secret Key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Secret Key') }}</label>
                            <input name="flutterwave_secret_key" type="text" class="form-control" placeholder="{{ __('Secret Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['flutterwave_secret_key']) && $systemSettings['flutterwave_secret_key'] != '' ? $systemSettings['flutterwave_secret_key'] : '' ) : '****************************' ) : ( isset($systemSettings['flutterwave_secret_key']) && $systemSettings['flutterwave_secret_key'] != '' ? $systemSettings['flutterwave_secret_key'] : '' ))}}">
                        </div>

                        {{-- Encryption key --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Encryption Key') }}</label>
                            <input name="flutterwave_encryption_key" type="text" class="form-control" placeholder="{{ __('Encryption Key') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['flutterwave_encryption_key']) && $systemSettings['flutterwave_encryption_key'] != '' ? $systemSettings['flutterwave_encryption_key'] : '' ) : '****************************' ) : ( isset($systemSettings['flutterwave_encryption_key']) && $systemSettings['flutterwave_encryption_key'] != '' ? $systemSettings['flutterwave_encryption_key'] : '' ))}}">
                        </div>

                        {{-- Flutterwave Webhook URL --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Flutterwave Webhook URL') }}</label>
                                <input name="flutterwave_webhook_url" type="text" class="form-control" placeholder="{{ __('Flutterwave Webhook URL') }}" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['flutterwave_webhook_url']) && $systemSettings['flutterwave_webhook_url'] != '' ? $systemSettings['flutterwave_webhook_url'] : '' ) : '****************************' ) : ( isset($systemSettings['flutterwave_webhook_url']) && $systemSettings['flutterwave_webhook_url'] != '' ? $systemSettings['flutterwave_webhook_url'] : '' ))}}">
                        </div>

                        {{-- Flutterwave Currency Symbol --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-label">{{ __('Flutterwave Currency Symbol') }}</label>
                            <select name="flutterwave_currency" id="flutterwave_currency" class="choosen-select form-select form-control-sm">
                                <option value="GBP" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'GBP' ? 'selected' : '' }}> British Pound Sterling (GBP)</option>
                                <option value="CAD" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'CAD' ? 'selected' : '' }}> Canadian Dollar (CAD)</option>
                                <option value="XAF" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'XAF' ? 'selected' : '' }}> Central African CFA Franc (XAF)</option>
                                <option value="CLP" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'CLP' ? 'selected' : '' }}> Chilean Peso (CLP)</option>
                                <option value="COP" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'COP' ? 'selected' : '' }}> Colombian Peso (COP)</option>
                                <option value="EGP" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'EGP' ? 'selected' : '' }}> Egyptian Pound (EGP)</option>
                                <option value="EUR" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'EUR' ? 'selected' : '' }}> SEPA (EUR)</option>
                                <option value="GHS" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'GHS' ? 'selected' : '' }}> Ghanaian Cedi (GHS)</option>
                                <option value="GNF" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'GNF' ? 'selected' : '' }}> Guinean Franc (GNF)</option>
                                <option value="KES" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'KES' ? 'selected' : '' }}> Kenyan Shilling (KES)</option>
                                <option value="MWK" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'MWK' ? 'selected' : '' }}> Malawian Kwacha (MWK)</option>
                                <option value="MAD" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'MAD' ? 'selected' : '' }}> Moroccan Dirham (MAD)</option>
                                <option value="NGN" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'NGN' ? 'selected' : '' }}> Nigerian Naira (NGN)</option>
                                <option value="RWF" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'RWF' ? 'selected' : '' }}> Rwandan Franc (RWF)</option>
                                <option value="SLL" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'SLL' ? 'selected' : '' }}> Sierra Leonean Leone (SLL)</option>
                                <option value="STD" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'STD' ? 'selected' : '' }}> São Tomé and Príncipe dobra (STD)</option>
                                <option value="ZAR" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'ZAR' ? 'selected' : '' }}> South African Rand (ZAR)</option>
                                <option value="TZS" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'TZS' ? 'selected' : '' }}> Tanzanian Shilling (TZS)</option>
                                <option value="UGX" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'UGX' ? 'selected' : '' }}> Ugandan Shilling (UGX)</option>
                                <option value="USD" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'USD' ? 'selected' : '' }}> United States Dollar (USD)</option>
                                <option value="XOF" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'XOF' ? 'selected' : '' }}> West African CFA Franc BCEAO (XOF)</option>
                                <option value="ZMW" {{ isset($systemSettings['flutterwave_currency']) && $systemSettings['flutterwave_currency'] == 'ZMW' ? 'selected' : '' }}> Zambian Kwacha (ZMW)</option>
                            </select>
                        </div>


                        {{-- Status --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label" id='lbl_flutterwave'>{{ __('Enable') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="flutterwave_status" id="flutterwave_status" value="{{ isset($systemSettings['flutterwave_status']) && $systemSettings['flutterwave_status'] != '' ? $systemSettings['flutterwave_status'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op' {{ isset($systemSettings['flutterwave_status']) && $systemSettings['flutterwave_status'] == '1' ? 'checked' : '' }} id="switch_flutterwave_status">
                                    <label class="form-check-label" for="switch_flutterwave_status"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bank Details Setting --}}
                    <div class="divider pt-3 mt-3">
                        <h6 class="divider-text">{{ __('Bank Details Setting') }}</h6>
                    </div>
                    <div class="form-group row">
                        {{-- Enable Bank Details --}}
                        <div class="col-sm-12 col-md-6 mt-2">
                            <label class="form-check-label">{{ __('Enable Bank Details') }}</label>
                            <div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="bank_transfer_status" id="bank_details_enabled" value="{{ isset($systemSettings['bank_transfer_status']) && $systemSettings['bank_transfer_status'] != '' ? $systemSettings['bank_transfer_status'] : 0 }}">
                                    <input class="form-check-input" type="checkbox" role="switch" {{ isset($systemSettings['bank_transfer_status']) && $systemSettings['bank_transfer_status'] == '1' ? 'checked' : '' }} id="switch_bank_details_enabled">
                                </div>
                            </div>
                        </div>

                        {{-- Bank Details Fields --}}
                        <div class="col-12 mt-3 bank-details-fields">
                            <label class="form-label">{{ __('Bank Details Fields') }}</label>
                            <div class="bank-details-repeater">
                                <div data-repeater-list="bank_details_fields">

                                    <div data-repeater-item>

                                        <div class="row mb-2">
                                            {{-- Title --}}
                                            <div class="col-md-4 form-group">
                                                <input type="text" class="form-control" name="title" placeholder="{{ __('Title') }}">
                                            </div>

                                            {{-- Value --}}
                                            <div class="col-md-4 form-group">
                                                <input type="text" class="form-control" name="value" placeholder="{{ __('Value') }}">
                                            </div>

                                            {{-- Delete --}}
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger" data-repeater-delete>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Add Field --}}
                                <button type="button" class="btn btn-success mt-2" data-repeater-create>
                                    {{ __('Add Field') }}
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="form-group row">
                        {{-- Deep Link Setting --}}
                        <div class="divider pt-3 mt-3">
                            <h6 class="divider-text">{{ __('Deep Link Settings') }}</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="schema" class="form-label">{{ __('Schema') }}</label>
                                <input type="text" class=" form-control" name="schema_for_deeplink" id="schema" value="{{ (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( isset($systemSettings['schema_for_deeplink']) && $systemSettings['schema_for_deeplink'] != '' ? $systemSettings['schema_for_deeplink'] : '' ) : 'your schema' ) : ( isset($systemSettings['schema_for_deeplink']) && $systemSettings['schema_for_deeplink'] != '' ? $systemSettings['schema_for_deeplink'] : '' ))}}" placeholder="{{ __('Your Schema') }}">
                                <small class="text-grey">{{ __('Note: Please add your scheme here using a single word in lowercase (e.g., ebroker).') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="divider pt-3">
                            <h6 class="divider-text">{{ __('Images') }}</h6>
                        </div>

                        <div class="row">
                            {{-- Favicon --}}
                            <div class="col-md-6 col-lg-4 mt-3">
                                <div class="col-12 form-group mandatory card title_card">
                                    {{ Form::label('favicon_icon', __('Favicon Icon'), ['class' => 'form-label col-12 ']) }}
                                    <input type="file" class="filepond" id="favicon_icon" name="favicon_icon" {{ isset($systemSettings['favicon_icon']) && $systemSettings['favicon_icon'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                    @if (isset($systemSettings['favicon_icon']) && $systemSettings['favicon_icon'] != '')
                                        <div class="title_img mt-2">
                                            <img src="{{ url('assets/images/logo/'.$systemSettings['favicon_icon']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Company Logo --}}
                            <div class="col-md-6 col-lg-4 mt-3">
                                <div class="col-12 form-group mandatory card title_card">
                                    {{ Form::label('company_logo', __('Comapany Logo'), ['class' => 'form-label col-12 ']) }}
                                    <input type="file" class="filepond" id="company_logo" name="company_logo" {{ isset($systemSettings['company_logo']) && $systemSettings['company_logo'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                    @if (isset($systemSettings['company_logo']) && $systemSettings['company_logo'] != '')
                                        <div class="title_img mt-2">
                                            <img src="{{ url('assets/images/logo/'.$systemSettings['company_logo']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Login Page Image --}}
                            <div class="col-md-6 col-lg-4 mt-3">
                                <div class="col-12 form-group mandatory card title_card">
                                    {{ Form::label('login_image', __('Login Page Image'), ['class' => 'form-label col-12 ']) }}
                                    <input type="file" class="filepond" id="login_image" name="login_image" {{ isset($systemSettings['login_image']) && $systemSettings['login_image'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                    @if (isset($systemSettings['login_image']) && $systemSettings['login_image'] != '')
                                        <div class="title_img mt-2">
                                            <img src="{{ url('assets/images/bg/'.$systemSettings['login_image']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                        </div>
                                    @else
                                        <div class="title_img mt-2">
                                            <img src="{{ url('assets/images/bg/Login_BG.jpg') }}" alt="Image" class="img-fluid" width="100" height="100">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" name="btnAdd" value="btnAdd" class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
            </div>
        {!! Form::close() !!}

    </section>
@endsection

@section('script')
<script type="text/javascript">
        $(document).ready(function () {
            let countryValue = "{{ isset($systemSettings['currency_code']) && $systemSettings['currency_code'] != '' ? $systemSettings['currency_code'] : '' }}";
            $("#currency-code").val(countryValue).trigger("change").promise().done(function () {
                let currencySymbol = "{{ isset($systemSettings['currency_symbol']) && $systemSettings['currency_symbol'] != '' ? $systemSettings['currency_symbol'] : '' }}";
                setTimeout(() => {
                    $("#currency-symbol").val(currencySymbol);
                }, 100);
            });

            // Initialize bank details visibility based on saved state
            if($("#bank_details_enabled").val() == "1") {
                $(".bank-details-fields").show();

                // After DOM is fully rendered, check if we need to add a default item
                setTimeout(() => {
                    const repeaterItems = $('[data-repeater-list="bank_details_fields"] [data-repeater-item]').length;
                    if (repeaterItems === 0) {
                        // Add one default item if none exist
                        $('[data-repeater-create]').trigger('click');
                    }
                }, 100);
            } else {
                $(".bank-details-fields").hide();
            }


            setTimeout(() => {
                bankDetailsRepeater.setList([
                    @foreach($bankDetailsFields as $key => $bankDetail)
                        {
                            title: "{{$bankDetail['title']}}",
                            value: "{{$bankDetail['value']}}",
                        },
                    @endforeach
                ]);
            }, 100);
        });


        $(document).on('click', '#favicon_icon', function(e) {

            $('.favicon_icon').hide();

        });
        $(document).on('click', '#company_logo', function(e) {

            $('.company_logo').hide();

        });

        $(document).on('click', '#login_image', function(e) {
            $('.login_image').hide();
        });


        const checkboxes = document.querySelectorAll('input[type=checkbox][role=switch][name=op]', );
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', (event) => {
                if (event.target.checked) {
                    checkboxes.forEach((checkbox) => {
                        if (checkbox !== event.target) {
                            checkbox.checked = false;
                            $("#switch_paypal_gateway").is(':checked') ? $("#paypal_gateway").val(1) : $("#paypal_gateway") .val(0);
                            $("#switch_razorpay_gateway").is(':checked') ? $("#razorpay_gateway").val(1) : $("#razorpay_gateway") .val(0);
                            $("#switch_paystack_gateway").is(':checked') ? $("#paystack_gateway").val(1) : $("#paystack_gateway") .val(0);
                            $("#switch_stripe_gateway").is(':checked') ? $("#stripe_gateway").val(1) : $("#stripe_gateway") .val(0);
                            $("#switch_flutterwave_status").is(':checked') ? $("#flutterwave_status").val(1) : $("#flutterwave_status") .val(0);
                        }
                    });
                }
            });
        });


        $("#switch_svg_clr").on('change', function() {
            $("#switch_svg_clr").is(':checked') ? $("#svg_clr").val(1) : $("#svg_clr") .val(0);
        });


        $("#switch_force_update").on('change', function() {
            $("#switch_force_update").is(':checked') ? $("#force_update").val(1) : $("#force_update") .val(0);
        });

        $("#switch_number_with_suffix").on('change', function() {
            $("#switch_number_with_suffix").is(':checked') ? $("#number_with_suffix").val(1) : $( "#number_with_suffix") .val(0);
        });

        // Change Event on OTP login Toggle
        $("#number-with-otp-login-toggle").on('change', function() {
            if ($("#number-with-otp-login-toggle").is(':checked')) {
                // If number with otp login is checked then make database value 1, show the otp services provider div, and trigger select option
                $("#number-with-otp-login").val(1);
                $("#otp-services-provider-div").show(100);
                $("#otp-services-provider").trigger('change');
            }else{
                // make database value 0, hide services provider div hide
                $("#number-with-otp-login") .val(0);
                $("#otp-services-provider-div").hide();
                $("#twilio-sms-settings-div").hide()
                $(".twilio-account-settings").removeAttr('required');
            }

        });

        // Change event on OTP services provider selection
        $("#otp-services-provider").on('change',function(){
            // Get the value of selection
            let otpServicesProviderValue = $(this).val();
            if(otpServicesProviderValue == 'twilio'){
                // IF Twilio then show the div of twilio sms settings with all the details required attribute
                $("#twilio-sms-settings-div").show();
                $(".twilio-account-settings").attr('required',true);
            }else{
                // IF other then hide the div of twilio sms settings and remove required attribute in all twilio settings
                $(".twilio-account-settings").removeAttr('required');
                $("#twilio-sms-settings-div").hide();
            }
        })

        $("#social-login-toggle").on('change', function() {
            $("#social-login-toggle").is(':checked') ? $("#social-login").val(1) : $( "#social-login") .val(0);
        });

        $("#switch_sandbox_mode").on('change', function() {
            $("#switch_sandbox_mode").is(':checked') ? $("#sandbox_mode").val(1) : $("#sandbox_mode") .val(0);
        });

        $("#switch_paypal_gateway").on('change', function() {
            $("#switch_paypal_gateway").is(':checked') ? $("#paypal_gateway").val(1) : $("#paypal_gateway") .val(0);
        });

        $("#switch_razorpay_gateway").on('change', function() {
            $("#switch_razorpay_gateway").is(':checked') ? $("#razorpay_gateway").val(1) : $("#razorpay_gateway") .val(0);
        });

        $("#switch_stripe_gateway").on('change', function() {
            $("#switch_stripe_gateway").is(':checked') ? $("#stripe_gateway").val(1) : $("#stripe_gateway") .val(0);
        });

        $("#switch_paystack_gateway").on('change', function() {
            $("#switch_paystack_gateway").is(':checked') ? $("#paystack_gateway").val(1) : $("#paystack_gateway") .val(0);
        });

        $("#switch_flutterwave_status").on('change', function() {
            $("#switch_flutterwave_status").is(':checked') ? $("#flutterwave_status").val(1) : $("#flutterwave_status") .val(0);
        });

        $("#switch_auto_approve_edited_listings").on('change', function() {
            $("#switch_auto_approve_edited_listings").is(':checked') ? $("#auto_approve_edited_listings").val(1) : $("#auto_approve_edited_listings") .val(0);
        });

        $("#switch_bank_details_enabled").on('change', function() {
            // Update hidden input value based on switch state
            if($(this).is(':checked')) {
                $("#bank_details_enabled").val(1);
                $(".bank-details-fields").show();

                // Check if there are no items in the repeater
                const repeaterItems = $('[data-repeater-list="bank_details_fields"] [data-repeater-item]').length;
                if (repeaterItems === 0) {
                    // Add one default item if none exist
                    $('[data-repeater-create]').trigger('click');
                }
            } else {
                $("#bank_details_enabled").val(0);
                $(".bank-details-fields").hide();
            }
        });

        function hexToRgb(hex) {
            const bigint = parseInt(hex.slice(1), 16);
            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;
            return `rgb(${r}, ${g}, ${b},0.15)`;
        }


        const colorForm = document.getElementById("setting_form");
        const systemColorInput = document.getElementById("systemColor");

        const hiddenRGBAInput = document.getElementById("hiddenRGBA");


        systemColorInput.addEventListener("change", function() {
            const selectedColor = systemColorInput.value;
            const alpha = 0.15; // You can adjust the alpha value as needed (1 for fully opaque)
            const rgba = hexToRgb(selectedColor);
            hiddenRGBAInput.value = rgba; // Update the hidden input with the new RGBA value
        });



        $(document).ready(function() {
            var companyname = $('#company_name').val();
            sessionStorage.setItem('comapanyname', $('#company_name').val());
            const newValue = `"${companyname}"`;
            const rgba = hexToRgb(systemColorInput.value);
            hiddenRGBAInput.value = rgba;

            // Bind change to login methods
            $("#number-with-otp-login-toggle, #social-login-toggle").change(function() {
                var loginCheckBoxToggle = $(this).attr("id") === "number-with-otp-login-toggle" ? "social-login-toggle" : "number-with-otp-login-toggle";
                var loginCheckbox = $("#" + loginCheckBoxToggle);

                // Check if both toggles are currently unchecked
                if (!$(this).is(":checked") && !loginCheckbox.is(":checked")) {
                    // Check the other toggle if both are unchecked
                    loginCheckbox.prop("checked", true).trigger('change');
                }
            });
            $("#number-with-otp-login-toggle").trigger('change')
        });

        $('.fav_icon_btn').click(function() {
            $('#fav_image').click();


        });
        fav_image.onchange = evt => {
            const [file] = fav_image.files
            if (file) {
                blah_fav.src = URL.createObjectURL(file)

            }
        }
        $('.btn_comapany_logo').click(function() {
            $('#company_logo').click();


        });
        company_logo.onchange = evt => {
            const [file] = company_logo.files
            if (file) {
                blah_comapany_logo.src = URL.createObjectURL(file)

            }
        }



        $('.btn_login_image').click(function() {
            $('#login_image').click();


        });
        login_image.onchange = evt => {
            const [file] = login_image.files
            if (file) {
                blah_login_image.src = URL.createObjectURL(file)

            }
        }

        function formSuccessFunction(){
            window.location.reload();
        }
    </script>
@endsection

