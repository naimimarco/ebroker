@extends('layouts.main')
@section('title')
    {{ __('Update Property') }}
@endsection
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('property.index') }}" id="subURL">{{ __('View Property') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Update') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection
@section('content')
    {!! Form::open([
        'route' => ['property.update', $id],
        'method' => 'PATCH',
        'data-parsley-validate',
        'files' => true,
        'id' => 'myForm',
    ]) !!}

    <div class='row'>
        <div class='col-md-6'>

            <div class="card">

                <h3 class="card-header">{{ __('Details') }}</h3>
                <hr>

                {{-- Category --}}
                <div class="card-body">
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                        <select name="category" class="choosen-select form-select form-control-sm" data-parsley-minSelect='1' id="category" required='true'>
                            <option value="">{{ __('Choose Category') }}</option>
                            @foreach ($category as $row)
                                <option value="{{ $row->id }}"
                                    {{ $list->category_id == $row->id ? ' selected=selected' : '' }}
                                    data-parametertypes='{{ $row->parameter_types }}'> {{ $row->category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Title --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('title', isset($list->title) ? $list->title : '', ['class' => 'form-control ', 'placeholder' => __('Title'), 'required' => 'true', 'id' => 'title']) }}
                    </div>

                    {{-- Slug --}}
                    <div class="col-md-12 col-12 form-group">
                        {{ Form::label('slug', __('Slug'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('slug', isset($list->slug_id) ? $list->slug_id : '', [ 'class' => 'form-control ', 'placeholder' =>  __('Slug'), 'id' => 'slug', ]) }}
                        <small class="text-danger text-sm">{{ __("Only Small English Characters, Numbers And Hypens Allowed") }}</small>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::textarea('description', isset($list->description) ? $list->description : '', ['class' => 'form-control mb-3', 'rows' => '3', 'id' => '', 'required' => 'true', 'placeholder' => __('Description')]) }}
                    </div>

                    {{-- Property Type --}}
                    <div class="col-md-12 col-12  form-group  mandatory">
                        <div class="row">
                            {{ Form::label('', __('Property Type'), ['class' => 'form-label col-12 ']) }}

                            {{-- For Sell --}}
                            <div class="col-md-6">
                                {{ Form::radio('property_type', 0, null, ['class' => 'form-check-input', 'id' => 'property_type', 'required' => true, isset($list->propery_type) && $list->getRawOriginal('propery_type') == 0 ? 'checked' : '']) }}
                                {{ Form::label('property_type', __('For Sell'), ['class' => 'form-check-label']) }}
                            </div>

                            {{-- For Rent --}}
                            <div class="col-md-6">
                                {{ Form::radio('property_type', 1, null, ['class' => 'form-check-input', 'id' => 'property_type', 'required' => true, isset($list->propery_type) && $list->getRawOriginal('propery_type') == 1 ? 'checked' : '']) }}
                                {{ Form::label('property_type', __('For Rent'), ['class' => 'form-check-label']) }}
                            </div>
                        </div>
                    </div>


                    {{-- When Rent Selected Then Show Duration For Price --}}
                    <div class="col-md-12 col-12 form-group mandatory" id='duration'>
                        {{ Form::label('Duration', __('Duration For Price'), ['class' => 'form-label col-12 ']) }}
                        <select name="price_duration" id="price_duration"class="choosen-select form-select form-control-sm" data-parsley-minSelect='1'>
                            <option value="">{{ __("Select Duration") }}</option>
                            <option value="Daily" {{ $list->rentduration == 'Daily' ? 'selected' : '' }}> {{ __('Daily') }} </option>
                            <option value="Monthly" {{ $list->rentduration == 'Monthly' ? 'selected' : '' }}> {{ __('Monthly') }} </option>
                            <option value="Yearly" {{ $list->rentduration == 'Yearly' ? 'selected' : '' }}> {{ __('Yearly') }} </option>
                            <option value="Quarterly" {{ $list->rentduration == 'Quarterly' ? 'selected' : '' }}> {{ __('Quarterly') }} </option>
                        </select>
                    </div>

                    {{-- Price --}}
                    <div class="control-label col-12 form-group mandatory">
                        {{ Form::label('price', __('Price') . '(' . $currency_symbol . ')', ['class' => 'form-label col-12 ']) }}
                        {{ Form::number('price', isset($list->price) ? $list->price : '', ['class' => 'form-control ', 'placeholder' => __('Price'), 'required' => 'true', 'min' => '1', 'max' => '9223372036854775807', 'id' => 'price']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class='col-md-6'>

            <div class="card">
                <h3 class="card-header">{{ __('SEO Details') }}</h3>
                <hr>
                <div class="row card-body">

                    {{-- Meta Title --}}
                    <div class="col-md-6 col-sm-12 form-group">
                        {{ Form::label('title', __('Meta Title'), ['class' => 'form-label text-center']) }}
                        <textarea id="edit_meta_title" name="edit_meta_title" class="form-control" oninput="getWordCount('edit_meta_title','edit_meta_title_count','12.9px arial')" rows="2" {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }} style="height: 75px" placeholder="{{ __('Meta Title') }}">{{ $list->meta_title }}</textarea>
                        <br>
                        <h6 id="edit_meta_title_count">0</h6>
                    </div>

                    {{-- Meta Image --}}
                    <div class="col-md-6 col-sm-12 form-group card">
                        {{ Form::label('title', __('Meta Image'), ['class' => 'form-label text-center']) }}
                        <input type="file" name="meta_image" id="meta_image">

                        {{-- Meta Image Show --}}
                        @if($list->meta_image != "")
                            <div class="col-md-2 col-sm-12 text-center">
                                <img src="{{ $list->meta_image }}" alt="" height="100px" width="100px">
                            </div>
                        @endif
                    </div>

                    {{-- Meta Description --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('description', __('Meta Description'), ['class' => 'form-label text-center']) }}
                        <textarea id="edit_meta_description" name="edit_meta_description" class="form-control" oninput="getWordCount('edit_meta_description','edit_meta_description_count','12.9px arial')" rows="3" placeholder="{{ __('Meta Description') }}">{{ $list->meta_description }}</textarea>
                        <br>
                        <h6 id="edit_meta_description_count">0</h6>
                    </div>

                    {{-- Meta Keywords --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('keywords', __('Meta Keywords'), ['class' => 'form-label']) }}
                        <textarea name="Keywords" id="" class="form-control" rows="3" placeholder="{{ __('Meta Keywords') }}">{{ $list->meta_keywords }}</textarea>
                        ({{ __('Add Comma Separated Keywords') }})
                    </div>
                </div>

            </div>
        </div>

        {{-- Outdoor Facility --}}
        <div class="col-md-12" id="outdoor_facility">
            <div class="card">
                <h3 class="card-header">{{ __('Near By Places') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        @foreach ($facility as $key => $value)
                            <div class='col-md-3  form-group'>
                                {{ Form::label('description', $value->name, ['class' => 'form-check-label']) }}
                                @if (count($value->assign_facilities))
                                    {{ Form::number('facility' . $value->id, $value->assign_facilities[0]['distance'], ['class' => 'form-control mt-3', 'placeholder' => trans('Distance').' ('.$distanceValue.')', 'id' => 'dist' . $value->id,'min' => 0, 'max' => 99999999.9,'step' => '0.1']) }}
                                @else
                                    {{ Form::number('facility' . $value->id, '', ['class' => 'form-control mt-3', 'placeholder' => trans('Distance').' ('.$distanceValue.')', 'id' => 'dist' . $value->id,'min' => 0, 'max' => 99999999.9 ,'step' => '0.1']) }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Facility --}}
        <div class="col-md-12" id="facility">
            <div class="card">
                <h3 class="card-header">{{ __('Facilities') }}</h3>
                <hr>
                {{ Form::hidden('category_count[]', $category, ['id' => 'category_count']) }}
                {{ Form::hidden('parameter_count[]', $parameters, ['id' => 'parameter_count']) }}
                {{ Form::hidden('parameter_add', '', ['id' => 'parameter_add']) }}
                <div id="parameter_type" name=parameter_type class="row card-body">
                    @foreach ($edit_parameters as $res)
                        @if($res->is_required == 1)
                            @if ($res->type_of_parameter == 'file')
                                @if (!empty($res->assigned_parameter->value))
                                @endif
                            @endif
                            <div class="col-md-3 form-group mandatory">
                        @else
                            <div class="col-md-3 form-group">
                        @endif
                            {{ Form::label($res->name, $res->name, ['class' => 'form-label col-12']) }}

                            {{-- DropDown --}}
                            @if ($res->type_of_parameter == 'dropdown')
                                <select name="{{ 'par_' . $res->id }}" class="choosen-select form-select form-control-sm" selected="false" {{ $res->is_required == 1 ? 'required' : '' }} >
                                    <option value=""></option>
                                    @foreach ($res->type_values as $key => $value)
                                        <option value="{{ $value }}"
                                            {{ $res->assigned_parameter && $res->assigned_parameter->value == $value ? ' selected=selected' : '' }}>
                                            {{ $value }} </option>
                                    @endforeach
                                </select>
                            @endif

                            {{-- Radio Button --}}
                            @if ($res->type_of_parameter == 'radiobutton')
                                @foreach ($res->type_values as $key => $value)
                                    <input type="radio" name="{{ 'par_' . $res->id }}" id="" value={{ $value }} class="form-check-input" {{ $res->assigned_parameter && $res->assigned_parameter->value == $value ? 'checked' : '' }} {{ $res->is_required == 1 ? 'required' : '' }} >
                                    {{ $value }}
                                @endforeach
                            @endif

                            {{-- Number --}}
                            @if ($res->type_of_parameter == 'number')
                                <input type="number" name="{{ 'par_' . $res->id }}" id="" class="form-control" value="{{ $res->assigned_parameter  && $res->assigned_parameter != 'null' ? $res->assigned_parameter->value : '' }}" {{ $res->is_required == 1 ? 'required' : '' }}>
                            @endif

                            {{-- TextBox --}}
                            @if ($res->type_of_parameter == 'textbox')
                                <input type="text" name="{{ 'par_' . $res->id }}" id="" class="form-control" value="{{ $res->assigned_parameter && $res->assigned_parameter->value != 'null' ? $res->assigned_parameter->value : '' }}" {{ $res->is_required == 1 ? 'required' : '' }}>
                            @endif

                            {{-- TextArea --}}
                            @if ($res->type_of_parameter == 'textarea')
                                <textarea name="{{ 'par_' . $res->id }}" id="" class="form-control" cols="30" rows="3" value="{{ $res->assigned_parameter && $res->assigned_parameter->value != 'null' ? $res->assigned_parameter->value : '' }}" {{ $res->is_required == 1 ? 'required' : '' }}>{{ $res->assigned_parameter && $res->assigned_parameter->value != 'null' ? $res->assigned_parameter->value : '' }}</textarea>
                            @endif

                            {{-- CheckBox --}}
                            @if ($res->type_of_parameter == 'checkbox')
                                @foreach ($res->type_values as $key => $value)
                                    <input type="checkbox" name="{{ 'par_' . $res->id . '[]' }}" id="" class="form-check-input" value={{ $value }} {{ !empty($res->assigned_parameter->value) && in_array($value, $res->assigned_parameter->value) ? 'Checked' : '' }} {{ $res->is_required == 1 ? 'required' : '' }}>{{ $value }}
                                @endforeach
                            @endif

                            {{-- FILE --}}
                            @if ($res->type_of_parameter == 'file')
                                @if (!empty($res->assigned_parameter->value))
                                    <a href="{{ url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' . $res->assigned_parameter->value }}" class="text-center col-12" style="text-align: center"> Click here to View</a> OR
                                    <input type="file" class='form-control' name="{{ 'par_' . $res->id }}" id='edit_param_img'>
                                @else
                                    <input type="file" class='form-control' name="{{ 'par_' . $res->id }}" id='edit_param_img' {{ $res->is_required == 1 ? 'required' : '' }}>
                                @endif
                                <input type="hidden" name="{{ 'par_' . $res->id }}" value="{{ $res->assigned_parameter ? $res->assigned_parameter->value : '' }}">
                            @endif
                        </div>
                        {{-- @endforeach --}}
                    @endforeach
                </div>
            </div>
        </div>
        <div class='col-md-12'>

            <div class="card">
                <h3 class="card-header">{{ __('Location') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        {{-- Google Map --}}
                        <div class='col-md-6'>
                            {{-- Map View --}}
                            <div class="card col-md-12" id="map" style="height: 90%">
                                <!-- Google map -->
                            </div>
                        </div>

                        {{-- Details of Map --}}
                        <div class='col-md-6'>
                            <div class="row">

                                {{-- City --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::hidden('city', isset($list->city) ? $list->city : '', ['class' => 'form-control ', 'id' => 'city']) !!}
                                    <input id="searchInput" value="{{ isset($list->city) ? $list->city : '' }}"  class="controls form-control" type="text" placeholder="{{ __('City') }}" required>
                                    {{-- {{ Form::text('city', isset($list->city) ? $list->city : '', ['class' => 'form-control ', 'placeholder' => 'City', 'id' => 'city']) }} --}}
                                </div>

                                {{-- Country --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('country', isset($list->country) ? $list->country : '', ['class' => 'form-control ', 'placeholder' => trans('Country'), 'id' => 'country', 'required' => true]) }}
                                </div>

                                {{-- State --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('state', isset($list->state) ? $list->state : '', ['class' => 'form-control ', 'placeholder' => trans('State'), 'id' => 'state', 'required' => true]) }}
                                </div>


                                {{-- Latitude --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('latitude', isset($list->latitude) ? $list->latitude : '', ['class' => 'form-control ', 'id' => 'latitude', 'step' => 'any', 'readonly' => true, 'required' => true, 'placeholder' => trans('Latitude')]) !!}
                                </div>

                                {{-- Longitude --}}
                                <div class="col-md-6 form-group  mandatory">
                                    {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('longitude', isset($list->longitude) ? $list->longitude : '', ['class' => 'form-control ', 'id' => 'longitude', 'step' => 'any', 'readonly' => true, 'required' => true, 'placeholder' => trans('Longitude')]) !!}
                                </div>

                                {{-- Client Address --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('address', __('Client Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('client_address', isset($list->client_address) ? $list->client_address : (system_setting('company_address') ?? ""), ['class' => 'form-control ', 'placeholder' => trans('Client Address'), 'rows' => '4', 'id' => 'client-address', 'autocomplete' => 'off', 'required' => 'true']) }}
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('address', isset($list->address) ? $list->address : '', ['class' => 'form-control ', 'placeholder' => trans('Address'), 'rows' => '4', 'id' => 'address', 'autocomplete' => 'off', 'required' => 'true']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Images') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        {{-- Title Image --}}
                        <div class="col-md-3 col-sm-12 form-group mandatory card title_card">
                            {{ Form::label('filepond_title', __('Title Image'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond_title" name="title_image" {{ $list->title_image == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                            @if ($list->title_image)
                                <div class="card1 title_img mt-2">
                                    <img src="{{ $list->title_image }}" alt="Image" class="card1-img">
                                </div>
                            @endif
                        </div>

                        {{-- 3D Image --}}
                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('filepond_3d', __('3D Image'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond_3d" name="3d_image">
                            @if ($list->three_d_image)
                                <div class="card1 3d_img">
                                    <img src="{{ $list->three_d_image }}" alt="Image" class="card1-img" id="3d_img">
                                    <button data-id="{{ $list->id }}" data-url="{{ route('property.remove-threeD-image',$list->id) }}" class="RemoveBtn1 removeThreeDImage">x</button>
                                </div>
                            @endif
                        </div>

                        {{-- Gallary Images --}}
                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('filepond2', __('Gallary Images'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" accept="image/jpg,image/png,image/jpeg" id="filepond2" name="gallery_images[]" multiple>
                            <div class="row mt-0">
                                <?php $i = 0; ?>
                                @if (!empty($list->gallery))
                                    @foreach ($list->gallery as $row)
                                        <div class="col-md-6 col-sm-12" id='{{ $row->id }}'>
                                            <div class="card1" style="height:10vh;">
                                                <img src="{{ url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $list->id . '/' . $row->image }}"
                                                    alt="Image" class="card1-img">
                                                <button data-id="{{ $row->id }}"
                                                    class="RemoveBtn1 RemoveBtngallary">x</button>
                                            </div>
                                        </div>

                                        <?php $i++; ?>
                                    @endforeach
                                @endif
                            </div>
                        </div>



                        {{-- Documents Images --}}
                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('edit-documents', __('Documents'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="doc-filepond" id="edit-documents" name="documents[]" multiple>
                            <div class="row mt-0 stored-documents-div">
                                @if (!empty($list->documents))
                                    @foreach ($list->documents as $row)
                                        <div class="properties_docs_main_div">
                                            <div class="doc_icon">
                                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M208 64h66.75a32 32 0 0122.62 9.37l141.26 141.26a32 32 0 019.37 22.62V432a48 48 0 01-48 48H192a48 48 0 01-48-48V304"></path><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M288 72v120a32 32 0 0032 32h120"></path><path fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M160 80v152a23.69 23.69 0 01-24 24c-12 0-24-9.1-24-24V88c0-30.59 16.57-56 48-56s48 24.8 48 55.38v138.75c0 43-27.82 77.87-72 77.87s-72-34.86-72-77.87V144"></path></svg>
                                            </div>
                                            <div class="doc_title">
                                                <a href="{{ $row->file }}" target="_blank"><span title="{{ $row->file_name }}"> {{ $row->file_name }} </span></a>
                                            </div>
                                            <div>
                                                <button class="btn btn-danger btn-sm removeDocument" data-id={{ $row->id }}>X</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            {{ Form::label('video_link', __('Video Link'), ['class' => 'form-label col-12 ']) }}
                            {{ Form::text('video_link', isset($list->video_link) ? $list->video_link : '', ['class' => 'form-control ', 'placeholder' => trans('Video Link'), 'id' => 'address', 'autocomplete' => 'off']) }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Accesibility') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="col-sm-12 col-md-12  col-xs-12 d-flex">
                        <label class="col-sm-1 form-check-label mandatory mt-3 ">{{ __('Is Private?') }}</label>
                        <div class="form-check form-switch mt-3">
                            <input type="hidden" name="is_premium" id="is_premium" value=" {{ $list->is_premium ? 1 : 0 }}">
                            <input class="form-check-input" type="checkbox" role="switch" {{ $list->is_premium ? 'checked' : '' }} id="is_premium_switch">
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class='col-md-12 d-flex justify-content-end mb-3'>
            <input type="submit" class="btn btn-primary" value="{{ __('Save') }}">
            &nbsp;
            &nbsp;
            <button class="btn btn-secondary reset-form" type="button">{{ __('Reset') }}</button>
        </div>
        {!! Form::close() !!}

    </div>
@endsection
@section('script')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key={{ env('PLACE_API_KEY') }}&callback=initMap" async defer></script>
    <script>
        function initMap() {
            var latitude = parseFloat($('#latitude').val());
            var longitude = parseFloat($('#longitude').val());
            var map = new google.maps.Map(document.getElementById('map'), {

                center: {
                    lat: latitude,
                    lng: longitude
                },


                zoom: 13
            });
            var marker = new google.maps.Marker({
                position: {
                    lat: latitude,
                    lng: longitude
                },
                map: map,
                draggable: true,
                title: 'Marker Title'
            });
            google.maps.event.addListener(marker, 'dragend', function(event) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'latLng': event.latLng
                }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            var address_components = results[0].address_components;
                            var city, state, country, full_address;

                            for (var i = 0; i < address_components.length; i++) {
                                var types = address_components[i].types;
                                if (types.indexOf('locality') != -1) {
                                    city = address_components[i].long_name;
                                } else if (types.indexOf('administrative_area_level_1') != -1) {
                                    state = address_components[i].long_name;
                                } else if (types.indexOf('country') != -1) {
                                    country = address_components[i].long_name;
                                }
                            }

                            full_address = results[0].formatted_address;

                            // Do something with the city, state, country, and full address
                            $('#searchInput').val(city);
                            $('#city').val(city);
                            $('#country').val(country);
                            $('#state').val(state);
                            $('#address').val(full_address);
                            $('#latitude').val(event.latLng.lat());
                            $('#longitude').val(event.latLng.lng());

                        } else {
                            console.log('No results found');
                        }
                    } else {
                        console.log('Geocoder failed due to: ' + status);
                    }
                });
            });
            var input = document.getElementById('searchInput');
            // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });
            autocomplete.addListener('place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setIcon(({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                var address = '';
                if (place.address_components) {
                    address = [
                        (place.address_components[0] && place.address_components[0].short_name || ''),
                        (place.address_components[1] && place.address_components[1].short_name || ''),
                        (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
                }

                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);

                // Location details
                for (var i = 0; i < place.address_components.length; i++) {
                    console.log(place);

                    if (place.address_components[i].types[0] == 'locality') {
                        $('#city').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'country') {
                        $('#country').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'administrative_area_level_1') {
                        console.log(place.address_components[i].long_name);
                        $('#state').val(place.address_components[i].long_name);


                    }
                }
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();
                $('#address').val(place.formatted_address);
                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());
            });
        }

        $(document).ready(function() {
            $('.reset-form').on('click',function(e){
                e.preventDefault();
                $('#myForm')[0].reset();
            });
            if ($('input[name="property_type"]:checked').val() == 0) {
                $('#duration').hide();
                $('#price_duration').removeAttr('required');
                $('#price_duration').val('');
            } else {
                $('#duration').show();
                $('#price_duration').attr('required', 'true');
                $('#price_duration').val('');
            }
            getWordCount("edit_meta_title", "edit_meta_title_count", "19.9px arial");
            getWordCount("edit_meta_description", "edit_meta_description_count", "12.9px arial");

        });
        $('input[name="property_type"]').change(function() {
            // Get the selected value
            var selectedType = $('input[name="property_type"]:checked').val();

            // Perform actions based on the selected value

            if (selectedType == 1) {
                $('#duration').show();
                $('#price_duration').attr('required', 'true');
            } else {
                $('#duration').hide();
                $('#price_duration').removeAttr('required');
            }
        });
        $(".RemoveBtngallary").click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Swal.fire({
                title: window.trans['Are you sure you wants to remove this document ?'],
                icon: 'error',
                showDenyButton: true,
                confirmButtonText: window.trans['Yes'],
                denyCanceButtonText: window.trans['No'],
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('property.removeGalleryImage') }}",

                        type: "POST",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            "id": id
                        },
                        success: function(response) {

                            if (response.error == false) {
                                Toastify({
                                    text: 'Image Delete Successful',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                                }).showToast();
                                $("#" + id).html('');
                            } else if (response.error == true) {
                                Toastify({
                                    text: 'Something Wrong !!!',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                                }).showToast()
                            }
                        },
                        error: function(xhr) {}
                    });
                }
            })

        });
        $(document).on('click', '#filepond_3d', function(e) {

            $('.3d_img').hide();
        });
        $(document).on('click', '#filepond_title', function(e) {

            $('.title_img').hide();
        });
        jQuery(document).ready(function() {
            initMap();

            $('#map').append('<iframe src="https://maps.google.com/maps?q=' + $('#latitude').val() + ',' + $(
                    '#longitude').val() +
                '&hl=en&amp;z=18&amp;output=embed" height="375px" width="800px"></iframe>');
        });
        $(document).ready(function() {
            $('.parsley-error filled,.parsley-required').attr("aria-hidden", "true");
            $('.parsley-error filled,.parsley-required').hide();

        });
        $(document).ready(function() {



            $("#is_premium_switch").on('change', function() {
                $("#is_premium_switch").is(':checked') ? $("#is_premium").val(1) : $(
                        "#is_premium")
                    .val(0);
            });

            FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
                FilePondPluginFileValidateType);

            $('#meta_image').filepond({
                credits: null,
                allowFileSizeValidation: "true",
                maxFileSize: '5000KB',
                labelMaxFileSizeExceeded: 'File is too large',
                labelMaxFileSize: 'Maximum file size is {filesize}',
                allowFileTypeValidation: true,
                acceptedFileTypes: ['image/*'],
                labelFileTypeNotAllowed: 'File of invalid type',
                fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
                storeAsFile: true,

            });
        });
        $("#title").on('keyup',function(e){
            let title = $(this).val();
            let id = "{{ $id }}";
            let slugElement = $("#slug");
            if(title){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('property.generate-slug') }}",
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        title: title,
                        id: id
                    },
                    beforeSend: function() {
                        slugElement.attr('readonly', true).val('Please wait....')
                    },
                    success: function(response) {
                        if(!response.error){
                            if(response.data){
                                slugElement.removeAttr('readonly').val(response.data);
                            }else{
                                slugElement.removeAttr('readonly').val("")
                            }
                        }
                    }
                });
            }else{
                slugElement.removeAttr('readonly', true).val("")
            }
        });




        $(".removeDocument").click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Swal.fire({
                title: window.trans['Are you sure you wants to remove this document ?'],
                icon: 'error',
                showDenyButton: true,
                confirmButtonText: window.trans['Yes'],
                denyCanceButtonText: window.trans['No'],
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('property.remove-documents') }}",
                        type: "POST",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            "id": id
                        },
                        success: function(response) {
                            if (response.error == false) {
                                Toastify({
                                    text: window.trans['Document Deleted Successfully'],
                                    duration: 1500,
                                    close: !0,
                                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                                }).showToast();

                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);

                                $("#" + id).html('');
                            } else if (response.error == true) {
                                Toastify({
                                    text: window.trans['Something Went Wrong'],
                                    duration: 5000,
                                    close: !0,
                                    backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                                }).showToast()
                            }
                        },
                        error: function(xhr) {}
                    });
                }
            })

        });


        $(".removeThreeDImage").on('click',function(e){
            e.preventDefault();
            let url = $(this).data('url');
            showDeletePopupModal(url,{
                successCallBack: function () {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }, errorCallBack: function (response) {
                    showErrorToast(response.message);
                }
            })
        })
    </script>
@endsection
