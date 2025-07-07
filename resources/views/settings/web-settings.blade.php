@extends('layouts.main')

@section('title')
    {{ __('Web Settings') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first"> </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <form class="form" id="myForm" action="{{ url('web-settings') }}" method="POST" enctype="multipart/form-data" data-parsley-validate>
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="divider pt-3">
                                        <h6 class="divider-text">{{ __('Image Settings') }}</h6>
                                    </div>
                                    <div class="row">
                                        {{-- Favicon --}}
                                        <div class="col-md-6 mt-3">
                                            <div class="col-12 form-group mandatory card title_card">
                                                {{ Form::label('web_favicon', __('Favicon'), ['class' => 'form-label col-12 ']) }}
                                                <input type="file" class="filepond" id="web_favicon" name="web_favicon" {{ isset($getWebSettings['web_favicon']) && $getWebSettings['web_favicon'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                                @if (isset($getWebSettings['web_favicon']) && $getWebSettings['web_favicon'] != '')
                                                    <div class="title_img mt-2">
                                                        <img src="{{ url('assets/images/logo/'.$getWebSettings['web_favicon']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Main Logo --}}
                                        <div class="col-md-6 mt-3">
                                            <div class="col-12 form-group mandatory card title_card">
                                                {{ Form::label('web_logo', __('Main Logo'), ['class' => 'form-label col-12 ']) }}
                                                <input type="file" class="filepond" id="web_logo" name="web_logo" {{ isset($getWebSettings['web_logo']) && $getWebSettings['web_logo'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                                @if (isset($getWebSettings['web_logo']) && $getWebSettings['web_logo'] != '')
                                                    <div class="title_img mt-2">
                                                        <img src="{{ url('assets/images/logo/'.$getWebSettings['web_logo']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Placeholder Image --}}
                                        <div class="col-md-6 mt-3">
                                            <div class="col-12 form-group mandatory card title_card">
                                                {{ Form::label('web_placeholder_logo', __('Placeholder Image'), ['class' => 'form-label col-12 ']) }}
                                                <input type="file" class="filepond" id="web_placeholder_logo" name="web_placeholder_logo" {{ isset($getWebSettings['web_placeholder_logo']) && $getWebSettings['web_placeholder_logo'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                                @if (isset($getWebSettings['web_placeholder_logo']) && $getWebSettings['web_placeholder_logo'] != '')
                                                    <div class="title_img mt-2">
                                                        <img src="{{ url('assets/images/logo/'.$getWebSettings['web_placeholder_logo']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Footer Logo --}}
                                        <div class="col-md-6 mt-3">
                                            <div class="col-12 form-group mandatory card title_card">
                                                {{ Form::label('web_footer_logo', __('Footer Logo'), ['class' => 'form-label col-12 ']) }}
                                                <input type="file" class="filepond" id="web_footer_logo" name="web_footer_logo" {{ isset($getWebSettings['web_footer_logo']) && $getWebSettings['web_footer_logo'] == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                                                @if (isset($getWebSettings['web_footer_logo']) && $getWebSettings['web_footer_logo'] != '')
                                                    <div class="title_img mt-2">
                                                        <img src="{{ url('assets/images/logo/'.$getWebSettings['web_footer_logo']) }}" alt="Image" class="img-fluid" width="100" height="100">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="card">
                                <div class="card-body">
                                    {{-- Iframe Link For Web --}}
                                    <div class="divider pt-3">
                                        <h6 class="divider-text">{{ __('Iframe Link For Web') }}</h6>
                                    </div>
                                    <div class="form-group mandatory row mt-3">
                                        <label class="form-label-mandatory">{{ __('Link') }}</label>
                                        <textarea id="iframe_tag" class="form-control" rows="4" data-parsley-required="true" placeholder="{{ __('Iframe Link') }}">{{ isset($getWebSettings['iframe_link']) && $getWebSettings['iframe_link'] != '' ? $getWebSettings['iframe_link'] : '' }}</textarea>
                                        <input type="hidden" name="iframe_link" id="iframe_link" value="{{ isset($getWebSettings['iframe_link']) && $getWebSettings['iframe_link'] != '' ? $getWebSettings['iframe_link'] : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="divider pt-3">
                                        <h6 class="divider-text">{{ __('Social Media Links') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            {{-- Facebook ID --}}
                                            <label class="form-label mt-2">{{ __('Facebook Id') }}</label>
                                            <div class="col-sm-12">
                                                <input name="facebook_id" type="text" class="form-control" id="facebook_id" placeholder="{{ __('Facebook Id') }}" value="{{ isset($getWebSettings['facebook_id']) && $getWebSettings['facebook_id'] != '' ? $getWebSettings['facebook_id'] : '' }}">
                                            </div>

                                            {{-- Instagram ID --}}
                                            <label class="form-label mt-2">{{ __('Instagram Id') }}</label>
                                            <div class="col-sm-12">
                                                <input name="instagram_id" type="text" class="form-control" placeholder="{{ __('Instagram Id') }}" value="{{ isset($getWebSettings['instagram_id']) && $getWebSettings['instagram_id'] != '' ? $getWebSettings['instagram_id'] : '' }}">
                                            </div>

                                            {{-- Twitter ID --}}
                                            <label class=" form-label mt-2">{{ __('Twitter Id') }}</label>
                                            <div class="col-sm-12">
                                                <input name="twitter_id" type="text" class="form-control" placeholder="{{ __('Twitter Id') }}" value="{{ isset($getWebSettings['twitter_id']) && $getWebSettings['twitter_id'] != '' ? $getWebSettings['twitter_id'] : '' }}">
                                            </div>

                                            {{-- Youtube ID --}}
                                            <label class="form-label mt-2">{{ __('Youtube Id') }}</label>
                                            <div class="col-sm-12">
                                                <input name="youtube_id" type="text" class="form-control" placeholder="{{ __('Youtube Id') }}" value="{{ isset($getWebSettings['youtube_id']) && $getWebSettings['youtube_id'] != '' ? $getWebSettings['youtube_id'] : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider pt-3">
                                                <h6 class="divider-text">{{ __('More Settings') }}</h6>
                                            </div>

                                            <div class="form-group row mt-3">
                                                {{-- Facility Background --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-12 mt-2 ">{{ __('Facility Background') }}</label>
                                                    <input name="category_background" class="form-control color-picker" data-jscolor="{hash:true, alphaChannel:true}" value="{{ isset($getWebSettings['category_background']) && $getWebSettings['category_background'] != '' ? $getWebSettings['category_background'] : '#087c7c14' }}">
                                                </div>

                                                {{-- Sell Color --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-12 mt-2 ">{{ __('Sell Color') }}</label>
                                                    <input name="sell_web_color" class="form-control color-picker" data-jscolor="{hash:true, alphaChannel:true}" value="{{ isset($getWebSettings['sell_web_color']) && $getWebSettings['sell_web_color'] != '' ? $getWebSettings['sell_web_color'] : '#14B8DCFF' }}">
                                                </div>

                                                {{-- Sell Background --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-12 mt-2 ">{{ __('Sell Background') }}</label>
                                                    <input name="sell_web_background_color" class="form-control color-picker" data-jscolor="{hash:true, alphaChannel:true}" value="{{ isset($getWebSettings['sell_web_background_color']) && $getWebSettings['sell_web_background_color'] != '' ? $getWebSettings['sell_web_background_color'] : '#14B8DC1F' }}">
                                                </div>

                                                {{-- Rent Color --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-12 mt-2 ">{{ __('Rent Color') }}</label>
                                                    <input name="rent_web_color" class="form-control color-picker" data-jscolor="{hash:true, alphaChannel:true}" value="{{ isset($getWebSettings['rent_web_color']) && $getWebSettings['rent_web_color'] != '' ? $getWebSettings['rent_web_color'] : '#E48D18FF' }}">
                                                </div>

                                                {{-- Rent Background --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-12 mt-2 ">{{ __('Rent Background') }}</label>
                                                    <input name="rent_web_background_color" class="form-control color-picker" data-jscolor="{hash:true, alphaChannel:true}" value="{{ isset($getWebSettings['rent_web_background_color']) && $getWebSettings['rent_web_background_color'] != '' ? $getWebSettings['rent_web_background_color'] : '#E48D181F' }}">
                                                </div>

                                                {{-- Maintenance Mode --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-4 form-check-label mt-3">{{ __('Maintenance Mode') }}</label>
                                                    <div class="form-check form-switch ">
                                                        <input type="hidden" name="web_maintenance_mode" id="web_maintenance_mode" value="{{ isset($getWebSettings['web_maintenance_mode']) && $getWebSettings['web_maintenance_mode'] != '' ? $getWebSettings['web_maintenance_mode'] : 0 }}">
                                                        <input class="form-check-input" type="checkbox" role="switch" {{ isset($getWebSettings['web_maintenance_mode']) && $getWebSettings['web_maintenance_mode'] == '1' ? 'checked' : '' }} id="switch_maintenance_mode">
                                                        <label class="form-check-label mandatory" for="switch_maintenance_mode"></label>
                                                    </div>
                                                </div>

                                                {{-- Allow Cookies --}}
                                                <div class="col-md-6">
                                                    <label class="col-sm-4 form-check-label mt-3">{{ __('Allow Cookies') }}</label>
                                                    <div class="form-check form-switch ">
                                                        <input type="hidden" name="allow_cookies" id="allow-cookies" value="{{ isset($getWebSettings['allow_cookies']) && $getWebSettings['allow_cookies'] != '' ? $getWebSettings['allow_cookies'] : 0 }}">
                                                        <input class="form-check-input" type="checkbox" role="switch" {{ isset($getWebSettings['allow_cookies']) && $getWebSettings['allow_cookies'] == '1' ? 'checked' : '' }} id="switch-allow-cookies">
                                                        <label class="form-check-label mandatory" for="switch-allow-cookies"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" name="btnAdd1" value="btnAdd" id="btnAdd1" class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
            </div>
        </form>
    </section>
@endsection
@section('script')
    <script>
        $("#switch_maintenance_mode").on('change', function() {
            $("#switch_maintenance_mode").is(':checked') ? $("#web_maintenance_mode").val(1) : $("#web_maintenance_mode").val(0);
        });
        $("#switch-allow-cookies").on('change', function() {
            $("#switch-allow-cookies").is(':checked') ? $("#allow-cookies").val(1) : $("#allow-cookies").val(0);
        });
        $(document).on('click', '#web_logo', function(e) {
            $('.web_logo').hide();
        });
        $(document).on('click', '#web_favicon', function(e) {
            $('.web_favicon').hide();
        });
        $(document).on('click', '#web_placeholder_logo', function(e) {
            $('.web_placeholder_logo').hide();
        });
        $(document).on('click', '#web_footer_logo', function(e) {
            $('.web_footer_logo').hide();
        });


        $('#myForm').submit(function(event) {
            const iframeContent = $('#iframe_tag').val();
            // Create a temporary element to extract the src attribute
            const tempElement = $('<div>').html(iframeContent);
            // Get the src attribute value from the parsed HTML
            const srcValue = tempElement.find('iframe').attr('src');
            if (srcValue) {
                // Set the src value to a hidden element
                $('#iframe_link').val(srcValue);
            }else{
                $('#iframe_link').val(iframeContent);
            }
        });


        $('.web_logo_btn').click(function() {
            $('#web_logo').click();
        });
        web_logo.onchange = evt => {
            const [file] = web_logo.files
            if (file) {
                blah_web_logo.src = URL.createObjectURL(file)
            }
        }


        $('.btn_placeholder_logo').click(function() {
            $('#web_placeholder_logo').click();
        });
        web_placeholder_logo.onchange = evt => {
            const [file] = web_placeholder_logo.files
            if (file) {
                blah_placeholder_logo.src = URL.createObjectURL(file)
            }
        }


        $('.web_footer_logo_btn').click(function() {
            $('#web_footer_logo').click();
        });
        web_footer_logo.onchange = evt => {
            const [file] = web_footer_logo.files
            if (file) {
                blah_web_footer_logo.src = URL.createObjectURL(file)

            }
        }


        $('.web_favicon_btn').click(function() {
            $('#web_favicon').click();

        });
        web_favicon.onchange = evt => {
            const [file] = web_favicon.files
            if (file) {
                blah_web_favicon.src = URL.createObjectURL(file)
            }
        }
    </script>
@endsection
