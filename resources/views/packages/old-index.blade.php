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
                            {!! Form::open(['route' => 'package.store', 'data-parsley-validate', 'files' => true]) !!}
                            <div class="card-body">

                                <div class="row ">

                                    <div class="col-md-4 form-group mandatory">

                                        {{ Form::label('name', __('Package Name'), ['class' => 'form-label col-12 ']) }}
                                        {{ Form::text('name', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Package Name'),
                                            'data-parsley-required' => 'true',
                                            'id' => 'name',
                                        ]) }}

                                    </div>
                                    <div class="col-md-4 form-group">

                                        {{ Form::label('ios_product_id', __('IOS Product ID'), ['class' => 'form-label col-12 ']) }}
                                        {{ Form::text('ios_product_id', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('IOS Product ID'),
                                            'id' => 'ios_product_id',
                                        ]) }}

                                    </div>
                                    <div class="col-md-4 form-group mandatory">

                                        {{ Form::label('duration', __('Duration'), ['class' => 'form-label col-12 ']) }}
                                        {{ Form::number('duration', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Duration (in days)'),
                                            'data-parsley-required' => 'true',
                                            'id' => 'duration',
                                            'min' => '1',
                                        ]) }}

                                    </div>
                                    <div class="col-md-4 form-group mandatory">

                                        {{ Form::label('price', __('Price') . '(' . $currency_symbol . ')', [
                                            'class' => 'form-label col-12 ',
                                        ]) }}
                                        {{ Form::number('price', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => trans('Price'),
                                            'data-parsley-required' => 'true',
                                            'id' => 'price',
                                            'min' => '0',
                                            'step' => '0.01'
                                        ]) }}

                                    </div>

                                    <div class="col-sm-12 col-md-12 form-group mandatory">
                                        {{ Form::label('price', __('Type'), ['class' => 'form-label col-12 ']) }}

                                        {{-- Product List and Promote --}}
                                        <div>
                                            <input type="radio" id="product-list-promote" name="package_type" value="product_listing" required>
                                            <label for="product-list-promote">{{ __('Product List and Promote') }}</label>
                                        </div>

                                        {{-- Premium User --}}
                                        <div>
                                            <input type="radio" id="premium-user" name="package_type" value="premium_user" required>
                                            <label for="premium-user">{{ __('Premium User') }}</label>
                                        </div>


                                    </div>
                                    <div class="limitations col-md-12">
                                        <div id="property_limitation" class="col-md-12 col-sm-12 form-group">
                                            {{ Form::label('price', __('Property'), ['class' => 'form-label col-12 ']) }}

                                            <input type="radio" id="property-limited" name="typep" value="add_limited_property">
                                            <label for="property-limited">{{ __('Limited') }}</label>

                                            <input type="radio" id="property-unlimited" name="typep" value="add_unlimited_property">
                                            <label for="property-unlimited">{{ __('Unlimited') }}</label>

                                        </div>
                                        <div id="limitation_for_property" class="col-md-4 col-lg-3 form-group">
                                            {{ Form::label('limit', __('Limit'), ['class' => 'form-label col-12 ']) }}
                                            {{ Form::number('property_limit', '', [
                                                'class' => 'form-control',
                                                'type' => 'number',
                                                'min' => '1',
                                                'placeholder' => trans('Limitation'),
                                                'id' => 'propertylimit',
                                                'min' => '1',
                                            ]) }}

                                        </div>

                                        <div id="advertisement_limitation" class="col-md-12 col-sm-12 form-group">
                                            {{ Form::label('price', __('Advertisement'), ['class' => 'form-label col-12']) }}

                                            <input type="radio" id="advertisement-limited" name="typel" value="add_limited_advertisement">
                                            <label for="advertisement-limited">{{ __('Limited') }}</label>

                                            <input type="radio" id="advertisment-unlimited" name="typel" value="add_unlimited_advertisement">
                                            <label for="advertisment-unlimited">{{ __('Unlimited') }}</label>

                                        </div>

                                        <div id="limitation_for_advertisement" class="col-md-4 col-lg-3 form-group">

                                            {{ Form::label('limit', __('Limit'), ['class' => 'form-label col-12 ']) }}

                                            {{ Form::number('advertisement_limit', '', [
                                                'class' => 'form-control ',
                                                'type' => 'number',
                                                'min' => '1',
                                                'placeholder' => trans('Limitation'),
                                                'id' => 'advertisementlimit',
                                                'min' => '1',
                                            ]) }}

                                        </div>
                                    </div>
                                </div>

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
                                    id="table_list" data-toggle="table" data-url="{{ url('package_list') }}"
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
                                            <th scope="col" data-field="duration" data-align="center" data-sortable="false"> {{ __('Duration') }}</th>
                                            <th scope="col" data-field="price" data-align="center" data-sortable="false"> {{ __('Price') }} </th>
                                            <th scope="col" data-field="property_limit" data-align="center" data-sortable="false"> {{ __('Limit For Property') }} </th>
                                            <th scope="col" data-field="advertisement_limit" data-align="center" data-sortable="false"> {{ __('Limit For Advertisement') }} </th>
                                            <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                            @if (has_permissions('update', 'package'))
                                                <th scope="col" data-field="operate" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Action') }}</th>
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
                    <div class="modal-body">
                        <form action="{{ url('package-update') }}" class="form-horizontal edit-form" enctype="multipart/form-data" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" id="edit_id" name="edit_id">
                            <div class="row">
                                {{-- Package Name --}}
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="edit_name" class="form-label col-12 ">{{ __('Package Name') }}</label>
                                        <input type="text" id="edit_name" class="form-control col-12" placeholder="{{ __('Package Name') }}" name="edit_name" required>
                                    </div>
                                </div>

                                {{-- IOS Product ID --}}
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="edit_ios_product_id" class="form-label col-12 ">{{ __('IOS Product ID') }}</label>
                                        <input type="text" id="edit_ios_product_id" class="form-control col-12" placeholder="{{ __('IOS Product ID') }}" name="edit_ios_product_id">
                                    </div>
                                </div>

                                {{-- Duration --}}
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="edit_duration" class="form-label col-12 ">{{ __('Duration') }}</label>
                                        <input type="text" id="edit_duration" class="form-control col-12" placeholder="{{ __('Duration') }}" name="edit_duration" min="1" required>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-sm-12 col-12">
                                    <div class="form-group">
                                        <label for="email" class="form-label col-12 ">{{ __('Status') }}</label>
                                        {!! Form::select('status', ['0' => trans('OFF'), '1' => trans('ON')], '', [ 'class' => 'form-select', 'id' => 'status', 'required' => true]) !!}
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>

                        <button type="submit"
                            class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->

        <!-- /.modal-dialog -->
        <!-- EDIT MODEL -->
    </section>
@endsection

@section('script')
    <script>
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
                $("#edit_id").val(row.id);
                $("#edit_ios_product_id").val(row.ios_product_id);
                $("#edit_name").val(row.name);
                $("#edit_duration").val(row.duration);
                $('#status').val(row.status);
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
    </script>
@endsection
