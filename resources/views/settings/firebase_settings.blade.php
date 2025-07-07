@extends('layouts.main')

@section('title')
    {{ __('Firebase Settings') }}
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

            <form class="form" action="{{ url('firebase-settings') }}" method="POST"> {{ csrf_field() }}
                <div class="card-body">
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="form-group row">

                                {{-- Api Key --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Api Key') }}</label>
                                <div class="col-sm-4">
                                    <input name="apiKey" type="text" class="form-control" placeholder="{{ __('Api Key') }}" id="apiKey" value="{{ system_setting('apiKey') != '' ? system_setting('apiKey') : '' }}" required="">
                                </div>

                                {{-- Auth Domain --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Auth Domain') }}</label>
                                <div class="col-sm-4">
                                    <input required name="authDomain" type="text" class="form-control" placeholder="{{ __('Auth Domain') }}" id="authDomain" value="{{ system_setting('authDomain') != '' ? system_setting('authDomain') : '' }}">
                                </div>

                                {{-- Project ID --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Project ID') }}</label>
                                <div class="col-sm-4">
                                    <input name="projectId" type="text" class="form-control" placeholder="{{ __('Project ID') }}" id="projectId" value="{{ system_setting('projectId') != '' ? system_setting('projectId') : '' }}" required="">
                                </div>

                                {{-- Storage Bucket --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Storage Bucket') }}</label>
                                <div class="col-sm-4">
                                    <input name="storageBucket" type="text" class="form-control" id="storageBucket" placeholder="{{ __('Storage Buckets') }}" value="{{ system_setting('storageBucket') != '' ? system_setting('storageBucket') : '' }}" required="">
                                </div>

                                {{-- Messaging Sender ID --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Messaging Sender ID') }}</label>
                                <div class="col-sm-4">
                                    <input name="messagingSenderId" type="text" class="form-control" placeholder="{{ __('Messaging Sender ID') }}" id="messagingSenderId" value="{{ system_setting('messagingSenderId') != '' ? system_setting('messagingSenderId') : '' }}" required="">
                                </div>

                                {{-- App ID --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('App ID') }}</label>
                                <div class="col-sm-4">
                                    <input name="appId" id="appId" type="text" class="form-control" placeholder="{{ __('App ID') }}" value="{{ system_setting('appId') != '' ? system_setting('appId') : '' }}" required="">
                                </div>

                                {{-- Measurement ID --}}
                                <label class="col-sm-2 col-form-label text-center mt-2">{{ __('Measurement ID') }}</label>
                                <div class="col-sm-4">
                                    <input name="measurementId" type="text" class="form-control" id="measurementId" placeholder="{{ __('Measurement ID') }}" value="{{ system_setting('measurementId') != '' ? system_setting('measurementId') : '' }}" required="">
                                </div>
                            </div>
                        </div>

                        {{-- Save --}}
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" name="btnAdd1" value="btnAdd" id="btnAdd1" class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
                        </div>
                        {{-- <button type="button" id="btn1">click</button> --}}
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('script')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>
        $(document).ready(function() {

            var apiKey = $('#apiKey').val();
            var authDomain = $('#authDomain').val();
            var projectId = $('#projectId').val();
            var storageBucket = $('#storageBucket').val();
            var messagingSenderId = $('#messagingSenderId').val();
            var measurementId = $('#measurementId').val();

            $('#btnAdd1').on('click', function(e) {

                console.log('click');





                var s = "importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');" +
                    "\n" +
                    " importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');" +
                    "\n" +

                    " const firebaseConfig = {" +
                    "apiKey:'" + $('#apiKey').val() + "'," + "\n" +
                    "authDomain:'" + $('#authDomain').val() + "'," + "\n" +
                    "projectId:'" + $('#projectId').val() + "'," + "\n" +
                    "storageBucket:'" + $('#storageBucket').val() + "'," + "\n" +
                    "messagingSenderId:'" + $('#messagingSenderId').val() + "'," + "\n" +
                    "appId:'" + $('#appId').val() + "'," + "\n" +
                    "measurementId:'" + $('#measurementId').val() + "'," + "\n" +
                    " };" + "\n" +


                    "if (!firebase.apps.length) {" + "\n" +

                    " firebase.initializeApp(firebaseConfig);" + "\n" +
                    " }" + "\n" +

                    "const messaging = firebase.messaging();" + "\n" +

                    "messaging.setBackgroundMessageHandler(function(payload) {" + "\n" +
                    "console.log(payload);" + "\n" +

                    " var title = payload.data.title;" + "\n" +

                    "var options = {" + "\n" +
                    "body: payload.data.body," + "\n" +
                    "icon: payload.data.icon," + "\n" +

                    "data: {" + "\n" +
                    " time: new Date(Date.now()).toString()," + "\n" +
                    " click_action: payload.data.click_action" + "\n" +
                    " }" + "\n" +
                    "};" + "\n" +

                    "return self.registration.showNotification(title, options);" + "\n" +
                    " });" + "\n" +

                    "self.addEventListener('notificationclick', function(event) {" + "\n" +
                    " var action_click = event.notification.data.click_action;" + "\n" +
                    "event.notification.close();" + "\n" +

                    "event.waitUntil(" + "\n" +
                    "clients.openWindow(action_click)" + "\n" +
                    " );" + "\n" +
                    "});";
                var filename = 'foobar.txt';

                var formData = new FormData();
                formData.append('file', new File([new Blob([s])], filename));
                // formData.append('another-form-field', 'some value');

                $.ajax({
                    url: 'firebase_messaging_settings',
                    data: formData,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: function() {
                        console.log('ok');
                    },
                    error: function() {
                        console.log('err'); // replace with proper error handling
                    }
                });



                // Add file content in the object URL
                link.href = URL.createObjectURL(file);

                // Add file name
                link.download = "sample.txt";

                // Add click event to <a> tag to save file.
                link.click();
                URL.revokeObjectURL(link.href);


            });
        });
    </script>
@section('script')
