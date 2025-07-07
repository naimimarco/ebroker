@extends('layouts.main')

@section('title')
    {{ __('Change Profile') }}
@endsection

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Change Profile') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <div class="row">
                    <div class="col-12">
                        {{ Form::open(['url' => route('updateprofile'), 'id' => '#form', 'files' => true]) }}
                        <div class="row mt-1">
                            <div class="card-body">

                                {{-- Name --}}
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label text-alert text-center">{{ __('Name') }}</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="name" class="form-control form-control-lg form-control-solid mb-2" placeholder={{ __('Name') }} value="{{ Auth::user()->name }}" required />
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="form-group row">
                                    <label
                                        class="col-sm-4 col-form-label text-alert text-center">{{ __('Email') }}</label>
                                    <div class="col-sm-4">
                                        <input type="email" name={{ __('email') }}
                                            class="form-control form-control-lg form-control-solid mb-2"
                                            placeholder="Email" value="{{ Auth::user()->email }}" required />
                                    </div>
                                </div>

                                {{-- Profile Image --}}
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label text-alert text-center" for="profile-image">{{ __('Profile Image') }}</label>
                                    <div class="col-sm-4">
                                        <input type="file" class="filepond" id="profile-image" name="profile_image" accept="image/jpg,image/png,image/jpeg" required>
                                        @if (Auth::user()->getRawOriginal('profile'))
                                            <div style="width: 70px;">
                                                <a data-toggle='lightbox' href="{{ Auth::user()->profile }}" target="_blank"><img class="img-fluid w-70 mt-1" alt="" src="{{ Auth::user()->profile }}"/></a>
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                {{-- Change Button --}}
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label text-alert">&nbsp;</label>
                                    <div class="col-sm-4 text-end">
                                        <button type="submit" value="btnadd" class="btn btn-primary float-right">{{ __('Change') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $('#old_password').on('blur input', function() {
            var old_password = $(this).val();
            $.ajax({
                type: 'POST',
                url: "{{ route('checkpassword') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    old_password: old_password
                },
                beforeSend: function() {
                    $('#old_status').html('checking..');
                },
                success: function(result) {

                    if (result === "1") {
                        $('#old_status').html("");
                        $('#old_status').html(
                            "<i class='bi bi-check-circle-fill fs-2 text-success'></i>");
                        allowsubmit = true;
                    } else {
                        $('#old_status').html("");
                        $('#old_status').html("<i class='bi bi-x-circle-fill fs-2 text-danger'></i>");
                        $('#old_password').focus();
                        allowsubmit = false;
                    }
                }
            });

        });
    </script>

    <script>
        //on keypress
        $('#confPassword').keyup(function(e) {
            //get values
            var pass = $('#newPassword').val();
            var confpass = $(this).val();

            //check the strings
            if (pass == confpass) {
                //if both are same remove the error and allow to submit
                $('.error').text('');
                allowsubmit = true;
            } else {
                //if not matching show error and not allow to submit
                $('.error').text('Password not Matching');
                allowsubmit = false;
            }
        });

        //jquery form submit
        $('#form').submit(function() {

            var pass = $('#newPassword').val();
            var confpass = $('#confPassword').val();

            //just to make sure once again during submit
            //if both are true then only allow submit
            if (pass == confpass) {
                allowsubmit = true;
            }
            if (allowsubmit) {
                return true;
            } else {
                return false;
            }
        });
    </script>
@endsection
