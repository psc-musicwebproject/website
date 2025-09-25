<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand">
        <div class="mx-2 d-flex align-items-center">

        </div>
    </div>
    <div class="sidebar-wrapper">
        <div class="user-panel mx-2 my-2 pb-3 mb-3 d-flex">
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
                    <a href="/dash" class="nav-link active">
                        <i class="bi bi-house-door"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-header">ห้องดนตรี</li>
                <li class="nav-item">
                    <a href="/dash" class="nav-link">
                        <i class="bi bi-pencil-square"></i>
                        <p>จองเข้าใช้ห้อง</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dash" class="nav-link">
                        <i class="bi bi-journals"></i>
                        <p>ประวัติการจอง</p>
                    </a>
                </li>
                <li class="nav-header">ชมรมดนตรี</li>
                <li class="nav-item">
                    <a href="/dash" class="nav-link">
                        <i class="bi bi-file-ruled"></i>
                        <p>สมัครสมาชิก</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>