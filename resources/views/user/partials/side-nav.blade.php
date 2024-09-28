<div class="sidebar">

    <div class="sidebar-inner">

        <div class="sidebar-area">

            <div class="sidebar-logo">

                <a href="{{ setRoute('index') }}" class="sidebar-main-logo">

                    <img src="{{ get_logo($basic_settings) }}" data-white_img="{{ get_logo($basic_settings) }}"

                    data-dark_img="{{ get_logo($basic_settings,"dark") }}" alt="logo">

                </a>

                <button class="sidebar-menu-bar">

                    <i class="fas fa-exchange-alt"></i>

                </button>

            </div>

            <div class="sidebar-menu-wrapper">

                <ul class="sidebar-menu">

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.dashboard') }}">

                            <i class="menu-icon las la-palette"></i>

                            <span class="menu-title">{{ __("Dashboard") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.buy.crypto.index') }}">

                            <i class="menu-icon las la-sign"></i>

                            <span class="menu-title">{{ __("Buy Crypto") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.sell.crypto.index') }}">

                            <i class="menu-icon las la-receipt"></i>

                            <span class="menu-title">{{ __("Sell Crypto") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.withdraw.crypto.index') }}">

                            <i class="menu-icon las la-fill-drip"></i>

                            <span class="menu-title">{{ __("Withdraw Crypto") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute("user.exchange.crypto.index") }}">

                            <i class="menu-icon lab la-stack-exchange"></i>

                            <span class="menu-title">{{ __("Exchange Crypto") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item sidebar-dropdown">

                        <a href="javascript:void(0)">

                            <i class="menu-icon las la-wallet"></i>

                            <span class="menu-title">{{ __("Transactions") }}</span>

                        </a>

                        <ul class="sidebar-submenu">

                            <li class="sidebar-menu-item">

                                <a href="{{ setRoute("user.transaction.buy.log") }}" class="nav-link">

                                    <i class="menu-icon las la-ellipsis-h"></i>

                                    <span class="menu-title">{{ __("Buy Log") }}</span>

                                </a>

                                <a href="{{ setRoute("user.transaction.sell.log") }}" class="nav-link">

                                    <i class="menu-icon las la-ellipsis-h"></i>

                                    <span class="menu-title">{{ __("Sell Log") }}</span>

                                </a>

                                <a href="{{ setRoute("user.transaction.withdraw.log") }}" class="nav-link">

                                    <i class="menu-icon las la-ellipsis-h"></i>

                                    <span class="menu-title">{{ __("Withdraw Log") }}</span>

                                </a>

                                <a href="{{ setRoute("user.transaction.exchange.log") }}" class="nav-link">

                                    <i class="menu-icon las la-ellipsis-h"></i>

                                    <span class="menu-title">{{ __("Exchange Log") }}</span>

                                </a>

                            </li>

                        </ul>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.authorize.kyc') }}">

                            <i class="menu-icon las la-user-alt"></i>

                            <span class="menu-title">{{__("KYC Verification")}}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="{{ setRoute('user.security.google.2fa') }}">

                            <i class="menu-icon las la-qrcode"></i>

                            <span class="menu-title">{{ __("2FA Security") }}</span>

                        </a>

                    </li>

                    <li class="sidebar-menu-item">

                        <a href="javascript:void(0)" class="logout-btn">

                            <i class="menu-icon las la-sign-out-alt"></i>

                            <span class="menu-title">{{ __("Logout") }}</span>

                        </a>

                    </li>

                </ul>

            </div>

        </div>

        <div class="sidebar-doc-box bg_img" data-background="{{ asset('public/frontend') }}/images/element/side-bg.png">

            <div class="sidebar-doc-icon">

                <i class="las la-headset"></i>

            </div>

            <div >

                <h5 class="title">{{ __("Help Center") }}</h5>

                <p>{{ __("How can we help you?") }}</p>

                <div class="sidebar-doc-btn">

                    <a href="{{ setRoute('user.support.ticket.index') }}" class="btn--base w-100"><span class="w-100 text-center">{{ __("Get Support") }}</span></a>

                </div>

            </div>

        </div>

    </div>

</div>

@push('script')



@endpush