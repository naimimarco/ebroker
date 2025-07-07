@extends('layouts.main')

@section('title')
    {{ __('Messages') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-md-12">
                <h4>@yield('title')</h4>

            </div>

        </div>
    </div>
@endsection

@section('content')
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <div class="container-fluid">
        <div class="panel messages-panel">
            <div class="contacts-list">
                <div class="tab-content">
                    <div id="inbox" class="contacts-outter-wrapper tab-pane active">

                        <div class="contacts-outter">
                            <ul class="list-unstyled contacts">
                                @foreach ($user_list as $key => $value)
                                    @empty($value->receiver)
                                        <li data-toggle="tab" data-target="#inbox-message-1"
                                            id="{{ 'tabs' . $value->sender->id }}"
                                            onclick="setallMessage({{ $value->property_id }}, {{ $value->sender->id }}, {{ $value->is_blocked_by_me }}, {{ $value->is_blocked_by_user }});"
                                            style="display: flex;">

                                            <img alt="" class="img-circle medium-image user-image" src="{{ $value->sender->profile ? $value->sender->profile : url('assets/images/faces/2.jpg') }}">

                                            <div class="vcentered info-combo">
                                                <h3 class="no-margin-bottom name"> <b>{{ $value->sender->name }}</b> </h3>

                                                <h5> {{ $value->property->title }}</h5>
                                            </div>

                                            {{-- Unread Count --}}
                                            @if($value->unread_count > 0)
                                                <div class="text-right unread-count">
                                                    <span class="badge rounded-pill bg-primary">{{ $value->unread_count }}</span>
                                                </div>
                                            @endif

                                        </li>
                                    @endempty
                                @endforeach

                                @foreach ($user_list as $key => $value)
                                    @empty($value->sender)
                                        <li data-toggle="tab" data-target="#inbox-message-1"
                                            id="{{ 'tabs' . $value->receiver->id }}"
                                            onclick="setallMessage({{ $value->property_id }}, {{ $value->receiver->id }}, {{ $value->is_blocked_by_me }}, {{ $value->is_blocked_by_user }});"
                                            style="display: flex;">

                                            <img alt="" class="img-circle medium-image user-image" src="{{ $value->receiver->profile ? $value->receiver->profile : url('assets/images/faces/2.jpg') }}">

                                            <div class="vcentered info-combo">
                                                <h3 class="no-margin-bottom name"> <b>{{ $value->receiver->name }} </b></h3>
                                                <h5> {{ $value->property->title }}</h5>
                                            </div>

                                            @if($value->unread_count > 0)
                                                {{-- Unread Count --}}
                                                <div class="text-right unread-count">
                                                    <span class="badge rounded-pill bg-primary">{{ $value->unread_count }}</span>
                                                </div>
                                            @endif

                                        </li>
                                    @endempty
                                @endforeach
                                @foreach ($otherUsers as $key => $value)
                                    <li data-toggle="tab" data-target="#inbox-message-1"
                                        id="{{ 'tabs' . $value['customer_id'] }}"
                                        onclick="setallMessage({{ $value['proeperty_id'] }}, {{ $value['customer_id'] }},{{$value['is_blocked_by_me']}},{{$value['is_blocked_by_user']}});"
                                        style="display: flex;">

                                        <img alt="" class="img-circle medium-image user-image"
                                            src="{{ $value['profile'] ? $value['profile'] : url('assets/images/faces/2.jpg') }}">

                                        <div class="vcentered info-combo">
                                            <h3 class="no-margin-bottom name"> <b>{{ $value['name'] }}</b> </h3>
                                            <h5> {{ $value['title'] }}</h5>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <div class="tab-content">

                <div class="tab-pane message-body" id="inbox-message-1">
                    <div class="chat_header">

                    </div>

                    <div class="message-chat" id="myscroll" style="background-color: #ffffff;">
                        <div class="chat-body myscroll" id="chat"></div>
                        <div class="chat-footer">
                            <div id="file-preview"></div>

                            <div class="blocked-user-message-div" style="display: none">
                                <span class="for-blocked-by-admin" style="display: none">
                                    {{ __("You have blocked the user to unblock ") }} <a style="cursor: pointer;" class="unblock-user-url unblock-user">{{ __("Click Here") }}</a>
                                </span>
                                <span class="for-blocked-by-user" style="display: none">
                                    {{ __("You have been blocked") }}
                                </span>
                            </div>
                            <form method="POST" id='chat_form'>
                                @csrf
                                <input type="hidden" id="blocked-by-admin">
                                <input type="hidden" id="blocked-by-user">
                                <input type="hidden" name="prop_id" id="prop_id">
                                <input type="hidden" name="inquiry_by" id="inquiry_by">
                                <input type="hidden" name="inquiry" id="inquiry">
                                <input type="hidden" name="receiver_id" id=receiver_id>
                                <input type="hidden" name="sender_id" id="sender_id" value="0">
                                <input type="hidden" name="apiKey" id="apiKey" value="{{ $firebase_settings['apiKey'] ? $firebase_settings['apiKey'] : '' }}">
                                <input type="hidden" name="authDomain" id="authDomain" value="{{ $firebase_settings['authDomain'] ? $firebase_settings['authDomain'] : '' }}">
                                <input type="hidden" name="projectId" id="projectId" value="{{ $firebase_settings['projectId'] ? $firebase_settings['projectId'] : '' }}">
                                <input type="hidden" name="storageBucket" id="storageBucket" value="{{ $firebase_settings['storageBucket'] ? $firebase_settings['storageBucket'] : '' }}">
                                <input type="hidden" name="messagingSenderId" id="messagingSenderId" value="{{ $firebase_settings['messagingSenderId'] ? $firebase_settings['messagingSenderId'] : '' }}">
                                <input type="hidden" name="appId" id="appId" value="{{ $firebase_settings['appId'] ? $firebase_settings['appId'] : '' }}">
                                <input type="hidden" name="measurementId" id="measurementId" value="{{ $firebase_settings['measurementId'] ? $firebase_settings['measurementId'] : '' }}">
                                <input type="text" class="send-message-text" id="Onmessage" placeholder="{{ __('Message') }}"/>

                                {{-- Audio Message --}}
                                <label class="audio-button">
                                    <input type="button" value="" id="start-btn" class="js-start" style="display: none">
                                    <input type="hidden" name="aud" id="aud" style="display:none">
                                    <span style="display: flex">
                                        <i class="bi bi-mic"></i>
                                        <h6 id='record' class="ml-2">{{ __('Recording') }}</h6>
                                    </span>
                                    <div class="audio-wrapper" style="display: none">
                                        <audio src="" controls class="js-audio audio" name="audio_file"></audio>
                                    </div>
                                </label>

                                {{-- Upload File Button --}}
                                <label class="upload-file">
                                    <input type="file" name="attachment" id="Homeattachment" accept="image/png,image/jpg,image/jpeg,application/pdf,application/msword">
                                    <i class="fa fa-paperclip"></i>
                                </label>
                                {{-- Send Button --}}
                                <button type="button" class="send-message-button chat-send btn-info" onclick="OnsendMessage();"> {{ __('Send') }} <i class="fa fa-send"></i></button>
                            </form>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    @endsection
    @section('script')
        <script>
            $(document).ready(function() {
                $("#file-preview").hide();
                $('.list-unstyled.contacts li:first').attr('class', 'active');
                $('.active').click();

                $('.send-message-text').keydown(function(e) {

                    if (e.keyCode == 13) {
                        e.preventDefault();
                        $('.send-message-button').click();
                    }
                });

                $('#record').hide();

                var isToggled = false;
                var apiKey = $('#apiKey').val();
                var authDomain = $('#authDomain').val();
                var projectId = $('#projectId').val();
                var storageBucket = $('#storageBucket').val();
                var messagingSenderId = $('#messagingSenderId').val();
                var measurementId = $('#measurementId').val();
            });

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



                btn = document.querySelector('#start-btn');

                startButton = document.querySelector('.js-start');
                stopButton = document.querySelector('.js-stop');

                // We'll get the user's audio input here.
                navigator.mediaDevices.getUserMedia({
                    audio: true // We are only interested in the audio
                }).then(stream => {

                    recorder = new MediaRecorder(stream);


                    recorder.ondataavailable = saveChunkToRecording;
                    recorder.onstop = saveRecording;
                });

                isToggled = false;
                $('#start-btn').click(function() {

                    var recordBtn = $('#start-btn');
                    recordBtn.removeClass('js-start');

                    if (!isToggled) {
                        recordBtn.removeClass('js-stop');
                        recordBtn.addClass('js-start');
                        isToggled = true;
                        $('#record').show();
                        recorder.start();
                    } else {
                        recordBtn.removeClass('js-start');
                        recordBtn.addClass('js-stop');
                        isToggled = false;
                        $('#record').hide();
                        recorder.stop();
                    }
                });

            })();



            // Add event listeners to the start and stop button


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
                                const fileUrl = item.attachment;
                                if(fileUrl == ""){
                                    file = "";
                                }else{
                                    // Identify file type
                                    let fileType;

                                    if (fileUrl.endsWith('.png') || fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg')) {
                                        fileType = 'image';
                                    } else if (fileUrl.endsWith('.pdf')) {
                                        fileType = 'pdf';
                                    } else if (fileUrl.endsWith('.doc') || fileUrl.endsWith('.docx')) {
                                        fileType = 'doc';
                                    } else {
                                        fileType = 'unknown'; // Handle unknown file types gracefully
                                    }

                                    // Generate content based on file type
                                    if (fileType === 'image') {
                                        file = '<img alt="Pic" src="' + fileUrl + '" style="height: 216px; width: 216px;"><br>';
                                    } else if (fileType === 'pdf' || fileType === 'doc') {
                                        const fileName = fileUrl.split('/').pop(); // Extract filename from URL
                                        file = `<a href="${fileUrl}" download="${fileName}">${fileName}</a><br>`;
                                    }
                                }
                                // if (item.attachment == "") {
                                //     file = "";
                                // } else {
                                //     file = '<img alt="Pic" src="' + item.attachment +
                                //         '" style="height: 216px;width: 216px;"/><br>'
                                // }
                                if (item.audio == "") {
                                    audio = "";

                                } else {

                                    audio = '<audio controls>' +
                                        '<source src="' + item.audio + '" type="audio/ogg">' +
                                        '<source src="' + item.audio + '" type="audio/mpeg">' +
                                        'Your browser does not support the audio element.' +
                                        '</audio>';

                                }

                                // Create a Message div according to condition
                                let adminProfileImage = "{{ !empty(Auth::user()->getRawOriginal('profile')) ? Auth::user()->profile : url('assets/images/faces/2.jpg') }}";
                                let dataMessageDiv;
                                if(item.message){
                                    dataMessageDiv = `<div class="message-text">  ${audio}  ${file}  ${item.message} </div>`
                                }else{
                                    dataMessageDiv = `<div class="message-text">  ${audio}  ${file} </div>`
                                }
                                if (item.sender_type == '0') {

                                    html +=
                                        `<div class="message my-message">
                                            <img alt="" class="img-circle medium-image user-image" src="${adminProfileImage}">
                                            <div class="message-body">
                                                <div class="message-body-inner" style="border-radius: 4px;background-color: #f5f5f4;padding:16px;">
                                                    ${dataMessageDiv}
                                                </div>
                                                <div style="background: #FFFFFF;">  ${item.time_ago} </div>
                                            </div>
                                        <br>
                                        </div>`;
                                } else {
                                    profile = item.sendeprofile ? item.sendeprofile : adminProfileImage;
                                    html +=
                                       `<div class="message info">
                                            <img alt="" class="img-circle medium-image user-image" src="${profile}">
                                            <div class="message-body">
                                                <div class="message-body-inner" style="border-radius: 4px;background-color: #ECF5F5;padding:16px;">
                                                    ${dataMessageDiv}
                                                </div>
                                                <div style="background: #FFFFFF;justify-content: end;display:flex;">${item.time_ago}</div>
                                            </div>
                                        <br>
                                        </div>`;
                                }

                            });

                            $("#chat").html(html);
                            $('.myscroll').animate({
                                scrollTop: $('.myscroll').get(0).scrollHeight
                            }, 1500);

                        } else {
                            // $("#Onmessage").html("");
                            $("#chat").html("");
                            $("#chat").append("No message");

                        }
                    }
                });
            }


            function OnsendMessage() {
                var submitButton = ($('.chat-send'));
                var submitButtonText = 'Send <i class="fa fa-send"></i>';

                submitButton.text('Sending...');
                submitButton.attr('disabled', 'disabled');

                var sender_by = $('#sender_id').val();
                var receive_by = $('#receiver_id').val();

                var message = $("#Onmessage").val();
                var attachment = $('#Homeattachment')[0].files;
                $('.progress').show();
                var fd = new FormData();

                fd.append('attachment', attachment[0] ?? "");
                fd.append('receiver_id', receive_by);
                fd.append('message', message);
                fd.append('property_id', $('#prop_id').val());
                fd.append('sender_type', 0);
                fd.append('sender_by', sender_by);
                fd.append('aud', $('#aud').val());


                if (message == "" && attachment.length == 0 && audio == "") {
                    showErrorToast(window.trans['Message not be empty']);
                    $("#Onmessage").val("");
                    submitButton.html(submitButtonText);
                    submitButton.removeAttr('disabled');
                    return false;
                }


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
                            getAllMessage(0, 10);
                            submitButton.html(submitButtonText);
                            submitButton.removeAttr('disabled');
                            $('#aud').val('');
                            $("#file-preview").hide();
                            showSuccessToast(data.message);
                        }
                        if (data.error == true) {
                            submitButton.html(submitButtonText);
                            submitButton.removeAttr('disabled');
                            showErrorToast(data.message);
                        }
                    },
                    error: function(data) {
                        submitButton.html(submitButtonText);
                        submitButton.removeAttr('disabled');
                        showErrorToast(data.responseJSON.message);
                    }
                });
            }


            function setallMessage(id, c_id, blockedByMe = 0, blockedByUser = 0) {
                $('.list-unstyled.contacts li.active').css('borderRight', '');
                $('#receiver_id').val(c_id);
                $('#prop_id').val(id);
                $("#chat").html("");
                $(".issueTitle").html("");

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
                        offset: 0,
                        limit: 10
                    },
                    async: true,
                    cache: false,
                    success: function(data) {
                        $(document).ready(function () {
                            if(blockedByMe == 1){
                                $("#chat_form").hide();
                                $(".blocked-user-message-div").show();
                                $(".for-blocked-by-admin").show();
                                $(".for-blocked-by-user").hide();
                                $('#block-user').hide();
                            }else if(blockedByUser == 1){
                                $("#chat_form").hide();
                                $(".blocked-user-message-div").show();
                                $(".for-blocked-by-admin").hide();
                                $(".for-blocked-by-user").show();
                                $('#block-user').show();
                            }else{
                                $("#chat_form").show();
                                $(".blocked-user-message-div").hide();
                                $(".for-blocked-by-admin").hide();
                                $(".for-blocked-by-user").hide();
                                $('#block-user').show();
                            }
                        })
                        if (data != '') {
                            // Get the active tab
                            var activeTab = $(document).find('#tabs' + c_id);
                            $('.list-unstyled.contacts li.active').css('borderRight',
                                '3px solid var(--bs-primary)');

                            // Check if the active tab exists and contains the desired elements
                            var username = activeTab.find('.username').html();
                            var img_src = activeTab.find('.user-image').attr('src');


                            if(activeTab.find('.unread-count').length > 0){
                                unread_count = activeTab.find('.unread-count').hide();
                            }



                            var chatHeader = document.querySelector('.chat_header');
                            if (chatHeader) {
                                let url = `{{ route('block-user', ':id') }}`.replace(':id', c_id);
                                chatHeader.innerHTML = `
                                    <div>
                                        <img alt="" class="img-circle medium-image user-image" src="${img_src ? img_src : ''}">
                                        <span class="ms-2 me-auto">${username ? username : ''}</span>
                                    </div>
                                    <div class="d-flex justify-content-end flex-direction-column">
                                        <div class="refresh-chats badge bg-success" data-property_id="${property_id}" data-sender_id="${c_id}" data-is_blocked_by_me="${blockedByMe}" data-is_blocked_by_user="${blockedByUser}">
                                            <i class="fa fa-refresh text-white" aria-hidden="true" title="{{ __('Refresh') }}"></i>
                                        </div>
                                        <div class="block-user badge bg-danger" data-url="${url}">
                                            <i class="fa fa-ban text-white" aria-hidden="true" title="{{ __('Block') }}"></i>
                                        </div>
                                    </div>
                                `;


                                let unblockUrl = `{{ route('unblock-user', ':id') }}`.replace(':id', c_id);
                                $(".blocked-user-message-div").find(".unblock-user-url").data("url",unblockUrl);
                            }




                            var html = '';
                            $("#chat").empty();


                            $.each(data.reverse(), function(i, item) {
                                const fileUrl = item.attachment;
                                if(fileUrl == ""){
                                    file = "";
                                }else{
                                    // Identify file type
                                    let fileType;

                                    if (fileUrl.endsWith('.png') || fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg')) {
                                        fileType = 'image';
                                    } else if (fileUrl.endsWith('.pdf')) {
                                        fileType = 'pdf';
                                    } else if (fileUrl.endsWith('.doc') || fileUrl.endsWith('.docx')) {
                                        fileType = 'doc';
                                    } else {
                                        fileType = 'unknown'; // Handle unknown file types gracefully
                                    }

                                    // Generate content based on file type
                                    if (fileType === 'image') {
                                        file = '<img alt="Pic" src="' + fileUrl + '" style="height: 216px; width: 216px;"><br>';
                                    } else if (fileType === 'pdf' || fileType === 'doc') {
                                        const fileName = fileUrl.split('/').pop(); // Extract filename from URL
                                        file = `<a href="${fileUrl}" download="${fileName}">${fileName}</a><br>`;
                                    }
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

                                // Create a Message div according to condition
                                let adminProfileImage = "{{ !empty(Auth::user()->getRawOriginal('profile')) ? Auth::user()->profile : url('assets/images/faces/2.jpg') }}";
                                let dataMessageDiv;
                                if(item.message){
                                    dataMessageDiv = `<div class="message-text">  ${audio}  ${file}  ${item.message} </div>`
                                }else{
                                    dataMessageDiv = `<div class="message-text">  ${audio}  ${file} </div>`
                                }
                                if (item.sender_type == '0') {

                                    html +=
                                        `<div class="message my-message">
                                            <img alt="" class="img-circle medium-image user-image" src="${adminProfileImage}">
                                            <div class="message-body">
                                                <div class="message-body-inner" style="border-radius: 4px;background-color: #f5f5f4;padding:16px;">
                                                    ${dataMessageDiv}
                                                </div>
                                                <div style="background: #FFFFFF;">  ${item.time_ago} </div>
                                            </div>
                                        <br>
                                        </div>`;
                                } else {
                                    profile = item.sendeprofile ? item.sendeprofile : adminProfileImage;
                                    html +=
                                       `<div class="message info">
                                            <img alt="" class="img-circle medium-image user-image" src="${profile}">
                                            <div class="message-body">
                                                <div class="message-body-inner" style="border-radius: 4px;background-color: #ECF5F5;padding:16px;">
                                                    ${dataMessageDiv}
                                                </div>
                                                <div style="background: #FFFFFF;justify-content: end;display:flex;">${item.time_ago}</div>
                                            </div>
                                        <br>
                                        </div>`;
                                }

                            });

                            $("#chat").html(html);
                            $('.myscroll').animate({
                                scrollTop: $('.myscroll').get(0).scrollHeight
                            }, 1500);

                        } else {
                            // Get the active tab
                            var activeTab = document.querySelector('#tabs' + c_id);


                            $('.list-unstyled.contacts li.active').css('borderRight',
                                '3px solid var(--bs-primary)');


                            var username = activeTab.childNodes[3].childNodes[1].innerHTML;
                            var img_src = activeTab.childNodes[1].src;



                            var chatHeader = document.querySelector('.chat_header');

                            if (chatHeader) {
                                let url = `{{ route('block-user', ':id') }}`.replace(':id', c_id);
                                chatHeader.innerHTML = `
                                    <div>
                                        <img alt="" class="img-circle medium-image user-image" src="${img_src ? img_src : ''}">
                                        <span class="ms-2 me-auto">${username ? username : ''}</span>
                                    </div>
                                    <div class="d-flex justify-content-end flex-direction-column">
                                        <div class="refresh-chats badge bg-success" data-property_id="${property_id}" data-sender_id="${c_id}" data-is_blocked_by_me="${blockedByMe}" data-is_blocked_by_user="${blockedByUser}">
                                            <i class="fa fa-refresh text-white" aria-hidden="true" title="{{ __('Refresh') }}"></i>
                                        </div>
                                        <div class="block-user badge bg-danger" data-url="${url}">
                                            <i class="fa fa-ban text-white" aria-hidden="true" title="{{ __('Block') }}"></i>
                                        </div>
                                    </div>
                                `;
                                let unblockUrl = `{{ route('unblock-user', ':id') }}`.replace(':id', c_id);
                                $(".blocked-user-message-div").find(".unblock-user-url").data("url",unblockUrl);
                            }

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
                if (payload.data.property_id == $('#prop_id').val()){

                    const fileUrl = payload.data.file;
                    if(fileUrl == ""){
                        file = "";
                    }else{
                        // Identify file type
                        let fileType;

                        if (fileUrl.endsWith('.png') || fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg')) {
                            fileType = 'image';
                        } else if (fileUrl.endsWith('.pdf')) {
                            fileType = 'pdf';
                        } else if (fileUrl.endsWith('.doc') || fileUrl.endsWith('.docx')) {
                            fileType = 'doc';
                        } else {
                            fileType = 'unknown'; // Handle unknown file types gracefully
                        }

                        // Generate content based on file type
                        if (fileType === 'image') {
                            file = '<img alt="Pic" src="' + fileUrl + '" style="height: 216px; width: 216px;"><br>';
                        } else if (fileType === 'pdf' || fileType === 'doc') {
                            const fileName = fileUrl.split('/').pop(); // Extract filename from URL
                            file = `<a href="${fileUrl}" download="${fileName}">${fileName}</a><br>`;
                        }
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
                    if(payload.data.user_profile){
                        profile = payload.data.user_profile;
                    }else{
                        profile = 'https://www.nicepng.com/png/detail/128-1280406_view-user-icon-png-user-circle-icon.png';
                    }
                    if (payload.data.type == 'chat') {
                        html1 = `<div class="message info">
                                    <img alt="" class="img-circle medium-image user-image" src="${profile}">
                                    <div class="message-body">
                                        <div class="message-body-inner" style="border-radius: 4px;background-color: #ECF5F5;padding:16px;">
                                            <div class="message-text">
                                                ${audio}${file}${payload.data.message} <br>
                                            </div>
                                        </div>
                                        <div style="background: #FFFFFF;justify-content: end;display:flex;">
                                            ${payload.data.time_ago}
                                        </div>
                                    </div>
                                    <br>
                                </div>`;

                        $("#chat").append(html1);
                        $('.myscroll').animate({
                            scrollTop: $('.myscroll').get(0).scrollHeight
                        }, 1500);
                    }
                }
            })
            $('#Homeattachment').change(function(e) {
            $("#file-preview").show();
            const file = e.target.files[0];

            // Check if a file is selected
            if (file) {
                // Check file type (optional)
                if (!isValidFileType(file)) {
                    alert('Invalid file type! Please select an image.');
                    return;
                }

                // Preview for images
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#file-preview').html(`
                            <div class="file-preview-with-close">
                                <img src="${e.target.result}" alt="Preview">
                                <button id="remove-file" class="btn btn-sm btn-primary"type="button">X</button>
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Display filename for other file types (optional)
                    $('#file-preview').html(`
                        <div class="file-preview-with-close">
                            <p>${file.name}</p>
                            <button id="remove-file" class="btn btn-sm btn-primary" type="button">X</button>
                        </div>
                    `);
                }

                // Add event listener for remove button
                $('#file-preview').on('click', '#remove-file', function() {
                    $('#Homeattachment').val('');  // Clear the input file
                    $('#file-preview').hide();  // Hide the preview div
                    $('#file-preview').html('');  // Clear the preview content
                });
            } else {
                // Clear preview if no file selected
                $('#file-preview').html('');
            }
        });

        function isValidFileType(file) {
            const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'application/pdf', 'application/msword'];
            return allowedTypes.includes(file.type);
        }


        $(document).on('click', '.refresh-chats', function () {
            property_id = $(this).data('property_id');
            sender_id = $(this).data('sender_id');
            is_blocked_by_me = $(this).data('is_blocked_by_me');
            is_blocked_by_user = $(this).data('is_blocked_by_user');
            setallMessage(property_id, sender_id, is_blocked_by_me, is_blocked_by_user)
        });
        </script>
    @endsection
