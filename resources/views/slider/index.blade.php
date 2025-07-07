@extends('layouts.main')

@section('title')
    {{ __('Slider') }}
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
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {!! Form::open(['url' => route('slider.store'), 'data-parsley-validate', 'files' => true, 'id' => 'sliderForm', 'onsubmit' => 'return validateForm()']) !!}
                        <div class="row mandatory mt-1">
                            {{-- Type --}}
                            <div class="col-sm-12 col-md-4 form-group mandatory">
                                {{ Form::label('type', __('Type'), [ 'class' => 'col-md-12 col-sm-12 form-label']) }}
                                <select name="type" class="choosen-select form-select form-control-sm" id="slider-type" data-parsley-required="true" >
                                    <option value="" selected disabled>{{ __('Select Type') }}</option>
                                    <option value="1">{{ __('Only Image') }}</option>
                                    <option value="2">{{ __('Category') }}</option>
                                    <option value="3">{{ __('Property') }}</option>
                                    <option value="4">{{ __('Other Link') }}</option>
                                </select>
                            </div>

                            {{-- App Image --}}
                            <div class="col-sm-12 col-md-4 form-group mandatory">
                                {{ Form::label('app-image', __('App Image'), [ 'class' => 'col-md-12 col-sm-12 form-label', ]) }}
                                {{ Form::file('image', ['class' => 'form-control', 'accept' => 'image/jpg,image/png,image/jpeg', 'data-parsley-required' => true,'id' => 'app-image']) }}
                                <span class="text-danger text-sm">{{ __('Only JPG, JPEG and PNG files are allowed') }}</span>
                            </div>

                            {{-- Web Image --}}
                            <div class="col-sm-12 col-md-4 form-group mandatory">
                                {{ Form::label('web-image', __('Web Image'), [ 'class' => 'col-md-12 col-sm-12 form-label', ]) }}
                                {{ Form::file('web_image', ['class' => 'form-control', 'accept' => 'image/jpg,image/png,image/jpeg', 'data-parsley-required' => true,'id' => 'web-image']) }}
                                <span class="text-danger text-sm">{{ __('Only JPG, JPEG and PNG files are allowed') }}</span>
                            </div>

                            {{-- Category --}}
                            <div class="col-sm-12 col-md-4 form-group mandatory" style="display: none" id="category-div">
                                {{ Form::label('category', __('Category'), [ 'class' => 'col-md-12 col-sm-12 form-label']) }}
                                <select name="category" class="choosen-select form-select form-control-sm" id="categories">
                                    @if (collect($categories)->isNotEmpty())
                                        <option value="" selected>{{ __('Choose Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category }} </option>
                                        @endforeach
                                    @else
                                        <option value="data-not-found" selected disabled>{{ __('No Data Found') }}</option>
                                    @endif
                                </select>
                            </div>

                            {{-- Property --}}
                            <div style="display: none" id="property-div" class="col-sm-12 col-md-4">
                                <div class="form-group mandatory">
                                    {{ Form::label('property', __('Property'), [ 'class' => 'col-md-12 col-sm-12 form-label', ]) }}
                                    <select name="property" id="properties" class="choosen-select form-select form-control-sm">
                                        <option value="" selected>{{ __('Choose Property') }}</option>
                                        <option value="data-not-found">{{ __('No Data Found') }}</option>
                                        @if (collect($properties)->isNotEmpty())
                                            @foreach ($properties as $row)
                                                <option value="{{ $row->id }}" data-category="{{ $row->category_id }}">{{ $row->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                {{-- Show Property Details --}}
                                <div class="form-check">
                                    <input id="show-property-details" name="show_property_details" type="checkbox" class="form-check-input">
                                    <label class="form-check-label" for="show-property-details">{{ __('Show Property Details') }}</label>
                                </div>
                            </div>

                            {{-- Link --}}
                            <div class="col-sm-12 col-md-4 form-group mandatory" style="display: none" id="link-div">
                                {{ Form::label('link', __('Link'), ['class' => 'col-md-12 col-sm-12 form-label']) }}
                                {!! Form::text('link', null, ['placeholder' => trans('Link'), 'id' => 'link', 'class' => 'form-control']) !!}
                            </div>

                            {{-- Save --}}
                            <div class="col-12 d-flex justify-content-end" style="padding: 1% 2%;">
                                {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1', 'id' => 'submitButton']) }}
                            </div>
                        </div>
                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="card">
            {{-- <form class="form" action="{{ route('slider.slider-order') }}" method="post">
                {{ csrf_field() }} --}}
                <div class="card-content">
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="form-group row ">
                                <div class="col-12">
                                    {{-- <table class="table table-striped" id="table_list" data-toggle="table" data-url="{{ url('sliderList') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3" data-query-params="queryParams" data-id-field="id" data-editable-emptytext="Default empty text." data-editable-url="{{ route('slider.slider-order') }}"> --}}
                                    <table class="table table-striped" id="table_list" data-toggle="table" data-url="{{ url('sliderList') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3" data-query-params="queryParams" data-id-field="id" data-editable-emptytext="Default empty text.">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                                <th scope="col" data-field="type" data-sortable="true" data-align="center" data-sortable="false"> {{ __('Type') }}</th>
                                                <th scope="col" data-field="image" data-align="center" data-formatter="imageFormatter" data-sortable="false"> {{ __('Image') }}</th>
                                                <th scope="col" data-field="web_image" data-align="center" data-formatter="imageFormatter" data-sortable="false"> {{ __('Web Image') }}</th>
                                                <th scope="col" data-field="category.category" data-sort-name="category" data-align="center" data-sortable="false">{{ __('Category') }}</th>
                                                <th scope="col" data-field="property.title" data-sort-name="title" data-align="center" data-sortable="false">{{ __('Property') }}</th>
                                                <th scope="col" data-field="show_property_details" data-align="center" data-visible="false" data-sortable="false" data-formatter="yesNoStatusFormatter">{{ __('Show Property Details') }}</th>
                                                <th scope="col" data-field="link" data-align="center" data-sortable="false">{{ __('Link') }}</th>
                                                <th scope="col" data-field="operate" data-align="center" data-sortable="false" data-events="actionEvents">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{-- </form> --}}
        </div>
    </section>



    <!-- EDIT MODEL MODEL -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Slider') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal edit-form" action="{{ url('slider') }}" enctype="multipart/form-data" data-parsley-validate>
                        {{ csrf_field() }}
                        <input type="hidden" id="edit-id" name="edit_id">

                        {{-- Edit Image --}}
                        <div class="row">
                            <div class="form-group edit-image-div">
                                {{ Form::label('edit-image', __('App Image'), ['class' => 'col-sm-12 col-form-label edit-image-label']) }}
                                {{ Form::file('edit_image', ['class' => 'form-control edit-image', 'accept' => 'image/jpg,image/png,image/jpeg']) }}
                            </div>
                            <div class="col-md-12 col-12 image-preview-div" style="display: none">
                                <img id="image-preview" height="100" width="110" />
                            </div>
                        </div>

                        {{-- Web Image --}}
                        <div class="row">
                            <div class="form-group edit-web-image-div">
                                {{ Form::label('web-image', __('Web Image'), ['class' => 'col-sm-12 col-form-label edit-web-image-label']) }}
                                {{ Form::file('edit_web_image', ['class' => 'form-control edit-web-image','id' => 'edit-web-image', 'accept' => 'image/jpg,image/png,image/jpeg']) }}
                            </div>
                            <div class="col-md-12 col-12 image-web-preview-div" style="display: none">
                                <img id="web-image-preview" height="100" width="110" />
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div>
    <!-- EDIT MODEL -->
@endsection

@section('script')
    <script>
        function validateForm() {
            $('#sliderForm').parsley().on('form:validate', function(formInstance) {
                if (formInstance.isValid()) {
                    $('#submitButton').prop('disabled', true);
                } else {
                    $('#submitButton').prop('disabled', false);
                }
            });
        }
        // If there are validation errors from backend, re-enable the submit button
        @if ($errors->any())
            document.getElementById('submitButton').disabled = false;
        @endif
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }

        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {

                $("#edit-id").val(row.id);

                // App Image
                $(".edit-image").removeAttr("required");
                if(row.image_exists && row.default_data == 0){
                    $(".image-preview-div").show().find('#image-preview').attr('src',row.image);
                } else if (row.default_data == 1) {
                    $(".image-preview-div").show().find('#image-preview').attr('src',row.image);
                }else{
                    $(".edit-image").attr("required",true);
                }

                // Web Image
                $(".edit-web-image").removeAttr("required");
                if(row.web_image_exists && row.default_data == 0){
                    $(".image-web-preview-div").show().find('#web-image-preview').attr('src',row.web_image);
                } else if (row.default_data == 1) {
                    $(".image-web-preview-div").show().find('#web-image-preview').attr('src',row.web_image);
                }else{
                    $(".edit-web-image").attr("required",true);
                }
            }
        }

        $(document).ready(function () {

            $("#slider-type").change(function(e){
                e.preventDefault();

                let type = $(this).val();
                let categoryElement = $('#categories')
                let propertyElement = $('#properties')
                let linkElement = $('#link')
                switch (type) {
                    case '1':
                        categoryElement.removeAttr('data-parsley-required');
                        $("#category-div").hide();

                        propertyElement.removeAttr('data-parsley-required');
                        $("#property-div").hide();

                        linkElement.removeAttr('data-parsley-required');
                        $("#link-div").hide();
                        break;
                    case '2':
                        $("#category-div").show();
                        categoryElement.attr('data-parsley-required',true).trigger('change');

                        propertyElement.removeAttr('data-parsley-required');
                        $("#property-div").hide();

                        linkElement.removeAttr('data-parsley-required');
                        $("#link-div").hide();
                        break;
                    case '3':
                        $("#category-div").show();
                        categoryElement.attr('data-parsley-required',true).trigger('change');

                        $("#property-div").show();
                        propertyElement.attr('data-parsley-required',true);

                        linkElement.removeAttr('data-parsley-required');
                        $("#link-div").hide();
                        break;
                    case '4':
                        categoryElement.removeAttr('data-parsley-required');
                        $("#category-div").hide();

                        propertyElement.removeAttr('data-parsley-required');
                        $("#property-div").hide();

                        $("#link-div").show();
                        linkElement.attr('data-parsley-required',true);
                        break;

                    default:
                        categoryElement.removeAttr('data-parsley-required');
                        $("#category-div").hide();

                        propertyElement.removeAttr('data-parsley-required');
                        $("#property-div").hide();

                        linkElement.removeAttr('data-parsley-required');
                        $("#link-div").hide();
                        break;
                }

            })



            $("#categories").change(function(e){
                e.preventDefault();
                let propertyElement = $('#properties');
                let categoryValue = $(this).val();

                propertyElement.val("").removeAttr('disabled').show();
                $("#show-property-details").attr('disabled',true)
                propertyElement.find('option').hide();
                if (propertyElement.find('option[data-category="' + categoryValue + '"]').length) {
                    $("#show-property-details").attr('disabled',false)
                    propertyElement.find('option[data-category="' + categoryValue + '"]').show().trigger('change');
                } else {
                    propertyElement.val("data-not-found").attr('disabled', true).show().trigger('change');
                    $("#show-property-details").attr('disabled',true)
                }

            })
        });
    </script>
@endsection

