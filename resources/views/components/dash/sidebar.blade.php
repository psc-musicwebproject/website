<x-dash.sidebar.layout>
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
        <a href="{{ route('dash.booking.history') }}"
            class="nav-link {{ request()->routeIs('dash.booking.history') ? 'active' : '' }}">
            <i class="bi bi-journals"></i>
            <p>ประวัติการจอง</p>
        </a>
    </li>
    <li class="nav-header">ชมรมดนตรี</li>
    <li class="nav-item">
        <a href="{{ route('dash.club.register') }}"
            class="nav-link {{ request()->routeIs('dash.club.register') ? 'active' : '' }}">
            <i class="bi bi-file-ruled"></i>
            <p>สมัครสมาชิก</p>
        </a>
    </li>
    </ul>
    </nav>
</x-dash.sidebar.layout>
