<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-center">
                <div class="logo">
                    <a href="{{ url('home') }}">
                        <img src="{{ url('assets/images/logo/' . (system_setting('company_logo') ?? null)) }}" alt="Logo" srcset="">
                    </a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">

                {{-- Dashboard --}}
                @if (has_permissions('read', 'dashboard'))
                    <li class="sidebar-item">
                        <a href="{{ url('home') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span class="menu-item">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Facility --}}
                @if (has_permissions('read', 'facility'))
                    <li class="sidebar-item">
                        <a href="{{ url('parameters') }}" class='sidebar-link'>
                            <i class="bi bi-x-diamond"></i>
                            <span class="menu-item">{{ __('Facilities') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Categories --}}
                @if (has_permissions('read', 'categories'))
                    <li class="sidebar-item">
                        <a href="{{ url('categories') }}" class='sidebar-link'>
                            <i class="fas fa-align-justify"></i>
                            <span class="menu-item">{{ __('Categories') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Near by places --}}
                @if (has_permissions('read', 'near_by_places'))
                    <li class="sidebar-item">
                        <a href="{{ url('outdoor_facilities') }}" class='sidebar-link'>
                            <i class="bi bi-geo-alt"></i>
                            <span class="menu-item">{{ __('Near By Places') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Customer --}}
                @if (has_permissions('read', 'customer'))
                    <li class="sidebar-item">
                        <a href="{{ url('customer') }}" class='sidebar-link'>
                            <i class="bi bi-person-circle"></i>
                            <span class="menu-item">{{ __('Customer') }}</span>
                        </a>
                    </li>
                @endif


                {{-- Verify Users --}}
                @if (has_permissions('read', 'verify_customer_form') || has_permissions('read', 'approve_agent_verification'))
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-person-check"></i>
                            <span class="menu-item">{{ __('Verify Agent') }}</span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">

                            {{-- Custom Form --}}
                            @if (has_permissions('read', 'verify_customer_form'))
                                <li class="submenu-item">
                                    <a href="{{ route('verify-customer.form') }}">{{ __('Custom Form') }}</a>
                                </li>
                            @endif

                            {{-- Custom Form --}}
                            @if (has_permissions('read', 'approve_agent_verification'))
                                <li class="submenu-item">
                                    <a href="{{ route('agent-verification.index') }}">{{ __('Agent Verification List') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Property --}}
                @if (has_permissions('read', 'property'))
                    <li class="sidebar-item">
                        <a href="{{ url('property') }}" class='sidebar-link'>
                            <i class="bi bi-building"></i>
                            <span class="menu-item">{{ __('Property') }}</span>
                        </a>
                    </li>
                @endif

                {{-- City Images--}}
                @if (has_permissions('read', 'city_images'))
                    <li class="sidebar-item">
                        <a href="{{ route('city-images.index') }}" class='sidebar-link'>
                            <i class="bi bi-image-alt"></i>
                            <span class="menu-item">{{ __('City Images') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Project --}}
                @if (has_permissions('read', 'project'))
                    <li class="sidebar-item">
                        <a href="{{ url('project') }}" class='sidebar-link'>
                        <i class="bi bi-house"></i>
                            <span class="menu-item">{{ __('Project') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Report Reson --}}
                @if (has_permissions('read', 'report_reason'))
                    <li class="sidebar-item">
                        <a href="{{ url('report-reasons') }}" class='sidebar-link'>
                            <i class="bi bi-list-task"></i>
                            <span class="menu-item">{{ __('Report Reasons') }}</span>
                        </a>
                    </li>
                @endif

                {{-- User Reports --}}
                @if (has_permissions('read', 'user_reports'))
                    <li class="sidebar-item">
                        <a href="{{ url('users_reports') }}" class='sidebar-link'>
                            <i class="bi bi-exclamation-octagon-fill"></i>
                            <span class="menu-item">{{ __('Users Reports') }}</span>
                        </a>
                    </li>
                @endif

                {{-- User Inquiries --}}
                @if (has_permissions('read', 'users_inquiries'))
                    <li class="sidebar-item">
                        <a href="{{ url('users_inquiries') }}" class='sidebar-link'>
                            <i class="fas fa-question-circle"></i>
                            <span class="menu-item">{{ __('Users Inquiries') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Chat --}}
                @if (has_permissions('read', 'chat'))
                    <li class="sidebar-item">
                        <a href="{{ route('get-chat-list') }}" class='sidebar-link'>
                            <i class="bi bi-chat"></i>
                            <span class="menu-item">{{ __('Chat') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Slider --}}
                @if (has_permissions('read', 'slider'))
                    <li class="sidebar-item">
                        <a href="{{ url('slider') }}" class='sidebar-link'>
                            <i class="bi bi-sliders"></i>
                            <span class="menu-item">{{ __('Slider') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Article --}}
                @if (has_permissions('read', 'article'))
                    <li class="sidebar-item">
                        <a href="{{ url('article') }}" class='sidebar-link'>
                            <i class="bi bi-vector-pen"></i>
                            <span class="menu-item">{{ __('Article') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Advertisement --}}
                @if (has_permissions('read', 'advertisement'))
                    <li class="sidebar-item">
                        <a href="{{ url('featured_properties') }}" class='sidebar-link'>
                            <i class="bi bi-badge-ad"></i>
                            <span class="menu-item">{{ __('Advertisement') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Package --}}
                @if (has_permissions('read', 'package'))
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-credit-card-2-back"></i>
                            <span class="menu-item">{{ __('Feature Packages') }}</span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">
                            {{-- List Features --}}
                            @if (has_permissions('read', 'package-feature') || has_permissions('create', 'package-feature'))
                                <li class="submenu-item">
                                    <a href="{{ route('package-features.index') }}">{{ __('Features') }}</a>
                                </li>
                            @endif

                            {{-- List Packages --}}
                            @if (has_permissions('read', 'package') || has_permissions('create', 'package'))
                                <li class="submenu-item">
                                    <a href="{{ route('package.index') }}">{{ __('Packages') }}</a>
                                </li>
                            @endif

                            {{-- User Packages --}}
                            @if (has_permissions('read', 'user_package'))
                                <li class="submenu-item">
                                    <a href="{{ route('user-packages.index') }}">{{ __('Users Packages') }}</a>
                                </li>
                            @endif

                            {{-- Payment --}}
                            @if (has_permissions('read', 'payment'))
                                <li class="submenu-item">
                                    <a href="{{ route('payment.index') }}">{{ __('Payment') }}</a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif

                {{-- Calculator --}}
                @if (has_permissions('read', 'calculator'))
                    <li class="sidebar-item">
                        <a href="{{ url('calculator') }}" class='sidebar-link'>
                            <i class="bi bi-calculator"></i>
                            <span class="menu-item">{{ __('Calculator') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Notification --}}
                @if (has_permissions('read', 'notification'))
                    <li class="sidebar-item">
                        <a href="{{ url('notification') }}" class='sidebar-link'>
                            <i class="bi bi-bell"></i>
                            <span class="menu-item">{{ __('Notification') }}</span>
                        </a>
                    </li>
                @endif

                {{-- FAQs --}}
                @if (has_permissions('read', 'faqs'))
                    <li class="sidebar-item">
                        <a href="{{ route('faqs.index') }}" class='sidebar-link'>
                            <i class="bi bi-question-circle"></i>
                            <span class="menu-item">{{ __('FAQ') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Homepage Sections --}}
                @if (has_permissions('read', 'homepage-sections'))
                    <li class="sidebar-item">
                        <a href="{{ route('homepage-sections.index') }}" class='sidebar-link'>
                            <i class="bi bi-house-fill"></i>
                            <span class="menu-item">{{ __('Homepage Sections') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Settings --}}
                @if (has_permissions('read', 'users_accounts') ||
                        has_permissions('read', 'about_us') ||
                        has_permissions('read', 'privacy_policy') ||
                        has_permissions('read', 'terms_conditions') ||
                        has_permissions('read', 'web_settings') ||
                        has_permissions('read', 'language') ||
                        has_permissions('read', 'app_settings'))

                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-gear"></i>
                            <span class="menu-item">{{ __('Settings') }}</span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">

                            {{-- User Accounts --}}
                            @if (has_permissions('read', 'users_accounts'))
                                <li class="submenu-item">
                                    <a href="{{ url('users') }}">{{ __('Users Accounts') }}</a>
                                </li>
                            @endif

                            {{-- About Us --}}
                            @if (has_permissions('read', 'about_us'))
                                <li class="submenu-item">
                                    <a href="{{ url('about-us') }}">{{ __('About Us') }}</a>
                                </li>
                            @endif

                            {{-- Privacy Policy --}}
                            @if (has_permissions('read', 'privacy_policy'))
                                <li class="submenu-item">
                                    <a href="{{ url('privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                                </li>
                            @endif

                            {{-- Terms & Conditions --}}
                            @if (has_permissions('read', 'terms_conditions'))
                                <li class="submenu-item">
                                    <a href="{{ url('terms-conditions') }}">{{ __('Terms & Condition') }}</a>
                                </li>
                            @endif

                            {{-- Language  --}}
                            @if (has_permissions('read', 'language'))
                                <li class="submenu-item">
                                    <a href="{{ url('language') }}">{{ __('Languages') }}</a>
                                </li>
                            @endif

                            {{-- System Settings --}}
                            @if (has_permissions('read', 'system_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('system-settings') }}">{{ __('System Settings') }}</a>
                                </li>
                            @endif

                            {{-- App Settings --}}
                            @if (has_permissions('read', 'app_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('app-settings') }}">{{ __('App Settings') }}</a>
                                </li>
                            @endif

                            {{-- Web Settings --}}
                            @if (has_permissions('read', 'web_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('web-settings') }}">{{ __('Web Settings') }}</a>
                                </li>
                            @endif

                            {{-- Seo Settings --}}
                            @if (has_permissions('read', 'seo_setting'))
                                <li class="submenu-item">
                                    <a href="{{ url('seo_settings') }}">{{ __('SEO Settings') }}</a>
                                </li>
                            @endif

                            {{-- Firebase Settings --}}
                            @if (has_permissions('read', 'firebase_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('firebase_settings') }}">{{ __('Firebase Settings') }}</a>
                                </li>
                            @endif

                            {{-- Notification Settings --}}
                            @if (has_permissions('read', 'notification_settings'))
                                <li class="submenu-item">
                                    <a href="{{ route('notification-setting-index') }}">{{ __('Notification Settings') }}</a>
                                </li>
                            @endif

                            {{-- Email Configurations --}}
                            @if (has_permissions('read', 'email_configurations'))
                                <li class="submenu-item">
                                    <a href="{{ route('email-configurations-index') }}">{{ __('Email Configurations') }}</a>
                                </li>
                            @endif

                            {{-- Email Templates --}}
                            @if (has_permissions('read', 'email_templates'))
                                <li class="submenu-item">
                                    <a href="{{ route('email-templates.index') }}">{{ __('Email Templates') }}</a>
                                </li>
                            @endif

                            {{-- Log Viewer --}}
                            @if (has_permissions('read', 'system_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('log-viewer') }}">{{ __('Log Viewer') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    {{-- System Update --}}
                    @if (has_permissions('read', 'system_update'))
                        <li class="sidebar-item">
                            <a href="{{ url('system-version') }}" class='sidebar-link'>
                                <i class="fas fa-cloud-download-alt"></i>
                                <span class="menu-item">{{ __('System Update') }}</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</div>
