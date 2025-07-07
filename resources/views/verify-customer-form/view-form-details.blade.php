@extends('layouts.main')

@section('title')
    {{ __('Manage Agent Verification') }}
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
        <a href="{{ route('agent-verification.index') }}" class="btn btn-primary">{{ __('Back') }}</a>
        <div class="card mt-3">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Agent Details') }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        {{-- Agent Id --}}
                        <div class="col-lg-4">
                            {{ Form::label('agnet-id', __('Agent Id'), ['class' => 'form-label text-center']) }}
                            {{ Form::text('agnet-id', $customerVerification->user->id, [ 'class' => 'form-control', 'placeholder' => trans('Question'),'readonly','disabled' => true]) }}
                        </div>

                        {{-- Agent Name --}}
                        <div class="col-lg-4">
                            {{ Form::label('agnet-name', __('Agent Name'), ['class' => 'form-label text-center']) }}
                            {{ Form::text('agnet-name', $customerVerification->user->name, [ 'class' => 'form-control', 'placeholder' => trans('Question'),'readonly','disabled' => true]) }}
                        </div>

                        {{-- Agent Verification Status --}}
                        <div class="col-lg-4">
                            {{ Form::label('verification-status', __('Verification Status'), ['class' => 'form-label d-block']) }}
                            @php
                                if($customerVerification->status == 'success'){
                                    $btnClass = 'btn btn-success';
                                }else if($customerVerification->status == 'failed'){
                                    $btnClass = 'btn btn-danger';
                                } else {
                                    $btnClass = 'btn btn-warning';
                                }
                            @endphp
                            {{ Form::text('verification-status', ucfirst($customerVerification->status), [ 'class' => $btnClass, 'placeholder' => trans('Question'),'readonly','disabled' => true]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mt-3">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Form Details') }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        @foreach ($customerVerification->verify_customer_values as $customerFormValue)
                            <div class="col-lg-4">
                                @switch($customerFormValue['verify_form']['field_type'])
                                    @case('text')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            <input type="text" class="form-control" value="{{ $customerFormValue['value'] }}" disabled>
                                        </div>
                                        @break

                                    @case('textarea')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            <textarea class="form-control" disabled>{{ $customerFormValue['value'] }}</textarea>
                                        </div>
                                        @break

                                    @case('number')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            <input type="number" class="form-control" value="{{ $customerFormValue['value'] }}" disabled>
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            @foreach ($customerFormValue['verify_form']['form_fields_values'] as $option)
                                                <input type="checkbox" class="form-check-input"
                                                    @if(in_array($option->value, $customerFormValue->value))
                                                        checked
                                                    @endif
                                                    disabled>{{ $option->value }}
                                            @endforeach
                                        </div>
                                        @break

                                    @case('dropdown')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            <select class="form-select form-control-sm" disabled>
                                                @foreach ($customerFormValue['verify_form']['form_fields_values'] as $option)
                                                    <option value="{{ $option->value }}" {{ $customerFormValue->value == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @break

                                    @case('radio')
                                        <div class="form-group">
                                            <label>{{ $customerFormValue['verify_form']['name'] }}</label>
                                            @foreach ($customerFormValue['verify_form']['form_fields_values'] as $option)
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" name="{{ $customerFormValue['verify_form']['name'] }}" value="{{ $option->value }}" {{ $customerFormValue['value'] == $option->value ? 'checked' : '' }} disabled>
                                                    <label class="form-check-label">{{ $option->value }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('file')
                                        @switch($customerFormValue['file_type'])
                                        @case('image')
                                            <div class="form-group">
                                                <label>{{ $customerFormValue['verify_form']['name'] }} :- </label>
                                                @if(!empty($customerFormValue['value']))
                                                    <a href="{{ $customerFormValue['value'] }}" target="_blank">{{ __('File') }}</a>
                                                @endif
                                            </div>
                                            @break

                                        @case('pdf')
                                        @case('txt')
                                        @case('doc')
                                        @case('docx')
                                            <div class="form-group">
                                                <label>{{ $customerFormValue['verify_form']['name'] }} :- </label>
                                                @if(!empty($customerFormValue['value']))
                                                    <a href="{{ $customerFormValue['value'] }}" target="_blank" download>{{ __('Download File') }}</a>
                                                @endif
                                            </div>
                                            @break
                                        @endswitch
                                    @default
                                        @break
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
