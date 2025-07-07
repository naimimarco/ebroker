<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="{{ url('assets/images/logo/' . (system_setting('favicon_icon') ?? null)) }}" type="image/x-icon">
    <title>{{ __('Reset Password') }}|| {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @include('layouts.include')
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-8" style="margin-top:50px">
                <div class="card">

                    <div class="card-body">
                    <form method="POST" class="create-form" action="{{ route('customer.reset-password') }}" data-success-function="formSuccessFunction">
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="row mb-3">
                                {{-- Password --}}
                                <label for="password" class="col-form-label">{{ __('Password') }}</label>
                                <div class="col-12">
                                    <input id="password" type="password" class="form-control" name="password" placeholder="{{ __('Password') }}" required>
                                </div>

                                {{-- Re-Password --}}
                                <label for="re-password" class="col-form-label">{{ __('Re-Password') }}</label>
                                <div class="col-12">
                                    <input id="re-password" type="password" class="form-control" name="re_password" placeholder="{{ __('Re-Password') }}" required>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layouts.footer_script')
<script>
    function formSuccessFunction(response) {
        if(!response.error){
            setTimeout(() => {
                let url = "{{ route('home') }}"
                window.location.replace(url);
            }, 500);
        }
    }
</script>
</html>
