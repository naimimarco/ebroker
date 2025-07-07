@extends('layouts.main')

@section('title')
    {{ __('Terms & Conditions') }}
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

            <form action="{{ url('settings') }}" method="post">
                @csrf
                <div class="card-body">
                    <input name="type" value="terms_conditions" type="hidden">
                    <div class="row form-group">
                        <div class="col-12 d-flex justify-content-end">
                            <a href="{{ route('customer-terms-conditions') }}"col-sm-12 col-md-12 d-fluid
                                class="btn icon btn-primary btn-sm rounded-pill" onclick="" title="Enable"><i
                                    class="bi bi-eye-fill"></i></a>
                        </div>
                        <div class="col-md-12 mt-3">
                            <textarea id="tinymce_editor" name="data" class="form-control col-md-7 col-xs-12">{{ $data }}</textarea>

                        </div>

                    </div>
                    <div class="col-12 d-flex justify-content-end">

                        <button class="btn btn-primary me-1 mb-1" type="submit" name="submit">{{ __('Save') }}</button>

                    </div>
                </div>
                {{-- <div class="card-footer"> --}}


                {{-- </div> --}}
            </form>
        </div>
    </section>
@endsection
