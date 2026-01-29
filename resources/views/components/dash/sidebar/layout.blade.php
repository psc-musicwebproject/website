<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand justify-content-start">
        <div class="mx-2 d-flex align-items-center">
            <img src="{{ asset('assets/image/logo.png') }}" alt="Logo" class="brand-image img-fluid shadow"
                style="max-height: 2.25rem">
            <span class="brand-text fw-bold">{{ $AppSetting::getSetting('header') ?? 'PSC Music' }}</span>
        </div>
    </div>
    <div class="sidebar-wrapper">
        <div class="user-panel mx-2 mb-2 d-flex">
            <div class="info w-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <a
                            class="d-block link-underline link-underline-opacity-0">{{ Auth::user()->name_title . Auth::user()->name . ' ' . Auth::user()->surname }}</a>
                    </div>
                    <div>
                        <button class="btn btn-link link-underline link-underline-opacity-0 dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="color: var(--lte-sidebar-color)"></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href=" {{ route('auth.user.setting') }}">ข้อมูลส่วนตัว</a></li>
                            <li>
                                <form method="POST" action=" {{ route('auth.web.logout') }} ">
                                    @csrf
                                    <button type="submit" class="dropdown-item">ออกจากระบบ</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <nav>
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Main Navigation" data-accordian="false" id="navigation">
                {{ $slot }}
            </ul>
        </nav>
    </div>
</aside>
