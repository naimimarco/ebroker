@extends('layouts.main')

@section('title')
    {{ __('Project') }}
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
        <div class="card">
            @if (has_permissions('create', 'project'))
                <div class="card-header">
                    <div class="row ">
                        {{-- Add Property Button --}}
                        <div class="col-12 col-xs-12 d-flex justify-content-end">
                            <a href="{{ route('project.create') }}" class="btn btn-primary">{{ __('Add Project') }}</a>
                        </div>

                    </div>
                </div>
                <hr>
            @endif
            <div class="card-body">

                <div class="row " id="toolbar">

                    <div class="col-sm-6">

                        <select class="form-select form-control-sm" id="filter_category">
                            <option value="">{{ __('Select Category') }}</option>
                            @if (isset($category))
                                @foreach ($category as $row)
                                    <option value="{{ $row->id }}">{{ $row->category }} </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6">

                        <select id="status" class="form-select form-control-sm">
                            <option value="">{{ __('Select Status') }} </option>
                            <option value="0">{{ __('Inactive') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped"
                            id="table_list" data-toggle="table" data-url="{{ route('project.show', 1) }}"
                            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="owner_name" data-align="center" data-sortable="false"> {{ __('Client Name') }}</th>
                                    <th scope="col" data-field="customer.mobile" data-align="center" data-sortable="false"> {{ __('Mobile') }} </th>
                                    <th scope="col" data-field="title" data-align="center" data-sortable="true">{{ __('Title') }} </th>
                                    <th scope="col" data-field="category.category" data-align="center"> {{ __('Category') }}</th>
                                    <th scope="col" data-field="type" data-align="center" data-sortable="true" data-formatter="projectTypeFormatter"> {{ __('Type') }}</th>
                                    <th scope="col" data-field="image" data-align="center" data-formatter="imageFormatter" data-sortable="false"> {{ __('Image') }}</th>
                                    @if (has_permissions('update', 'project'))
                                        <th scope="col" data-field="status" data-sortable="false" data-align="center" data-formatter="projectEnableDisableSwitchFormatter" data-width="5%"> {{ __('Enable/Disable') }}</th>
                                    @endif
                                    <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="yesNoStatusFormatter"> {{ __('Is Project Active ?') }}</th>
                                    <th scope="col" data-field="request_status" data-sortable="false" data-align="center" data-width="5%" data-formatter="requestStatusFormatter"> {{ __('Status') }}</th>
                                    <th scope="col" data-field="video_link" data-sortable="false" data-align="center" data-formatter="videoLinkFormatter"> {{ __('Video Link') }}</th>
                                    <th scope="col" data-field="document_action" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Documents/Images') }}</th>
                                    <th scope="col" data-field="action" data-align="center" data-sortable="false" data-events="actionEvents"> {{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div id="documentsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">{{ __('Documents/Images') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h3>{{ __('Images') }}</h3>
                        <hr>
                        <div class="row gallary_images">

                        </div>
                        <hr>
                        <h3 class="mt-4">{{ __('Documents') }}</h3>
                        <hr>
                        <div class="row documents"></div>
                        <hr>

                        <h3 class="mt-4">{{ __('Floor Plans') }}</h3>
                        <hr>
                        <div class="row plans"></div>
                    </div>
                </div>

            </div>

        </div>
    </section>


    {{-- Change Request Status Modal --}}
    <div id="changeRequestStatusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="changeRequestStatusModalContent" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="changeRequestStatusModalContent">{{ __('Change Status') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form class="create-form" action="{{ route('update-project-request-status') }}" data-success-function="editFormSuccessFunction" method="POST">
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
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn_submit">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>

        </div>

@endsection

@section('script')
    <script>
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });

        $('#filter_category').on('change', function() {
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
            };
        }

        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {

            },
            'click .documents-btn': function(e, value, row, index) {
                $('.gallary_images').empty();
                $('.documents').empty();
                $('.plans').empty();

                if(row.gallary_images.length){
                    $.each(row.gallary_images, function(key, value) {
                        $('.gallary_images').append(
                            `<div class="col-sm-12 col-md-3 col-lg-2 mt-1 ml-1">
                                <a href="${value.name}" target="_blank">
                                    <img src="${value.name}"height="100" width="100" class="rounded"/>
                                </a>
                            </div>`
                        );
                    });
                }else{
                    $('.gallary_images').append(
                        `<span class="no-data-found-span">
                            ${window.trans["No Data Found"]}
                        </span>`
                    );
                }

                if(row.documents.length){
                    $.each(row.documents, function(key, value) {
                        var url = value.name; // Your URL
                        var filename = url.split('/').pop();
                        var documentSvgImage = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M208 64h66.75a32 32 0 0122.62 9.37l141.26 141.26a32 32 0 019.37 22.62V432a48 48 0 01-48 48H192a48 48 0 01-48-48V304"></path><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M288 72v120a32 32 0 0032 32h120"></path><path fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M160 80v152a23.69 23.69 0 01-24 24c-12 0-24-9.1-24-24V88c0-30.59 16.57-56 48-56s48 24.8 48 55.38v138.75c0 43-27.82 77.87-72 77.87s-72-34.86-72-77.87V144"></path></svg>`;
                        var downloadImg = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m12 16 4-5h-3V4h-2v7H8z"></path><path d="M20 18H4v-7H2v7c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2v-7h-2v7z"></path></svg>`;

                        $('.documents').append(
                            `<div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2">
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
                                            <span>${window.trans["Download"]}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>`
                        );
                    });
                }else{
                    $('.documents').append(
                        `<span class="no-data-found-span">
                            ${window.trans["No Data Found"]}
                        </span>`
                    );
                }


                if(row.plans.length){
                    $.each(row.plans, function(key, value) {
                        var url = value.title; // Your URL

                        $('.plans').append(
                            `<div class="accordion col-6">
                                <div class="accordion-item">
                                    <div class="accordion-item-header">${value.title}</div>
                                    <div class="accordion-item-body">
                                        <div class="accordion-item-body-content">
                                            <img src="${value.document}" height="100%" width="100%"/>
                                        </div>
                                    </div>
                                </div>
                            </div>`
                        );
                    });
                }else{
                    $('.plans').append(
                        `<span class="no-data-found-span">
                            ${window.trans["No Data Found"]}
                        </span>`
                    );
                }
            },
            'click .request-status-btn': function(e, value, row, index) {
                $("#edit-request-status-id").val(row.id);
                $('input[name=request_status]').prop('checked', false);
                $(".reject-reason-text-div").hide();
            }
        }

        function editFormSuccessFunction () {
            $('#table_list').bootstrapTable('refresh');
            setTimeout(function () {
                $('#changeRequestStatusModal').modal('hide');
            }, 1000);
        }
    </script>
@endsection
