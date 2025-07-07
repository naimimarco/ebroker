@extends('layouts.main')

@section('title')
    {{ __('Notification Settings') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>{{ __('Notification Settings') }}</h4>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <form class="form" action="{{ route('notification-setting-store') }}" method="POST" enctype="multipart/form-data" data-parsley-validate>
            {{ csrf_field() }}
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        {{-- Project ID --}}
                                        @if(empty($firebaseProjectId))
                                            <div class="col-sm-12 col-lg-6 form-group mandatory">
                                                {{ Form::label('project-id', __('Project ID') , ['class' => 'form-label']) }}
                                                {!! Form::text('firebase_project_id', ($firebaseProjectId != '' ? $firebaseProjectId : ''), ['class' => 'form-control', 'placeholder' => __('Project ID'), 'id' => 'project-id', 'data-parsley-required' => 'true']) !!}
                                            </div>
                                        @else
                                            <div class="col-sm-12 col-lg-6 form-group">
                                                {{ Form::label('project-id', __('Project ID') , ['class' => 'form-label']) }}
                                                {!! Form::text('firebase_project_id', ($firebaseProjectId != '' ? $firebaseProjectId : ''), ['class' => 'form-control', 'placeholder' => __('Project ID'), 'id' => 'project-id']) !!}
                                            </div>
                                        @endif

                                        {{-- Firebase Service JSON File --}}
                                        @if(empty($firebaseServiceJsonFile))
                                            <div class="col-sm-12 col-lg-6 form-group mandatory">
                                                {{ Form::label('service-json-file', __('Service JSON File'), ['class' => 'form-label']) }}
                                                {{ Form::file('firebase_service_json_file', ['class' => 'form-control', 'data-parsley-required' => 'true', 'accept' => '.json', 'required' => true]) }}
                                            </div>
                                        @else
                                            <div class="col-sm-12 col-lg-6 form-group">
                                                {{ Form::label('service-json-file', __('Service JSON File'), ['class' => 'form-label']) }}
                                                {{ Form::file('firebase_service_json_file', ['class' => 'form-control', 'accept' => '.json']) }}
                                                <div class="mt-2">
                                                    <span class="alert-info rounded text-sm p-2">{{__('File Exists')}}</span>
                                                </div>
                                            </div>
                                            @endif
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
