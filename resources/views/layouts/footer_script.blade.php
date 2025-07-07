<script src="{{ url('assets/js/bootstrap.js') }}"></script>

{{-- Call Language Route for JS --}}
<script src="{{url('/js/lang')}}"></script>
<script type="text/javascript" src="{{ url('/assets/js/axios.min.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/firebase/firebase-app-8-10-0.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/firebase/firebase-messaging-8-10-0.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/jquery-3-1-1.min.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/filepond/filepond.js') }}"></script>
<script type="text/javascript" src="{{ url('/assets/js/js-color.min.js') }}"></script>

<script src="{{ url('/assets/js/jquery.repeater.js') }}"></script>

<script type="text/javascript" src="{{ url('/assets/js/filepond/filepond.jquery.js') }}"></script>

<script src="{{ url('assets/js/custom/firebase_config.js') }}"></script>

<script src="{{ url('assets/js/bootstrap-3-3-7.min.js') }}"></script>

<script src="{{ url('assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ url('assets/js/dragula.js') }}"></script>

<script src="{{ url('assets/js/app.js') }}"></script>
<script src="{{ url('assets/extensions/tinymce/tinymce.min.js') }}"></script>
<script src="{{ url('assets/js/custom/function.js') }}"></script>
<script src="{{ url('assets/js/custom/common.js') }}"></script>
<script src="{{ url('assets/js/custom/custom.js') }}"></script>
<script src="{{ url('assets/js/custom/formatter.js') }}"></script>
<script src="{{ url('assets/js/custom/validate.js') }}"></script>

<script src="{{ url('assets/js/jquery-jvectormap-2.0.5.min.js') }}"></script>
<script src="{{ url('assets/js/jquery-jvectormap-asia-merc.js') }}"></script>
<script src="{{ url('assets/js/query-jvectormap-world-mill-en.js') }}"></script>
<script src="{{ url('assets/js/jquery-jvectormap-world-mill.js') }}"></script>

<script src="{{ url('assets/extensions/toastify-js/src/toastify.js') }}"></script>
<script src="{{ url('assets/extensions/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ url('assets/js/pages/parsley.js') }}"></script>
<script src="{{ url('assets/extensions/bootstrap-table/bootstrap-table.min.js') }}"></script>
<script src="{{ url('assets/extensions/bootstrap-table/reorder-rows.min.js') }}"></script>
<script src="{{ url('assets/extensions/bootstrap-table/jquery.tablednd.min.js') }}"></script>
<script src="{{ url('assets/extensions/bootstrap-table/fixed-columns/bootstrap-table-fixed-columns.min.js') }}"></script>
<script src="{{ url('assets/extensions/bootstrap-table/mobile/bootstrap-table-mobile.min.js') }}"></script>

<script src="{{ url('assets/extensions/magnific-popup/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ url('assets/extensions/select2/dist/js/select2.min.js') }}"></script>

<script src="{{ url('assets/extensions/jquery-ui/jquery-ui.js') }}"></script>

<script src="{{ url('assets/extensions/clipboardjs/dist/clipboard.min.js') }}"></script>

<script src="{{ asset('assets/js/chosen.jquery.min.js') }}"></script>

<script src="{{ asset('assets/js/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond-plugin-image-preview.min.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond-plugin-pdf-preview.min.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond-plugin-file-validate-size.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond-plugin-file-validate-type.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond-plugin-image-validate-size.js') }}"></script>
<script src="{{ asset('assets/js/filepond/filepond.jquery.js') }}"></script>

<script src="{{ asset('assets/js/tagify-4-15-2.min.js') }}"></script>

<script>
    if (document.getElementById("meta_tags") != null) {
        $(document).ready(function() {
            var input = document.querySelector('input[id=meta_tags]');
            new Tagify(input)
        });
    }

    if (document.getElementById("edit_meta_tags") != null) {
        $(document).ready(function() {
            var input = document.querySelector('input[id=edit_meta_tags]');
            new Tagify(input)
        });
    }
</script>
<script>
    // Retrieve the value from the .env file in Laravel
    const fillColor = "{{ env('PRIMARY_COLOR') }}";
</script>

<script>
    // Set the CSS custom property using JavaScript
    var primarycolor = "{{ env('PRIMARY_COLOR') }}";

    document.documentElement.style.setProperty('--bs-primary', primarycolor);

    var rgbaprimarycolor = "{{ env('PRIMARY_RGBA_COLOR') }}";

    document.documentElement.style.setProperty('--primary-rgba', rgbaprimarycolor);
</script>

@if (Session::has('success'))
    <script type="text/javascript">
        Toastify({
            text: '{{ Session::get('success') }}',
            duration: 6000,
            close: !0,
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
        }).showToast()
    </script>
@endif

@if (Session::has('error'))
    <script type="text/javascript">
        Toastify({
            text: '{{ Session::get('error') }}',
            duration: 6000,
            close: !0,
            backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
        }).showToast()
    </script>
@endif
@if ($errors->any())
    <script type="text/javascript">
        Toastify({
            text: "{{ implode(', ', $errors->all()) }}",
            duration: 6000,
            close: true,
            backgroundColor: '#dc3545'
        }).showToast();
    </script>
@endif
