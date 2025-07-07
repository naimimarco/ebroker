@php

$lang = Session::get('language');

@endphp

@if ($lang)
@if ($lang->rtl)




<link rel="stylesheet" href="{{ url('assets/css/main/rtl.css') }}">
<link rel="stylesheet" href="{{ url('assets/css/pages/otherpages_rtl.css') }}" />
@else
<link rel="stylesheet" href="{{ url('assets/css/main/app.css') }}">
<link rel="stylesheet" href="{{ url('assets/css/pages/otherpages.css') }}" />
@endif
@else
<link rel="stylesheet" href="{{ url('assets/css/main/app.css') }}">
<link rel="stylesheet" href="{{ url('assets/css/pages/otherpages.css') }}" />
@endif
<link href="{{ url('assets/css/bootstrap/bootstrap-switch-button.min.css') }}" rel="stylesheet">
<link href="{{ url('assets/extensions/toastify-js/src/toastify.css') }}" rel="stylesheet">
<link href="{{ url('/assets/extensions/bootstrap-table/bootstrap-table.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('/assets/extensions/bootstrap-table/fixed-columns/bootstrap-table-fixed-columns.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('/assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/extensions/magnific-popup/magnific-popup.css') }}" rel="stylesheet">
<link href="{{ url('assets/extensions/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ url('assets/extensions/select2/dist/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
<link href="{{ url('assets/extensions/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet"/>
<link href="{{ url('assets/extensions/chosen.css') }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/filepond/filepond.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/filepond/filepond-plugin-image-preview.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/filepond/filepond-plugin-pdf-preview.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/pages/jquery-jvectormap-2.0.5.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/pages/owl.carousel.min.css') }}" rel="stylesheet" type="text/css" />

<style>


    .fontawesome-icons {
        text-align: center;
    }

    article dl {
        background-color: rgba(0, 0, 0, 0.02);
        padding: 20px;
    }

    .fontawesome-icons .the-icon {
        font-size: 24px;
        line-height: 1.2;
    }
</style>
