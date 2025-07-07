@extends('layouts.main')

@section('title')
    {{ __('Change Password') }}
@endsection

@section('content')
    <section class="section">
        <div class="card">

            <div class="card-header">

                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Change Password') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card-content">

                <div class="row">
                    <div class="col-12">
                        {!! Form::open(['url' => route('changepassword.store'), 'data-parsley-validate', 'id' => 'form']) !!}
                        @csrf
                        <div class="row mt-1">

                            @if (Auth::user()->type == 0)
                                <div class="form-group row">
                                    <label
                                        class="col-sm-4 col-form-label text-alert text-center">{{ __('Name') }}</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="name"
                                            class="form-control form-control-lg form-control-solid mb-2"
                                            placeholder={{ __('Name') }} value="{{ Auth::user()->name }}" required
                                            readonly />
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row">
                                <label
                                    class="col-sm-4 col-form-label text-alert text-center">{{ __('Current Password') }}</label>
                                <div class="col-sm-4">
                                    <div class="form-group position-relative form-floating has-icon-right mb-1"
                                        id="pwd">
                                        <input type="password" name="current_password" id="old_password"
                                            class="form-control" placeholder="Current password" required />

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                        <div class="form-control-icon icon-right">
                                            <i class="bi bi-eye" id='toggle_pass'></i>
                                        </div>
                                    </div>
                                </div>
                                <label class="col-sm-4" id="old_status"></label>

                            </div>

                            <div class="form-group row">

                                <label
                                    class="col-sm-4 col-form-label text-alert text-center">{{ __('New Password') }}</label>

                                <div class="col-sm-4">
                                    <div class="form-group position-relative form-floating has-icon-right mb-1"
                                        id="pwd">
                                        <input type="password" name="newPassword" id="newPassword" class="form-control"
                                            placeholder="Current password" required />

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                        <div class="form-control-icon icon-right">
                                            <i class="bi bi-eye" id='new_toggle_pass'></i>
                                        </div>
                                    </div>
                                    <span class="error-password text-danger"></span>
                                </div>
                                <label class="col-sm-4" id="old_status"></label>

                            </div>
                            <div class="form-group row">
                                <label
                                    class="col-sm-4 col-form-label text-alert text-center">{{ __('Verify Password') }}</label>
                                <div class="col-sm-4">
                                    <div class="form-group position-relative form-floating has-icon-right mb-1"
                                        id="pwd">
                                        <input type="password" name="confPassword" id="confPassword" class="form-control"
                                            placeholder="Current password" required />

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                        <div class="form-control-icon icon-right">
                                            <i class="bi bi-eye" id='conf_toggle_pass'></i>
                                        </div>
                                    </div>
                                    <span class="error-password text-danger"></span>
                                </div>

                                {{-- <div class="col-sm-4">
                                    <input type="password" id="confPassword" name="confPassword"
                                        class="form-control form-control-lg form-control-solid" value=""
                                        placeholder="Verify password" required />
                                </div> --}}
                                <span class="error col-sm-4" style="color:red">
                                    @error('newPassword')
                                        {{ $message }}
                                    @enderror
                                    @error('confPassword')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label text-alert">&nbsp;</label>
                                <div class="col-sm-4 text-end">
                                    <button type="submit" name="btnadd" value="btnadd"
                                        class="btn btn-primary float-right">{{ __('Change') }}</button>
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
        $(document).ready(function() {

            $("#toggle_pass").click(function() {


                $(this).toggleClass("bi bi-eye bi-eye-slash");
                var input = $('[name="current_password"]');
                if (input.attr("type") == "password") {
                    input.attr("type", "text");

                } else {
                    input.attr("type", "password");
                }
            });




            $("#conf_toggle_pass").click(function() {


                $(this).toggleClass("bi bi-eye bi-eye-slash");
                var input = $('[name="confPassword"]');
                if (input.attr("type") == "password") {
                    input.attr("type", "text");

                } else {
                    input.attr("type", "password");
                }
            });


            $("#new_toggle_pass").click(function() {


                $(this).toggleClass("bi bi-eye bi-eye-slash");
                var input = $('[name="newPassword"]');
                if (input.attr("type") == "password") {
                    input.attr("type", "text");

                } else {
                    input.attr("type", "password");
                }
            });
        });
        $('#old_password').on('blur input', function() {
            var old_password = $(this).val();
            console.log(old_password);
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
                    console.log(result);
                    if (result.error == true) {
                        $('#old_status').html("");
                        $('#old_status').html(
                            "<i class='bi bi-check-circle-fill fs-2 text-success'></i>");
                    } else {
                        $('#old_status').html("");
                        $('#old_status').html("<i class='bi bi-x-circle-fill fs-2 text-danger'></i>");
                        $('#old_password').focus();
                        allowsubmit = false;
                    }

                },
                error: function error(error) {
                    $('#old_status').html("");
                    $('#old_status').html("<i class='bi bi-x-circle-fill fs-2 text-danger'></i>");
                    $('#old_password').focus();
                    allowsubmit = false;
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
            console.log(pass + "::" + confpass);
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
