@extends('layouts.main')

@section('title')
    {{ __('Email Configurations') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>{{ __('Email Configurations') }}</h4>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        {!! Form::open(['url' => route('email-configurations-store'), 'data-parsley-validate', 'class' => 'create-form', 'data-success-function'=> "formSuccessFunction"]) !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 order-md-1">
                                            <h5>{{ __('Update Configurations') }}</h5>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        {{-- Mailer --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-mailer">{{__('Mailer')}}</label>
                                            <select required name="mail_mailer" id="mail-mailer" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                                <option value="">{{ __("Select Mailer") }}</option>
                                                <option {{env('MAIL_MAILER')=='smtp' ?'selected':''}} value="smtp">SMTP</option>
                                                <option {{env('MAIL_MAILER')=='sendmail' ?'selected':''}} value="sendmail">sendmail</option>
                                                <option {{env('MAIL_MAILER')=='amazon_ses' ?'selected':''}} value="amazon_ses">Amazon SES</option>
                                            </select>
                                        </div>

                                        {{-- Mail Host --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-host">{{__('Mail Host')}}</label>
                                            <input name="mail_host" id="mail-host" value="{{env('MAIL_HOST')}}" type="text" required placeholder="{{__('Mail Host')}}" class="form-control"/>
                                        </div>

                                        {{-- Mail Port --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-port">{{__('Mail Port')}}</label>
                                            <input name="mail_port" id="mail-port" value="{{env('MAIL_PORT')}}" type="text" required placeholder="{{__('Mail Port')}}" class="form-control"/>
                                        </div>

                                        {{-- Mail Username --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-username">{{__('Mail Username')}}</label>
                                            <input name="mail_username" id="mail-username" value="{{env('MAIL_USERNAME')}}" type="text" required placeholder="{{__('Mail Username')}}" class="form-control"/>
                                        </div>

                                        {{-- Mail Password --}}
                                        <div class="form-group position-relative mb-4 col-md-4" id="pwd">
                                            <label for="mail-password">{{ __('Mail Password') }}</label>
                                            <input id="mail-password" type="password" value="{{env('MAIL_PASSWORD')}}" name="mail_password" placeholder="{{ __('Password') }}" class="form-control form-input" required>
                                            <div class="form-control-icon eye-icon">
                                                <i class="bi bi-eye" id='toggle_pass'></i>
                                            </div>
                                        </div>


                                        {{-- Mail Encryption --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-encryption">{{__('Mail Encryption')}}</label>
                                            <input name="mail_encryption" id="mail-encryption" value="{{env('MAIL_ENCRYPTION')}}" type="text" required placeholder="{{__('Mail Encryption')}}" class="form-control"/>
                                        </div>

                                        {{-- Mail Send From --}}
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="mail-send-from">{{__('Mail Send From')}}</label>
                                            <input name="mail_send_from" id="mail-send-from" value="{{env('MAIL_FROM_ADDRESS')}}" type="text" required placeholder="{{__('Mail Send From')}}" class="form-control"/>
                                        </div>
                                    </div>

                                    {{-- Save --}}
                                    <div class="col-12 d-flex mt-4">
                                        <button type="submit" name="btnAdd1" value="btnAdd" id="btnAdd1" class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}
    </section>

    {{-- Email Configuration Verification --}}
    <section class="section">
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 order-md-1">
                                <h5>{{ __('Email Configuration Verification') }}</h5>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <form class="verify_email create-form" action="{{route('verify-email-config')}}" method="POST" data-success-function="formSuccessFunction">
                                @csrf
                                {{-- Verify Email --}}
                                <div class="form-group col-md-6 col-lg-4">
                                    <label>{{__('Email')}}</label>
                                    <input name="verify_email" type="email" required placeholder="{{__('Email')}}" class="form-control" />
                                </div>
                                {{-- Verify --}}
                                <div class="form-group col-md-4">
                                    <input class="btn btn-primary" type="submit" value="{{ trans('Verify') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
<script>
    $('#pwd').click(function() {
        $('#password').focus();
    });

    $("#toggle_pass").click(function() {
        $(this).toggleClass("bi bi-eye bi-eye-slash");
        var input = $('[name="mail_password"]');
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });

    function formSuccessFunction(response) {
        if(!response.error && !response.warning){
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    }
</script>
@endsection
