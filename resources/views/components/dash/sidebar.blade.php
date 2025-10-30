<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand justify-content-start">
        <div class="mx-2 d-flex align-items-center">
            <img src="{{ asset("assets/image/logo.png") }}" alt="Logo" class="brand-image img-fluid shadow" style="max-height: 2.25rem">
            <span class="brand-text fw-bold">PSC Music</span>
        </div>
    </div>
    <div class="sidebar-wrapper">
        <div class="user-panel mx-2 mb-2 d-flex">
            <div class="info w-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <a class="d-block link-underline link-underline-opacity-0">{{ Auth::user()->name }}
                            {{ Auth::user()->surname }}</a>
                    </div>
                    <div>
                        <form method="POST" action=" {{ route('auth.web.logout') }} ">
                            @csrf
                            <button type="submit" class="btn btn-link" style="color: var(--lte-sidebar-color)"><i class="bi bi-box-arrow-left"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav>
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Main Navigation" data-accordian="false" id="navigation">
                <li class="nav-item">
                    <a href=" {{ route('dash') }} " class="nav-link {{ request()->routeIs('dash') ? 'active' : '' }}">
                        <i class="bi bi-house-door"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-header">ห้องดนตรี</li>
                <li class="nav-item">
                    <a href="{{ route('dash.booking') }}" class="nav-link {{ request()->routeIs('dash.booking') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square"></i>
                        <p>จองเข้าใช้ห้อง</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dash.booking.history')}}" class="nav-link {{ request()->routeIs('dash.booking.history') ? 'active' : '' }}">
                        <i class="bi bi-journals"></i>
                        <p>ประวัติการจอง</p>
                    </a>
                </li>
                <li class="nav-header">ชมรมดนตรี</li>
                <li class="nav-item">
                    <a href="{{ route('dash.club.register') }}" class="nav-link {{ request()->routeIs('dash.club.register') ? 'active' : '' }}">
                        <i class="bi bi-file-ruled"></i>
                        <p>สมัครสมาชิก</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>