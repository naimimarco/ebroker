@extends('layouts.main')

@section('title')
    {{ __('Property Enquiry') }}
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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="section">
        <div class="card">

            <div class="card-body">
                <div class="row justify-content-center" id="toolbar">


                    <div class="col-sm-12">

                        {{-- {{ Form::label('filter_status', 'Status', ['class' => 'form-label col-12 text-center']) }} --}}
                        {{-- <select id="filter_status" class="form-select form-control-sm">
                            <option value="">Select Status </option>
                            <option value="0">Pending</option>
                            <option value="1">Accept</option>
                            <option value="2">Complete</option>
                            <option value="3">Cancel</option>
                        </select> --}}

                        {{-- {!! Form::select(
                            'filter_status',
                            ['0' => 'Pending', '1' => 'Accept', '2' => 'Complate', '3' => 'Cancel'],
                            $status,
                            ['class' => 'form-select form-control-sm', 'id' => 'filter_status', 'placeholder' => 'Select Status'],
                        ) !!} --}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('getPropertyInquiryList') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="title" data-align="center" data-sortable="false">
                                        {{ __('Title') }}
                                    </th>
                                    <th scope="col" data-field="property_owner" data-align="center"
                                        data-sortable="false">{{ __('Owner Name') }}</th>
                                    <th scope="col" data-field="property_mobile" data-align="center"
                                        data-sortable="false">{{ __('Owner Mobile') }}</th>
                                    <th scope="col" data-field="name" data-align="center" data-sortable="false">
                                        {{ __('Inquiry By') }}</th>


                                    <th scope="col" data-field="location" data-align="center" data-sortable="false">
                                        {{ __('Location') }}</th>
                                    <th scope="col" data-field="email" data-align="center" data-sortable="false">
                                        {{ __('Email') }}
                                    </th>
                                    <th scope="col" data-field="mobile" data-align="center" data-sortable="false">
                                        {{ __('Mobile') }}
                                    </th>





                                    <th scope="col" data-field="inquiry_created" data-align="center"
                                        data-sortable="false">{{ __('Enquiries Posted') }}
                                    </th>

                                    <th scope="col" data-field="chat" data-align="center" data-sortable="false"
                                        data-events="actionEvents1">
                                        {{ __('Chat') }}
                                    </th>
                                    @if (has_permissions('update', 'property') || has_permissions('delete', 'property'))
                                        <th scope="col" data-field="operate" data-events="actionEvents"
                                            data-sortable="false">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </section>


    <div class="modal modal-sticky modal-sticky-bottom-center" id="chat_modal" role="dialog" data-backdrop="false">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <!--begin::Card-->
                <div class="card card-custom">
                    <!--begin::Header-->





                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel1">{{ __('Enquiries Chat') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>



                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body">
                        <div class="row">
                            <label class="col-sm-12 col-form-label text-center issueTitle"></label>
                        </div>

                        <!--begin::Scroll-->
                        <div id="myscroll" style="overflow-y: scroll;height: 400px">
                            <!--begin::Messages-->

                            <div class="messages" id="chat">

                            </div>
                            <!--end::Messages-->

                        </div>

                        <form action="" method="POST" id='chat_form'>
                            @csrf
                            <input type="hidden" name="prop_id" id="prop_id">
                            <input type="hidden" name="inquiry_by" id="inquiry_by">
                            <input type="hidden" name="inquiry" id="inquiry">

                            <input type="hidden" name="receiver_id" id=receiver_id>
                            <input type="hidden" name="sender_id" id="sender_id" value="0">

                            <!--begin::Compose-->
                            <div class='row mt-5'>

                                <textarea class="form-control  p-0" data-emoji-picker="true" rows="2" placeholder="Type a message"
                                    id="Onmessage" required></textarea>
                                {{-- <div class="d-flex align-items-center justify-content-between mt-5"> --}}
                            </div>
                            <div class='row mt-5'>
                                <div class="col-sm-12 col-md-3">

                                    <div class="mr-3">
                                        <div class="custom-file">
                                            <input type="file" name="attachment"
                                                class="custom-file-input form-control" id="Homeattachment" />

                                        </div>
                                    </div>

                                </div>


                                <div class="col-sm-12 col-md-2">

                                    <button class="js-start button button--start btn btn-primary" type="button"
                                        id="start-btn">{{ __('Start Recording') }}</button>


                                </div>
                                <div class="col-sm-12 col-md-2" style="display: none">
                                    <div class="audio-wrapper">
                                        <audio src="" controls class="js-audio audio" name="audio_file"></audio>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-2">
                                    <button class="js-stop button button--stop btn btn-primary" id='stop-btn'
                                        type="button">{{ __('Stop Recording') }}</button>
                                </div>




                                <input type="hidden" name="aud" id="aud" style="display:none">

                                <div class="col-sm-12 col-md-1">

                                    <button type="button"
                                        class="btn btn-primary btn-md text-uppercase font-weight-bold chat-send py-2 px-6"
                                        onclick="OnsendMessage();">{{ __('Send') }}</button>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-12 col-md-12 text-center">

                                    <p class="recorg_audio"></p>
                                </div>
                            </div>
                            {{-- </div> --}}
                        </form>
                        <!--begin::Compose-->
                    </div>
                    <!--end::Footer-->

                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>
    <!--end::Chat Panel-->




    <!-- VIEW PROPERTY MODEL -->
    <div id="ViewPropertyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">{{ __('View Property') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row ">
                        <div class="col-md-6 col-12 ">
                            <div class="col-md-12 col-12 form-group">
                                <label for="title" class="form-label col-12 text-center">{{ __('Title') }}</label>
                                <input class="form-control " placeholder="Title" id="title" readonly type="text">
                            </div>
                        </div>

                        <div class="col-md-6 col-12 ">
                            <div class="row ">
                                <div class="col-md-3 col-12 form-group">
                                    <label for="category"
                                        class="form-label col-12 text-center">{{ __('Category') }}</label>
                                    <input class="form-control " placeholder="Category" id="category" readonly
                                        type="text">
                                </div>
                                <div class="col-md-3 col-12 form-group">
                                    <label for="city"
                                        class="form-label col-12 text-center">{{ __('City') }}</label>
                                    <input class="form-control " placeholder="City" readonly="true" id="city"
                                        type="text">

                                </div>

                                <div class="col-md-3 col-12 form-group">
                                    <label for="country"
                                        class="form-label col-12 text-center">{{ __('Country') }}</label>
                                    <input class="form-control " placeholder="Country" id="country" readonly
                                        type="text">
                                </div>

                                <div class="col-md-3 col-12 form-group">
                                    <label for="state"
                                        class="form-label col-12 text-center">{{ __('State') }}</label>
                                    <input class="form-control " placeholder="State" id="state" readonly
                                        type="text">
                                </div>
                            </div>
                        </div>


                    </div>


                    <div class="row ">
                        <div class="col-md-6 col-12">
                            <div class="row">
                                <div class="col-md-6 col-12 form-group">
                                    <label for="property_type"
                                        class="form-label col-12 text-center">{{ __('Property Type') }}</label>
                                    <input class="form-control " placeholder="Property Type" id="property_type" readonly
                                        type="text">
                                </div>
                                <div class="col-md-6 col-12 form-group">
                                    <label for="price"
                                        class="form-label col-12 text-center">{{ __('Price') }}</label>
                                    <input class="form-control " placeholder="Price" id="price" readonly
                                        type="text">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="row">

                                <div class="col-md-4 col-12 form-group">
                                    <label for="latitude"
                                        class="form-label col-12 text-center">{{ __('Latitude') }}</label>
                                    <input class="form-control " placeholder="Latitude" id="latitude" readonly
                                        type="text">
                                </div>
                                <div class="col-md-4 col-12 form-group">
                                    <label for="longitude"
                                        class="form-label col-12 text-center">{{ __('Longitude') }}</label>
                                    <input class="form-control " placeholder="Longitude" id="longitude" readonly
                                        type="text">
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="row ">
                        <div class="col-md-12 col-12">
                            <div class="row">
                                <div class="col-md-6 col-12 form-group">
                                    <label for="client_address"
                                        class="form-label col-12 text-center">{{ __('Client Address') }}</label>
                                    <textarea class="form-control " placeholder="Client Address" rows="2" id="client_address" autocomplete="off"
                                        cols="50" readonly></textarea>
                                </div>
                                <div class="col-md-6 col-12 form-group">
                                    <label for="address"
                                        class="form-label col-12 text-center">{{ __('Address') }}</label>
                                    <textarea class="form-control " placeholder="Address" rows="2" id="address" autocomplete="off"
                                        cols="50" readonly></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row ">
                        <div class="col-md-12 col-12">
                            <label for="description"
                                class="form-label col-12 text-center">{{ __('Description') }}</label>
                            <p id="description"></p>
                        </div>

                    </div>
                </div>
                <input type="hidden" name="apiKey" id="apiKey"
                    value="{{ $firebase_settings['apiKey'] ? $firebase_settings['apiKey'] : '' }}">

                <input type="hidden" name="authDomain" id="authDomain"
                    value="{{ $firebase_settings['authDomain'] ? $firebase_settings['authDomain'] : '' }}">

                <input type="hidden" name="projectId" id="projectId"
                    value="{{ $firebase_settings['projectId'] ? $firebase_settings['projectId'] : '' }}">

                <input type="hidden" name="storageBucket" id="storageBucket"
                    value="{{ $firebase_settings['storageBucket'] ? $firebase_settings['storageBucket'] : '' }}">

                <input type="hidden" name="messagingSenderId" id="messagingSenderId"
                    value="{{ $firebase_settings['messagingSenderId'] ? $firebase_settings['messagingSenderId'] : '' }}">

                <input type="hidden" name="appId" id="appId"
                    value="{{ $firebase_settings['appId'] ? $firebase_settings['appId'] : '' }}">

                <input type="hidden" name="measurementId" id="measurementId"
                    value="{{ $firebase_settings['measurementId'] ? $firebase_settings['measurementId'] : '' }}">
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- VIEW PROPERTY MODEL -->
@endsection

@section('script')
    <script>
        $(document).ready(function() {

            $('.recorg_audio').text('');

            var apiKey = $('#apiKey').val();

            var authDomain = $('#authDomain').val();
            var projectId = $('#projectId').val();
            var storageBucket = $('#storageBucket').val();
            var messagingSenderId = $('#messagingSenderId').val();
            var measurementId = $('#measurementId').val();

        });
        $('.js-start').on('click', function() {
            $('.recorg_audio').text('Recording Audio...')

        });

        $('.js-stop').on('click', function() {
            $('.recorg_audio').text('Audio Recorded')


        });
        // We'll save all chunks of audio in this array.
        const chunks = [];

        // We will set this to our MediaRecorder instance later.
        let recorder = null;

        // We'll save some html elements here once the page has loaded.
        let audioElement = null;
        let startButton = null;
        let stopButton = null;

        /**
         * Save a new chunk of audio.
         * @param  {MediaRecorderEvent} event
         */
        const saveChunkToRecording = (event) => {
            chunks.push(event.data);
        };

        /**
         * Save the recording as a data-url.
         * @return {[type]}       [description]
         */
        const saveRecording = () => {
            const blob = new Blob(chunks, {
                type: 'audio/mp3; codecs=opus'
            });
            const url = URL.createObjectURL(blob);

            audioElement.setAttribute('src', url);
            const input = document.querySelector('.js-audio');
            input.value = url;

            // Convert Blob to data URL
            const reader = new FileReader();
            reader.onload = () => {
                const dataUrl = reader.result;
                const hiddenInput = document.querySelector('#aud');
                hiddenInput.value = dataUrl;
            };
            reader.readAsDataURL(blob);
        };


        /**
         * Start recording.
         */
        const startRecording = () => {
            recorder.start();
        };

        /**
         * Stop recording.
         */
        const stopRecording = () => {
            recorder.stop();
        };


        // Wait until everything has loaded
        (function() {
            audioElement = document.querySelector('.js-audio');
            startButton = document.querySelector('.js-start');
            stopButton = document.querySelector('.js-stop');

            // We'll get the user's audio input here.
            navigator.mediaDevices.getUserMedia({
                audio: true // We are only interested in the audio
            }).then(stream => {
                // Create a new MediaRecorder instance, and provide the audio-stream.
                recorder = new MediaRecorder(stream);

                // Set the recorder's eventhandlers
                recorder.ondataavailable = saveChunkToRecording;
                recorder.onstop = saveRecording;
            });

            // Add event listeners to the start and stop button
            startButton.addEventListener('mouseup', startRecording);

            stopButton.addEventListener('mouseup', stopRecording);
        })();


        var clipboard = new ClipboardJS('.CopyLocation');

        clipboard.on('success', function(e) {
            Toastify({
                text: 'Copied',
                duration: 1000,
                close: !0,
                backgroundColor: "#000000"
            }).showToast()
            e.clearSelection();
        });
    </script>
    <script>
        $('#filter_status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        })
        $(document).ready(function() {
            var params = new window.URLSearchParams(window.location.search);

            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
        });

        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#filter_status').val(),



            };
        }
        window.actionEvents1 = {

            'click .chatdata': function(e, value, row, index) {

                console.log(row);

                $('#inquiry_by').val(row.inquiry_by);
                $('#receiver_id').val(row.inquiry_by);
                $('#prop_id').val(row.property_id);


            }
        }

        function chatsqueryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#filter_status').val(),
                property_id: $('#prop_id').val(),
                inquiry_by: $('#inquiry').val(),

            };
        }

        function setValue(id) {
            //$('#editUserForm').attr('action', ($('#editUserForm').attr('action') +'/edit/'+id));
            $("#edit_id").val(id);
            $("#status").val($("#" + id).data('status')).trigger('change');
            $('#inquiry').val($('#inquiry_by').val());



        }


        function setValues(id) {
            $('#prop_id').val(id);
            $('#inquiry').val($('#inquiry_by').val());
            $('#table_list1').bootstrapTable('refresh');
        }

        function changeStatus() {
            var id = $("#edit_id").val();
            var status = $("#status").val();

            $.ajax({
                url: "{{ route('property-inquiry.updateStatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": status,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Inquiry Status Change successfully',
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                        $("#edit_id").val('');
                        $("#status").val(0).trigger('change');
                        $('#editModal').modal('toggle');
                    } else {
                        Toastify({
                            text: result.message,
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                }

            });
        }
    </script>

    <script>
        window.actionEvents = {
            'click .view-property': function(e, value, row, index) {
                $("#title").val(row.title);
                $("#category").val(row.category);
                $("#state").val(row.state);
                $("#city").val(row.city);
                $("#country").val(row.country);
                $("#state").val(row.state);
                $("#property_type").val(row.property_type);
                $("#price").val(row.price);
                $("#latitude").val(row.latitude);
                $("#longitude").val(row.longitude);
                $("#client_address").val(row.client_address);
                $("#address").val(row.address);
                $("#description").html(row.description);
            },
            'click .view-chat': function(e, value, row, index) {
                console.log(row);
                $("#prop_id").val(row.property_id);
                $('#receiver_id').val(row.user);
                $('#property_id').val(row.property_id);


            }


        };


        function OnsendMessage() {
            var submitButton = ($('.chat-send'));

            submitButton.text('Sending...');

            var sender_by = $('#sender_id').val();
            var receive_by = $('#receiver_id').val();

            var message = $("#Onmessage").val();
            var attachment = $('#Homeattachment')[0].files;
            $('.progress').show();
            var fd = new FormData();

            console.log(attachment);

            fd.append('attachment', attachment[0]);
            fd.append('receiver_id', receive_by);
            fd.append('message', message);
            fd.append('property_id', $('#prop_id').val());
            fd.append('sender_type', 0);
            fd.append('sender_by', sender_by);
            fd.append('aud', $('#aud').val());


            console.log("success");
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = ((evt.loaded / evt.total) * 100);
                            $(".progress-bar").width(percentComplete + '%');
                            $(".progress-bar").html(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: 'store_chat',
                enctype: 'multipart/form-data',
                type: "POST",
                dataType: 'json',
                data: fd,
                processData: false,
                contentType: false,
                async: true,
                cache: false,

                success: function(data) {

                    if (data.error == false) {

                        $('#Homeattachment').val('');
                        $('.custom-file-label').html('');
                        $("#Onmessage").val("");
                        console.log("success");

                        getAllMessage(0, 10);
                        submitButton.text(__('Send'));
                        $('#aud').val('');

                    }
                }
            });
        }

        function getAllMessage(offset, limit) {
            $.ajax({
                url: 'getAllMessage',
                type: "GET",
                dataType: 'json',
                data: {
                    property_id: $('#prop_id').val(),
                    client_id: $('#receiver_id').val(),
                    offset: offset,
                    limit: limit
                },
                async: true,
                cache: false,
                success: function(data) {

                    if (data != '') {
                        var html = '';
                        $("#chat").empty();


                        $.each(data.reverse(), function(i, item) {
                            console.log(item);

                            if (item.attachment == "") {
                                file = "";
                            } else {
                                file = '<img alt="Pic" src="' + item.attachment +
                                    '" style="height: 216px;width: 216px;"/><br>'
                            }
                            if (item.audio == "") {
                                audio = "";

                            } else {

                                audio = '<audio controls>' +
                                    '<source src="' + item.audio + '" type="audio/ogg">' +
                                    '<source src="' + item.audio + '" type="audio/mpeg">' +
                                    'Your browser does not support the audio element.' +
                                    '</audio>';

                            }

                            if (item.sender_type == '0') {
                                html += '<div class="d-flex flex-column mb-5 align-items-end">' +
                                    '<div class="d-flex align-items-center">' +
                                    '<div class="mt-2 rounded p-3 bg-light-primary text-dark-50 font-weight-bold font-size-sm  max-w-400px">' +
                                    audio + file + item.message +
                                    '</div>' +
                                    '</div>' +
                                    '<div>' +
                                    '<span  class="text-dark-75 text-hover-primary font-weight-bold font-size-sm">' +
                                    item.ssendername + '</span>' +
                                    '&nbsp;<span class="text-muted font-size-sm">' + item.time_ago +
                                    ' </span></div></div>';


                            } else {
                                html += '<div class="d-flex flex-column mb-5 align-items-start">' +
                                    ' <div class="d-flex align-items-center">' +
                                    ' <div>' +
                                    '<div class="mt-2 rounded p-3 bg-light-success text-dark-50 font-weight-bold font-size-sm text-left max-w-400px">' +
                                    audio + file + item.message + '</div>' +
                                    '    <span  class="text-dark-75 text-hover-primary font-weight-bold font-size-sm">' +
                                    item.sendername + '</span>' +
                                    '   <span class="text-muted font-size-sm"> ' + item.time_ago +
                                    '</span>' +
                                    '</div>' +
                                    ' </div>' +
                                    '</div>';

                            }

                        });

                        $("#chat").html(html);

                    } else {
                        // $("#Onmessage").html("");
                        $("#chat").html("");
                        $("#chat").append("No message");

                    }
                }
            });
        }

        function setallMessage(id, c_id) {
            $("#chat").html("");
            $(".issueTitle").html("");
            console.log("client:" + c_id);
            // var issue = atob($('#' + id).data('issue'));
            property_id = id;
            client_id = c_id;

            $.ajax({
                url: 'getAllMessage',
                type: "GET",
                dataType: 'json',
                data: {
                    property_id: property_id,
                    client_id: c_id,
                    saveChunkToRecording
                    offset: 0,
                    limit: 10
                },
                async: true,
                cache: false,
                success: function(data) {

                    if (data != '') {
                        var html = '';
                        $("#chat").empty();


                        $.each(data.reverse(), function(i, item) {


                            if (item.attachment == "") {
                                file = "";
                            } else {
                                file = '<img alt="Pic" src="' + item.attachment +
                                    '"style="height: 216px;width: 216px;"/><br>'
                            }
                            if (item.audio == "") {
                                audio = "";
                            } else {

                                audio = '<audio controls>' +
                                    '<source src="' + item.audio + '" type="audio/ogg">' +
                                    '<source src="' + item.audio + '" type="audio/mpeg">' +
                                    'Your browser does not support the audio element.' +
                                    '</audio>';

                            }

                            if (item.sender_type == '0') {
                                html += '<div class="d-flex flex-column mb-5 align-items-end">' +
                                    '<div class="d-flex align-items-center">' +
                                    '<div class="mt-2 rounded p-3 bg-light-primary text-dark-50 font-weight-bold font-size-sm  max-w-400px">' +
                                    audio + file + item.message +
                                    '</div>' +
                                    '</div>' +
                                    '<div>' +
                                    '<span  class="text-dark-75 text-hover-primary font-weight-bold font-size-sm">' +
                                    item.ssendername + '</span>' +
                                    '&nbsp;<span class="text-muted font-size-sm">' + item.time_ago +
                                    ' </span></div></div>';

                            } else {
                                html += '<div class="d-flex flex-column mb-5 align-items-start">' +
                                    ' <div class="d-flex align-items-center">' +
                                    ' <div>' +
                                    '<div class="mt-2 rounded p-3 bg-light-success text-dark-50 font-weight-bold font-size-sm text-left max-w-400px">' +
                                    audio + file + item.message + '</div>' +
                                    '    <span  class="text-dark-75 text-hover-primary font-weight-bold font-size-sm">' +
                                    item.sendername + '</span>' +
                                    '   <span class="text-muted font-size-sm"> ' + item.time_ago +
                                    '</span>' +
                                    '</div>' +
                                    ' </div>' +
                                    '</div>';

                            }

                        });

                        $("#chat").html(html);
                        $('#myscroll').animate({
                            scrollTop: $('#myscroll').get(0).scrollHeight
                        }, 1500);
                    } else {
                        // $("#Onmessage").html("");
                        $("#chat").html("");
                        $("#chat").append("No message");

                    }
                }
            });
        }
    </script>

    <script type="text/javascript">
        messaging.onMessage(function(payload) {
            console.log(payload);


            if (payload.data.property_id == $('#prop_id').val()) {


                if (payload.data.file == "") {
                    file = "";
                } else {
                    file = '<img alt="Pic" src="' + payload.data.file +
                        '" /><br>'
                }
                if (payload.data.audio == "") {
                    audio = "";
                } else {
                    audio = '<audio controls>' +
                        '<source src="' + payload.data.audio + '" type="audio/ogg">' +
                        '<source src="' + payload.data.audio + '" type="audio/mpeg">' +
                        'Your browser does not support the audio element.' +
                        '</audio>';


                }

                if (payload.data.type == 'chat') {

                    html1 = '<div class="d-flex flex-column mb-5 align-items-start">' +

                        ' <div>' +
                        '<div class="mt-2 rounded p-3 bg-light-success text-dark-50 font-weight-bold font-size-sm text-left max-w-400px">' +

                        audio + file + payload.data.message + '</div>' +
                        '    <span  class="text-dark-75 text-hover-primary font-weight-bold font-size-sm">' +
                        payload
                        .data.username + '</span>' +
                        '   <span class="text-muted font-size-sm"> ' + payload.data.time_ago + '</span>' +
                        '</div>' +
                        ' </div>' +
                        '</div>';
                    $("#chat").append(html1);
                    $('#myscroll').animate({
                        scrollTop: $('#myscroll').get(0).scrollHeight
                    }, 1500);
                }
            }
        })
        $('.chat-send').on('click', function(e) {
            console.log("submit");
            var submitButton = ($('.chat-send'));

            $(this).append('<span>submitting...</span>'); //Replace with whatever you want
            // submitButton.css('display', 'none');
        })
    </script>
@endsection
