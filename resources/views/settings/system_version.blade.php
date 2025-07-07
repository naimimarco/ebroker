@extends('layouts.main')

@section('title')
    {{ __('System Update') }}
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
            <div class="card-body">
                <div class="row mt-1">
                    <form class="form" action="{{ url('system-version-setting') }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <label class="col-sm-12 col-form-label text-center">{{ __('System Version') }} {{ system_setting('system_version') != '' ? system_setting('system_version') : '1.0.0' }}</label>
                        <div class="form-group row mt-5">
                            {{-- Purchase code --}}
                            <label class="col-sm-2 col-form-label text-center">{{ __('Purchase Code') }}</label>
                            <div class="col-sm-3">
                                <input required name="purchase_code" type="text" class="form-control">
                            </div>

                            {{-- Upload zip file --}}
                            <label class="col-sm-2 col-form-label text-center">{{ __('Update File') }}</label>
                            <div class="col-sm-3">
                                <input required name="file" type="file" class="form-control" accept=".zip">
                            </div>
                            {{-- Submit --}}
                            <div class="col-sm-2 d-flex justify-content-end">
                                <button type="submit" name="btnAdd1" value="btnAdd" class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div>
            @if(system_setting('system_version') == "1.2.3")
                    <p class="alter alert-danger rounded p-2"><strong>Note :- </strong> As part of our system update to version 1.2.3, we have migrated old packages to our new package system. Please note that old packages are currently disabled, and now only active packages will be available for subscription. However, users will still be able to view all previously subscribed packages, even if they are inactive.</p>
                @endif
        </div>
    </section>
@endsection

@section('script')
@endsection
