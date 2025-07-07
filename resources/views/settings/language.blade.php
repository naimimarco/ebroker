@php

    $lang = Session::get('language');

@endphp

@extends('layouts.main')

@section('title')
    {{ __('Languages') }}
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

            <div class="card-header">

                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Add Language') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <div class="card-body">
                    <div class="row form-group">
                        <div class="col-sm-12 col-md-12 form-group">
                            {!! Form::open(['url' => route('language.store'), 'files' => true, 'data-parsley-validate']) !!}

                            <div class="row">
                                {{-- Language Name --}}
                                <div class="col-sm-12 col-md-4 form-group mandatory ">
                                    {{ Form::label('Language Name', __('Language Name'), [ 'class' => 'form-label text-center', ]) }}
                                    {{ Form::text('name', '', [ 'class' => 'form-control', 'placeholder' => trans('Language Name'), 'data-parsley-required' => 'true', ]) }}
                                </div>

                                {{-- Language Code --}}
                                <div class="col-sm-12 col-md-4 form-group mandatory ">
                                    {{ Form::label('Language Code', __('Language Code'), [ 'class' => 'form-label text-center', ]) }}
                                    {{ Form::text('code', '', [ 'class' => 'form-control', 'placeholder' => trans('Language Code'), 'data-parsley-required' => 'true', ]) }}
                                    <small class="text-danger text-sm">{{ __("Only Small English Characters, Numbers And Hypens Allowed") }}</small>
                                </div>

                                {{-- RTL --}}
                                <div class="col-sm-1 col-md-1">
                                    {{ Form::label('file', __('RTL'), ['class' => 'col-form-label text-center']) }}
                                    <div class="form-check form-switch col-12">
                                        {{ Form::checkbox('rtl', 'true', false, ['class' => 'form-check-input']) }}
                                    </div>
                                </div>

                                {{-- Super Admin EN Json File --}}
                                <div class="col-sm-1 col-md-1">
                                    {{ Form::label('file', trans('Sample for Admin'), [ 'class' => 'col-form-label text-center', ]) }}
                                    <div class="form-check form-switch language-download-pill">
                                        <a class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" href="{{ route('download-panel-file') }}" title="Edit"><i class="bi bi-download"></i></a>
                                    </div>
                                </div>

                                {{-- App EN Json File --}}
                                <div class="col-sm-1 col-md-1">
                                    {{ Form::label('file', trans('Sample For App'), ['class' => 'col-form-label text-center']) }}
                                    <div class="form-check form-switch language-download-pill">
                                        <a class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" href="{{ route('download-app-file') }}" title="Edit"><i class="bi bi-download"></i></a>
                                    </div>
                                </div>

                                {{-- Web EN Json File --}}
                                <div class="col-sm-1 col-md-1">
                                    {{ Form::label('file', trans('Sample For Web'), ['class' => 'col-form-label text-center']) }}
                                    <div class="form-check form-switch language-download-pill">
                                        <a class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" href="{{ route('download-web-file') }}" title="Edit"><i class="bi bi-download"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-sm-2 col-md-3 form-group mandatory">

                                    {{ Form::label('file', __('File For Admin Panel'), [
                                        'class' => 'form-label text-center',
                                        'accept' => '.json.*',
                                    ]) }}
                                    {{ Form::file('file_for_panel', [
                                        'class' => 'form-control',
                                        'language code',
                                        'data-parsley-required' => 'true',
                                        'accept' => '.json',
                                        'id' => 'admin_file',
                                    ]) }}

                                </div>
                                <div class="col-sm-2 col-md-3  form-group mandatory">

                                    {{ Form::label('file', __('File For App'), ['class' => 'form-label text-center', 'accept' => '.json.*']) }}

                                    {{ Form::file('file', ['class' => 'form-control', 'data-parsley-required' => 'true', 'accept' => '.json', 'id' => 'app_file']) }}

                                </div>
                                <div class="col-sm-2 col-md-3  form-group mandatory">

                                    {{ Form::label('file', __('File For Web'), ['class' => 'form-label text-center', 'accept' => '.json.*']) }}
                                    {{ Form::file('file_for_web', [
                                        'class' => 'form-control',
                                        'data-parsley-required' => 'true',
                                        'accept' => '.json',
                                        'id' => 'web_file',
                                    ]) }}

                                </div>
                                <div class="" style="margin-top: 2%">
                                    {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1']) }}

                                </div>

                                <div class="col-md-12">
                                    <div class="img_error" style="color: #dc3545"></div>

                                </div>

                            </div>
                        </div>

                        <div class="col-sm-12 d-flex justify-content-end">
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('language_list') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                    <th scope="col" data-field="name" data-sortable="false">{{ __('Name') }}</th>
                                    <th scope="col" data-field="code" data-sortable="true">{{ __('Code') }}</th>
                                    <th scope="col" data-field="rtl" data-sortable="true">{{ __('Is RTL') }}
                                    <th scope="col" data-field="operate" data-sortable="false"
                                        data-events="actionEvents">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- EDIT MODEL MODEL -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ url('language_update') }}" class="form-horizontal" enctype="multipart/form-data"
                method="POST" id="myForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Language') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <input type="hidden" id="old_image" name="old_image">
                        <input type="hidden" id="edit_id" name="edit_id">
                        <div class="row">

                            {{-- Edit Language Name --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label for="edit_language" class="form-label col-12">{{ __('Language Name') }}</label>
                                        <input type="text" id="edit_language_name" class="form-control col-12" placeholder="{{ __('Language Name') }}" name="edit_language_name" required>
                                    </div>
                                </div>

                            </div>

                            {{-- EDIT Language Code --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label for="edit_language" class="form-label col-12">{{ __('Language Code') }}</label>
                                        <input type="text" class="form-control col-12 edit_language_code" placeholder="{{ __('Language Code') }}" required disabled>
                                        <input type="hidden" name="edit_language_code" class="edit_language_code">
                                    </div>
                                </div>

                            </div>

                            {{-- EDIT ADMIN JSON --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group" id="edit-admin-file-div">
                                        <label for="edit-admin-file" class="form-label col-12">{{ __('File For Admin Panel') }}</label>
                                        <input type="file" id="edit-admin-file" class="form-control col-12" name="edit_json_admin">

                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-danger error-msg">{{ $error }}</div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span class="d-none alert-info rounded text-sm p-2" id="edit-admin-file-status">{{ __('File Exists') }}</span>
                                </div>
                            </div>

                            {{-- EDIT APP JSON --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group" id="edit-app-file-div">
                                        <label for="edit-app-file" class="form-label col-12">{{ __('File For App') }}</label>
                                        <input type="file" id="edit-app-file" class="form-control col-12" name="edit_json_app">

                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-danger error-msg">{{ $error }}</div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <span class="d-none alert-info rounded text-sm p-2" id="edit-app-file-status">{{ __('File Exists') }}</span>
                            </div>

                            {{-- EDIT WEB JSON --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group" id="edit-web-file-div">
                                        <label for="edit-web-file" class="form-label col-12">{{ __('File For Web') }}</label>
                                        <input type="file" id="edit-web-file" class="form-control col-12" name="edit_json_web">

                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-danger error-msg">{{ $error }}</div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <span class="d-none alert-info rounded text-sm p-2" id="edit-web-file-status">{{ __('File Exists') }}</span>
                            </div>

                            {{-- EDIT RTL SWITCH --}}
                            <div class="col-sm-12 mt-2">
                                <div class="col-md-12 col-12">
                                    <div class="form-group form-check form-switch">
                                        <label for="edit_json" class="form-label col-12">{{ __('RTL') }}</label>
                                        <input type="checkbox" class="form-check-input" name="edit_rtl" id="edit_rtl">

                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-danger error-msg">{{ $error }}</div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>

                        <button type="submit"
                            class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
                    </div>
            </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div>
    <!-- EDIT MODEL -->
@endsection
@section('script')
    <script>
        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit_id").val(row.id);
                $("#edit_language_name").val(row.name);
                $(".edit_language_code").val(row.code);
                $("#edit_rtl").prop('checked', row.rtl === "Yes");
                if(row.file_for_admin){
                    $("#edit-admin-file-div").removeClass('mandatory');
                    $("#edit-admin-file").removeAttr('data-parsley-required');
                    $("#edit-admin-file-status").removeClass('d-none');
                }else{
                    $("#edit-admin-file-div").addClass('mandatory');
                    $("#edit-admin-file").attr('data-parsley-required',true);
                    $("#edit-admin-file-status").addClass('d-none');
                }
                if(row.file_for_app){
                    $("#edit-app-file-div").removeClass('mandatory');
                    $("#edit-app-file").removeAttr('data-parsley-required');
                    $("#edit-app-file-status").removeClass('d-none');
                }else{
                    $("#edit-app-file-div").addClass('mandatory');
                    $("#edit-app-file").attr('data-parsley-required',true);
                    $("#edit-app-file-status").addClass('d-none');
                }
                if(row.file_for_web){
                    $("#edit-web-file-div").removeClass('mandatory');
                    $("#edit-web-file").removeAttr('data-parsley-required');
                    $("#edit-web-file-status").removeClass('d-none');
                }else{
                    $("#edit-web-file-div").addClass('mandatory');
                    $("#edit-web-file").attr('data-parsley-required',true);
                    $("#edit-web-file-status").addClass('d-none');
                }
            }
        }


        $('#admin_file,#app_file').on('change', function() {
            const allowedExtensions = /(\.json)$/i;
            const fileInput = this;
            const file = fileInput.files[0];

            if (!file) {
                return; // No file selected
            }

            if (!allowedExtensions.exec(file.name)) {
                $('.img_error').text('Invalid file type. Please choose an json file.');
                fileInput.value = '';
                return;
            }


        });

        function queryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,

            };
        }
    </script>
@endsection
