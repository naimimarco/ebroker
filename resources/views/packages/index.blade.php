@extends('layouts.main')

@section('title')
    {{ __('Packages') }}
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
        <div class="row">
            @if (has_permissions('create', 'package'))
                <div class="col-12">
                    <div class="card">

                        <div class="card">
                            {!! Form::open(['route' => 'package.store', 'data-parsley-validate', 'class' => 'create-form', 'data-success-function'=> "formSuccessFunction"]) !!}
                            <div class="card-body">
                                <div class="row ">
                                    {{-- Package Name --}}
                                    <div class="col-md-6 col-lg-4 form-group mandatory">
                                        {{ Form::label('name', __('Package Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('name', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Package Name'),
                                            'data-parsley-required' => 'true',
                                            'id' => 'name',
                                        ]) }}
                                    </div>

                                    {{-- Duration --}}
                                    <div class="col-md-6 col-lg-4 form-group mandatory">
                                        {{ Form::label('duration', __('Duration (In Days)'), ['class' => 'form-label']) }}
                                        {{ Form::number('duration', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Duration (In Days)'),
                                            'data-parsley-required' => 'true',
                                            'id' => 'duration',
                                            'min' => '1',
                                        ]) }}
                                    </div>

                                    {{-- Package Type --}}
                                    <div class="col-md-6 col-lg-4 form-group mandatory">
                                        {{ Form::label('', __('Package Type'), ['class' => 'form-label col-12 ']) }}

                                        {{-- Paid --}}
                                        {{ Form::radio('package_type', 'paid', null, [ 'class' => 'form-check-input package-type', 'data-parsley-required' => 'true', 'id' => 'package-type-paid', 'checked' => true ]) }}
                                        {{ Form::label('package-type-paid', __('Paid'), ['class' => 'form-check-label']) }}

                                        {{-- Free --}}
                                        {{ Form::radio('package_type', 'free', null, [ 'class' => 'form-check-input package-type', 'data-parsley-required' => 'true', 'id' => 'package-type-free' ]) }}
                                        {{ Form::label('package-type-free', __('Free'), ['class' => 'form-check-label']) }}
                                    </div>


                                    {{-- Price --}}
                                    <div class="col-md-6 col-lg-4 form-group mandatory price-div">
                                        {{ Form::label('price', __('Price') . '(' . $currency_symbol . ')', [ 'class' => 'form-label']) }}
                                        {{ Form::number('price', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Price'),
                                            'id' => 'price',
                                            'data-parsley-required' => 'true',
                                            'min' => '0.01',
                                            'step' => '0.01'
                                        ])}}
                                    </div>

                                    {{-- IOS Product ID --}}
                                    <div class="col-md-6 col-lg-4 form-group" id="ios-product-id-div">
                                        {{ Form::label('ios-product-id', __('IOS Product ID'), ['class' => 'form-label']) }}
                                        {{ Form::text('ios_product_id', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('IOS Product ID'),
                                            'id' => 'ios-product-id',
                                        ]) }}
                                    </div>
                                </div>

                                <hr>
                                {{-- Feature Section --}}
                                <div class="feature-sections">
                                    <div class="mt-4" data-repeater-list="feature_data">
                                        <div class="col-md-5 pl-0 mb-4">
                                            <button type="button" class="btn btn-success add-new-feature" data-repeater-create title="Add new row">
                                                <span><i class="fa fa-plus"></i> {{__('Add New Feature')}}</span>
                                            </button>
                                        </div>
                                        <div class="row feature-section" data-repeater-item>
                                            {{-- Select Feature --}}
                                            <div class="form-group mandatory col-md-6 col-lg-4">
                                                <label>{{ __('Select Feature') }} <span class="text-danger">*</span></label>
                                                <select name="feature_id" class="form-control form-select features" required>
                                                    <option value="">{{trans('Select Option')}}</option>
                                                    @foreach ($featuresList as $feature)
                                                        <option value="{{$feature->id}}"> {{$feature->name}} </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Type --}}
                                            <div class="form-group mandatory col-md-6 col-lg-3 package-types">
                                                {{ Form::label('', __('Type'), ['class' => 'form-label col-12 ']) }}

                                                {{-- Unlimited --}}
                                                {{ Form::radio('type', 'unlimited', null, [ 'class' => 'form-check-input feature-type feature-type-unlimited', 'required' => true ]) }}
                                                {{ Form::label('', __('Unlimited'), ['class' => 'form-check-label feature-type-unlimited-label']) }}

                                                {{-- Limited --}}
                                                {{ Form::radio('type', 'limited', null, [ 'class' => 'form-check-input feature-type feature-type-limited', 'required' => true ]) }}
                                                {{ Form::label('', __('Limited'), ['class' => 'form-check-label feature-type-limited-label']) }}
                                            </div>

                                            {{-- Limited Value --}}
                                            <div class="col-md-5 col-lg-4 form-group mandatory limit-div" style="display: none">
                                                {{ Form::label('', __('Limited'), ['class' => 'form-label']) }}
                                                {!! Form::text('limit', '', ['min' => 1, 'class' => 'form-control limit', 'placeholder' => trans('Enter your limit')]) !!}
                                            </div>

                                            {{-- Remove Option --}}
                                            <div class="form-group col-md-1 pl-0 mt-4">
                                                <button data-repeater-delete type="button" class="btn btn-icon btn-danger remove-default-option" title="{{__('Remove Option')}}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>

                                <div class="col-12 form-group mt-2">
                                    {{ Form::submit(trans('Add Package'), ['class' => 'center btn btn-primary']) }}
                                </div>

                            </div>
                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            @endif
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-striped"
                                    id="table_list" data-toggle="table" data-url="{{ route('package.show',1) }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-search-align="right" data-toolbar="#toolbar" data-show-columns="true"
                                    data-show-refresh="true" data-trim-on-search="false" data-responsive="true"
                                    data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3"
                                    data-query-params="queryParams">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                            <th scope="col" data-field="ios_product_id" data-align="center" data-sortable="true"> {{ __('IOS Product ID') }} </th>
                                            <th scope="col" data-field="name" data-align="center" data-sortable="true"> {{ __('Name') }} </th>
                                            <th scope="col" data-field="duration" data-align="center" data-sortable="false"> {{ __('Duration (In Days)') }}</th>
                                            <th scope="col" data-field="package_type" data-align="center" data-sortable="false" data-formatter="packageTypeFormatter"> {{ __('Package Type') }} </th>
                                            <th scope="col" data-field="price" data-align="center" data-sortable="false" data-formatter="packagePriceFormatter"> {{ __('Price') }} </th>
                                            <th scope="col" data-field="package_features" data-sortable="false" data-formatter="packageFeaturesFormatter"> {{ __('Features') }} </th>
                                            @if (has_permissions('update', 'package'))
                                                <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                                <th scope="col" data-field="operate" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Action') }}</th>
                                            @else
                                                <th scope="col" data-field="status" data-sortable="true" data-align="center" data-width="5%" data-formatter="yesNoStatusFormatter"> {{ __('Is Active ?') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- EDIT MODEL MODEL -->
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Package') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ url('package') }}" class="edit-form" enctype="multipart/form-data" method="POST">
                        <div class="modal-body">
                            {{ csrf_field() }}
                            <input type="hidden" id="edit-id" name="edit_id">
                            <div class="row">
                                 {{-- Package Name --}}
                                 <div class="col-12 form-group mandatory">
                                    {{ Form::label('edit-name', __('Package Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('name', '', [
                                        'class' => 'form-control ',
                                        'placeholder' => trans('Package Name'),
                                        'data-parsley-required' => 'true',
                                        'id' => 'edit-name',
                                    ]) }}
                                </div>

                                {{-- IOS Product ID --}}
                                <div class="col-12 form-group">
                                    {{ Form::label('edit-ios-product-id', __('IOS Product ID'), ['class' => 'form-label']) }}
                                    {{ Form::text('ios_product_id', '', [
                                        'class' => 'form-control ',
                                        'placeholder' => trans('IOS Product ID'),
                                        'id' => 'edit-ios-product-id',
                                    ]) }}
                                </div>

                                {{-- Duration --}}
                                <div class="col-12 form-group mandatory">
                                    {{ Form::label('edit-duration', __('Duration (In Days)'), ['class' => 'form-label']) }}
                                    {{ Form::number('duration', '', [
                                        'class' => 'form-control ',
                                        'placeholder' => trans('Duration (In Days)'),
                                        'data-parsley-required' => 'true',
                                        'id' => 'edit-duration',
                                        'min' => '1',
                                    ]) }}
                                </div>
                                {{-- Price --}}
                                <div class="col-12 form-group mandatory" id="edit-price-div">
                                    {{ Form::label('edit-price', __('Price') . '(' . $currency_symbol . ')', [ 'class' => 'form-label']) }}
                                    {{ Form::number('price', '', [
                                        'class' => 'form-control ',
                                        'placeholder' => trans('Price'),
                                        'id' => 'edit-price',
                                        'data-parsley-required' => 'true',
                                        'min' => '0',
                                        'step' => '0.01'
                                    ])}}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- EDIT MODEL -->
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('.package-type').on('click',function(e){
                $("#price").val("");
                $("#ios-product-id").val("");
                if($(this).val() == 'paid'){
                    // Show Price Div
                    $('.price-div').show();
                    $("#price").attr('data-parsley-required',true);
                    $("#price").val("");

                    // Show IOS Product ID Div
                    $("#ios-product-id-div").show();
                }else{
                    // Hide Price Div
                    $("#price").removeAttr('data-parsley-required');
                    $('.price-div').hide();
                    // Hide IOS Product ID Div
                    $("#ios-product-id-div").hide();
                }
            })
            $(".add-new-feature").trigger('click');
            checkDuplicateFeatures('.features','.add-new-feature'); // Check duplicates after adding a new row

        });
        $(document).on('change','.features',function (e) {
            let value = $(this).val();
            checkDuplicateFeatures('.features','.add-new-feature'); // Check duplicates after adding a new row
            // Get Feature ID of mortgage, premium properties and project access
            let mortgageCalculatorText = "{{$featureMapData[config('constants.FEATURES.MORTGAGE_CALCULATOR_DETAIL')] ?? ''}}";
            let premiumPropertiesText = "{{$featureMapData[config('constants.FEATURES.PREMIUM_PROPERTIES')] ?? ''}}";
            let projectAccessText = "{{$featureMapData[config('constants.FEATURES.PROJECT_ACCESS')] ?? ''}}";

            // Get limit Radio Elements
            let limitRadioElement = $(this).parent().parent().find('.package-types').find('.feature-type-limited');
            let limitRadioLabelElement = $(this).parent().parent().find('.package-types').find('.feature-type-limited-label');

            // Get unlimited Radio Elements
            let unlimitedRadioElement = $(this).parent().parent().find('.package-types').find('.feature-type-unlimited');
            unlimitedRadioElement.click()

            // IF there is mortgage or premium properties or project access type then hide Limit Element with label
            if(value == mortgageCalculatorText || value == premiumPropertiesText || value == projectAccessText){
                limitRadioElement.removeAttr('required').hide()
                limitRadioLabelElement.hide()
            }else{
                limitRadioElement.attr('required',true).show()
                limitRadioLabelElement.show()
            }

        });
        $(document).on('change','.feature-type',function (e) {
            let value = $(this).val();
            if(value == 'limited'){
                $(this).parent().parent().find('.limit-div').show()
                $(this).parent().parent().find('.limit-div').find('.limit').attr('data-parsley-required',true)
            }else{
                $(this).parent().parent().find('.limit-div').find('.limit').removeAttr('data-parsley-required')
                $(this).parent().parent().find('.limit-div').hide()
            }

        })
        function queryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,

            };
        }

        function chk(checkbox) {
            if (checkbox.checked) {

                active(event.target.id);

            } else {

                disable(event.target.id);
            }
        }
        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit-id").val(row.id);
                $("#edit-name").val(row.name);
                $("#edit-duration").val(row.duration);
                if(row.package_type == 'paid'){
                    // Show Price Div
                    $("#edit-price-div").show();
                    $('#edit-price').val(row.price).attr('data-parsley-required',true)
                    // Show IOS Product ID Div
                    $("#edit-ios-product-id-div").show();
                    $("#edit-ios-product-id").val(row.ios_product_id);
                }else{
                    // Hide Price Div
                    $('#edit-price').val("").removeAttr('data-parsley-required')
                    $("#edit-price-div").hide();
                    // Hide IOS Product ID Div
                    $("#edit-ios-product-id-div").hide();
                    $("#edit-ios-product-id").val("");
                }
            }
        }
    </script>

    <script>
        window.onload = function() {

            $('#limitation_for_property').hide();

            $('#limitation_for_advertisement').hide();
            $('.limitations').hide();

        }


        $('input[type="radio"][name="package_type"]').click(function() {
            if ($(this).is(':checked')) {
                if ($(this).val() == 'product_listing') {
                    $('.limitations').show();
                } else {
                    $('.limitations').hide();

                }
            }

        });

        $('input[type="radio"][name="typep"]').click(function() {


            if ($(this).is(':checked')) {
                if ($(this).val() == 'add_limited_property') {
                    $('#limitation_for_property').show();
                    $('#propertylimit').attr('required', 'true');
                } else {
                    $('#limitation_for_property').hide();
                    $('#propertylimit').removeAttr('required');
                }
            }
        });
        $('input[type="radio"][name="typel"]').click(function() {

            if ($(this).is(':checked')) {
                if ($(this).val() == 'add_limited_advertisement') {

                    $('#limitation_for_advertisement').show();
                    $('#advertisementlimit').attr("required", "true");
                } else {
                    $('#limitation_for_advertisement').hide();
                    $('#advertisementlimit').removeAttr("required");
                }
            }
        });


        function disable(id) {
            $.ajax({
                url: "{{ route('package.updatestatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 0,
                },
                cache: false,
                success: function(result) {
                    let text = '{{ trans("Package OFF Successfully") }}';
                    if (result.error == false) {
                        Toastify({
                            text: text,
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: result.message,
                            duration: 6000,
                            close: !0,
                            backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"

                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                },
                error: function(error) {

                }
            });
        }

        function active(id) {
            $.ajax({
                url: "{{ route('package.updatestatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 1,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        let text = '{{ trans("Package On Successfully") }}';
                        Toastify({
                            text: text,
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: result.message,
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                },
                error: function(error) {

                }
            });
        }

        let formSuccessFunction = () => {
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    </script>
@endsection
