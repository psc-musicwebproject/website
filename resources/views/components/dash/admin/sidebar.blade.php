<x-dash.sidebar.layout>
    <li class="nav-item">
        <a href="{{ route('admin.dash') }}" class="nav-link {{ request()->routeIs('admin.dash') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-item">
        <a href=" {{ route('admin.appsetting') }}"
            class="nav-link {{ request()->routeIs('admin.appsetting') ? 'active' : '' }}">
            <i class="bi bi bi-gear-fill"></i>
            <p>ตั้งค่าระบบ</p>
        </a>
    </li>
    <li class="nav-item">
        <a href=" {{ route('admin.usersetting') }}"
            class="nav-link {{ request()->routeIs('admin.usersetting') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i>
            <p>ตั้งค่าผู้ใช้</p>
        </a>
    </li>
    <li class="nav-header">ห้องดนตรี</li>
    <li class="nav-item">
        <a href="{{ route('admin.roomsetting') }}"
            class="nav-link {{ request()->routeIs('admin.roomsetting') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i>
            <p>ตั้งค่าห้อง</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.booking') }}"
            class="nav-link {{ request()->routeIs('admin.booking*') ? 'active' : '' }}">
            <i class="bi bi-journals"></i>
            <p>อนุมัติการใช้ห้อง</p>
        </a>
    </li>
    <li class="nav-header">ชมรมดนตรี</li>
    <li class="nav-item">
        <a href="{{ route('admin.club.approve') }}"
            class="nav-link {{ request()->routeIs('admin.club.approve*') ? 'active' : '' }}">
            <i class="bi bi-file-ruled"></i>
            <p>อนุมัติการสมัคร</p>
        </a>
    </li>
</x-dash.sidebar.layout>
