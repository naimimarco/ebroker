@extends('layouts.main')

@section('title')
    {{ __('Property') }}
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
            @if (has_permissions('create', 'property'))
                <div class="card-header">
                    <div class="row ">
                        {{-- Add Property Button --}}
                        <div class="col-12 col-xs-12 d-flex justify-content-end">
                            {!! Form::open(['route' => 'property.create']) !!}
                            {{ method_field('get') }}
                            {{ Form::submit(__('Add Property'), ['class' => 'btn btn-primary']) }}
                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            @endif

            <hr>
            <div class="card-body">
                <div class="row" id="toolbar">
                    {{-- Filter Category --}}
                    <div class="col-xl-3 mt-2">
                        <select class="form-select form-control-sm" id="filter_category">
                            <option value="">{{ __('Select Category') }}</option>
                            @if (isset($category))
                                @foreach ($category as $row)
                                    <option value="{{ $row->id }}">{{ $row->category }} </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {{-- Filter Status --}}
                    <div class="col-xl-3 mt-2">
                        <select id="status" class="form-select form-control-sm">
                            <option value="">{{ __('Select Status') }} </option>
                            <option value="0">{{ __('Inactive') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                        </select>
                    </div>
                    {{-- Filter Type --}}
                    <div class="col-xl-3 mt-2">
                        <select id="property-type-filter" class="form-select form-control-sm">
                            <option value="">{{ __('Select Type') }} </option>
                            <option value="0">{{ __('Sale') }}</option>
                            <option value="1">{{ __('Rent') }}</option>
                            <option value="2">{{ __('Sold') }}</option>
                            <option value="3">{{ __('Rented') }}</option>
                        </select>
                    </div>
                    {{-- Filter Owner --}}
                    <div class="col-xl-3 mt-2">
                        <select id="property-owner-filter" class="form-select form-control-sm">
                            <option value="">{{ __('Select Property Owner') }} </option>
                            <option value="0">{{ __('By Admin') }}</option>
                            <option value="1">{{ __('By Users') }}</option>
                        </select>
                    </div>
                    {{-- Filter Private/General --}}
                    <div class="col-xl-3 mt-2">
                        <select id="property-accessibility-filter" class="form-select form-control-sm">
                            <option value="">{{ __('Select Accessibility Type') }} </option>
                            <option value="0">{{ __('All') }}</option>
                            <option value="1">{{ __('Private') }}</option>
                            <option value="2">{{ __('General') }}</option>
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped"
                            id="table_list" data-toggle="table" data-url="{{ url('getPropertyList') }}"
                            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="added_by" data-align="center" data-sortable="false"> {{ __('Client Name') }}</th>
                                    <th scope="col" data-field="mobile" data-align="center" data-sortable="false"> {{ __('Mobile') }} </th>
                                    <th scope="col" data-field="client_address" data-align="center" data-sortable="false">{{ __('Client Address') }}</th>
                                    <th scope="col" data-field="title" data-sortable="false" class="max-width-row">{{ __('Title') }}</th>
                                    <th scope="col" data-field="slug_id" data-visible="false" data-sortable="true" data-align="center">{{ __('Slug') }}</th>
                                    <th scope="col" data-field="address" data-align="center" data-sortable="false"> {{ __('Address') }}</th>
                                    <th scope="col" data-field="category.category" data-align="center" data-sortable="false"> {{ __('Category') }}</th>
                                    <th scope="col" data-field="propery_type" data-formatter="propertyTypeFormatter" data-align="center" data-sortable="true"> {{ __('Type') }}</th>
                                    <th scope="col" data-field="edit_status" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                    <th scope="col" data-field="status" data-align="center" data-sortable="false" data-formatter="statusFormatter"> {{ __('Status') }}</th>
                                    <th scope="col" data-field="title_image" data-formatter="imageFormatter" data-align="center" data-sortable="false"> {{ __('Image') }}</th>
                                    <th scope="col" data-field="three_d_image" data-formatter="imageFormatter" data-align="center" data-sortable="false"> {{ __('3D Image') }}</th>
                                    <th scope="col" data-field="interested_users" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Total Interested Users') }}</th>
                                    <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="yesNoStatusFormatter"> {{ __('Is Property Active ?') }}</th>
                                    <th scope="col" data-field="request_status" data-sortable="false" data-align="center" data-width="5%" data-formatter="requestStatusFormatter"> {{ __('Status') }}</th>
                                    <th scope="col" data-field="is_premium" data-formatter="premium_status_switch" data-align="center" data-sortable="false"> {{ __('Private/Public') }}</th>
                                    <th scope="col" data-field="gallery-images-btn" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Gallery Images') }}</th>
                                    <th scope="col" data-field="documents-btn" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Documents') }}</th>
                                    <th scope="col" data-field="video_link" data-sortable="false" data-align="center" data-formatter="videoLinkFormatter"> {{ __('Video Link') }}</th>
                                    @if (has_permissions('update', 'property'))
                                        <th scope="col" data-field="operate" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Interested Users Modal --}}
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">{{ __('Interested Users') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped" id="customer_table_list" data-toggle="table" data-url="{{ url('customerList') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3" data-query-params="customerqueryParams" data-show-export="true" data-export-options='{ "fileName": "data-list-<?= date(' d-m-y') ?>" }'>
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="profile" data-sortable="false" data-align="center" data-formatter="imageFormatter"> {{ __('Profile') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true" data-align="center"> {{ __('Name') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="true" data-align="center"> {{ __('Number') }}</th>
                                    <th scope="col" data-field="email" data-sortable="false" data-align="center"> {{ __('Email') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Interested Users Modal --}}


        {{-- Gallery Images Modal --}}
        <div id="galleryImagesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="galleryImagesModalContent" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="galleryImagesModalContent">{{ __('Gallery Images') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body gallary-images-div row">
                    </div>
                </div>

            </div>

        </div>
        {{-- End Gallery Images Modal --}}

        {{-- Documents Modal --}}
        <div id="documentsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="documentsModalContent" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="documentsModalContent">{{ __('Documents') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body documents-div row">
                    </div>
                </div>

            </div>

        </div>
        {{-- End Documents Modal --}}

        {{-- Change Request Status Modal --}}
        <div id="changeRequestStatusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="changeRequestStatusModalContent" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="changeRequestStatusModalContent">{{ __('Change Status') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form class="create-form" action="{{ route('update-property-request-status') }}" data-pre-submit-function="beforeSubmitFunction" data-success-function="editFormSuccessFunction" method="POST">
                        <div class="modal-body">
                            {{ csrf_field() }}

                            {!! Form::hidden('id', "", ['id' => 'edit-request-status-id']) !!}
                            <div class="col-md-12 col-12 form-group mandatory">
                                <div class="row">
                                    {{ Form::label('', __('Status'), ['class' => 'form-label col-12 ']) }}

                                    {{-- Approve --}}
                                    <div class="col-md-3">
                                        {{ Form::radio('request_status', 'approved', null, [ 'class' => 'form-check-input request-status', 'id' => 'status-approve', 'required' => true ]) }}
                                        {{ Form::label('status-approve', __('Approve'), ['class' => 'form-check-label']) }}
                                    </div>

                                    {{-- Reject --}}
                                    <div class="col-md-3">
                                        {{ Form::radio('request_status', 'rejected', null, [ 'class' => 'form-check-input request-status', 'id' => 'status-reject', 'required' => true, ]) }}
                                        {{ Form::label('status-reject', __('Reject'), ['class' => 'form-check-label']) }}
                                    </div>

                                    {{-- Reason for Reject --}}
                                    <div class="col-12 mt-1 reject-reason-text-div" style="display: none">
                                        {{ Form::label('reject-reason-text', __('Reject Reason'), ['class' => 'form-label']) }}
                                        {!! Form::textarea('reject_reason', null, ["id" => "reject-reason-text", "class" => 'form-control', "placeholder" => trans('Reject Reason')]) !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect close-btn" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn_submit">{{ __('Update') }}</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
        {{-- End Change Request Status --}}

        <input type="hidden" id="property_id">

    </section>

@endsection

@section('script')
    <script>
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });

        $('#filter_category').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $('#property-type-filter').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $('#property-owner-filter').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $('#property-accessibility-filter').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });


        $(document).ready(function() {
            var params = new window.URLSearchParams(window.location.search);
            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
            if (params.get('type') != 'null') {
                $('#type').val(params.get('type'));
            }
        });


        function queryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#status').val(),
                category: $('#filter_category').val(),
                property_type: $('#property-type-filter').val(),
                property_added_by: $('#property-owner-filter').val(),
                property_accessibility: $('#property-accessibility-filter').val(),
                customerID: "{{ $customerID }}"
            };
        }

        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $('#property_id').val(row.id);
                $('#customer_table_list').bootstrapTable('refresh');
            },
            'click .gallery-image-btn': function(e, value, row, index) {
                $('.gallary-images-div').empty();
                if(row.gallery.length){
                    $.each(row.gallery, function(key, value) {
                        $('.gallary-images-div').append(
                            `<div class="col-sm-12 col-md-3 col-lg-2 mt-1 ml-1">
                                <a href="${value.image_url}" target="_blank">
                                    <img src="${value.image_url}"height="100" width="100" class="rounded"/>
                                </a>
                            </div>`
                        );
                    });
                }else{
                    $('.gallary-images-div').append(
                        `<span class="no-data-found-span">
                            ${window.trans["No Data Found"]}
                        </span>`
                    );
                }
            },
            'click .documents-btn': function(e, value, row, index) {
                $('.documents-div').empty();
                if(row.documents.length){
                    $.each(row.documents, function(key, value) {
                        var url = value.file; // Your URL
                        var filename = value.file_name;
                        var documentSvgImage = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M208 64h66.75a32 32 0 0122.62 9.37l141.26 141.26a32 32 0 019.37 22.62V432a48 48 0 01-48 48H192a48 48 0 01-48-48V304"></path><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M288 72v120a32 32 0 0032 32h120"></path><path fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M160 80v152a23.69 23.69 0 01-24 24c-12 0-24-9.1-24-24V88c0-30.59 16.57-56 48-56s48 24.8 48 55.38v138.75c0 43-27.82 77.87-72 77.87s-72-34.86-72-77.87V144"></path></svg>`;
                        var downloadImg = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m12 16 4-5h-3V4h-2v7H8z"></path><path d="M20 18H4v-7H2v7c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2v-7h-2v7z"></path></svg>`;
                        var downloadText = "{{ __('Download') }}";

                        $('.documents-div').append(
                            `<div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 bg-light rounded m-2 p-2">
                                <div class="docs_main_div">
                                    <div class="doc_icon">
                                        ${documentSvgImage}
                                    </div>
                                    <div class="doc_title">
                                        <span title="${filename}">${filename}</span>
                                    </div>
                                    <div class="doc_download_button">
                                        <a href="${url}" target="_blank">
                                            <span>
                                                ${downloadImg}
                                            </span>
                                            <span>${downloadText}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>`
                        );
                    });
                }else{
                    $('.documents-div').append(
                        `<span class="no-data-found-span">
                            ${window.trans["No Data Found"]}
                        </span>`
                    );
                }
            },
            'click .request-status-btn': function(e, value, row, index) {
                $("#edit-request-status-id").val(row.id);
                $('input[name=request_status]').prop('checked', false);
                $("#reject-reason-text").text("").removeAttr("required");
                $(".reject-reason-text-div").hide();
            }
        }

        function customerqueryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                property_id: $('#property_id').val(),
            };
        }
        function editFormSuccessFunction () {
            $('#table_list').bootstrapTable('refresh');
            setTimeout(function () {
                $('#changeRequestStatusModal').modal('hide');
            }, 1000);
            $('#changeRequestStatusModal').find('.btn-close').removeAttr('disabled');
            $('#changeRequestStatusModal').find('.close-btn').removeAttr('disabled');
        }

        const beforeSubmitFunction = (formElement) => {
            console.log('hello');

            $('#changeRequestStatusModal').find('.btn-close').removeAttr('disabled');
            $('#changeRequestStatusModal').find('.close-btn').removeAttr('disabled');
        }
    </script>
@endsection
