 <header>
     <nav class="navbar navbar-expand navbar-light" style="background-color: white;">
         <div class="container-fluid">
             <a href="#" class="burger-btn d-block">
                 <i class="bi bi-justify fs-3"></i>
             </a>
            @php
                $emailConfigStatus = DB::table('settings')->select('data')->where('type','email_configuration_verification')->first();
                if($emailConfigStatus){
                    $data = $emailConfigStatus->data;
                }else{
                    $data = 0;
                }
            @endphp
            @if($data == 0)
                @if(has_permissions('update', 'email_configurations'))
                    <div class="mx-auto order-0">
                        <div class="alert alert-danger my-2" role="alert">
                            <i class="fa fa-exclamation"></i> {{ __("Email Configration is not verified") }} <a href="{{route('email-configurations-index')}}" class="alert-link">{{ __("Click here to redirect to email configration") }}</a>.
                        </div>
                    </div>
                @endif
            @endif
             <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                 data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                 aria-label="Toggle navigation">
                 <span class="navbar-toggler-icon"></span>
             </button>
             <div class="collapse navbar-collapse" id="navbarSupportedContent">
                 <div class="dropdown">
                     <a href="#" id="topbarUserDropdown"
                         class="user-dropdown d-flex align-items-center dropend dropdown-toggle"
                         data-bs-toggle="dropdown" aria-expanded="false">
                         <div class="avatar avatar-md2">
                             <i class="bi bi-translate"></i>
                         </div>
                         <div class="text">
                         </div>
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end topbarUserDropdown"
                         aria-labelledby="topbarUserDropdown">
                         @foreach (get_language() as $key => $language)
                             <li>
                                 <a class="dropdown-item"
                                     href="{{ url('set-language') . '/' . $language->code }}">{{ $language->name }}</a>
                             </li>
                         @endforeach
                         <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                             {{ csrf_field() }}

                         </form>
                     </ul>
                 </div>
                 &nbsp;&nbsp;
                 <div class="dropdown">
                    <a href="#" id="topbarUserDropdown" class="user-dropdown d-flex align-items-center dropend dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-md2">
                            <img src="{{ !empty(Auth::user()->getRawOriginal("profile")) ? Auth::user()->profile : url('assets/images/faces/2.jpg') }}">
                        </div>
                        <div class="text">
                            <h6 class="user-dropdown-name">{{ Auth::user()->name }}</h6>
                            <p class="user-dropdown-status text-sm text-muted"> </p>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end topbarUserDropdown" aria-labelledby="topbarUserDropdown">
                        {{-- Change Password --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('changepassword') }}">
                                <i class="icon-mid bi bi-gear me-2"></i>
                                Change Password
                            </a>
                        </li>

                        {{-- Change Profile --}}
                        @if (Auth::user()->type == 0)
                            <li>
                                <a class="dropdown-item" href="{{ route('changeprofile') }}">
                                    <i class="icon-mid bi bi-person me-2"></i>
                                    Change Profile
                                </a>
                            </li>
                        @endif

                        {{-- Logout --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }} " onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="icon-mid bi bi-box-arrow-left me-2"></i>
                                Logout
                            </a>
                        </li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        {{ csrf_field() }}
                        </form>
                     </ul>
                 </div>
             </div>
         </div>
     </nav>
 </header>
