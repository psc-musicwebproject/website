<x-auth.layout>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"></span>
            </button>
        </div>
        <hr>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"></span>
            </button>
        </div>
        <hr>
    @endif
    <div class="text-center login-box-msg p-0">
        <p>ข้อมูลส่วนบุคคลของผู้ใช้</p>
        <p>หากต้องการแก้ไขข้อมูลของผู้ใช้ / เปลี่ยนแปลงรหัสผ่าน กรุณาติดต่อผู้ดูแลระบบ</p>
    </div>
    <hr>
    <div class="mb-3">
        <label for="name" class="form-label">ชื่อ - นามสกุล</label>
        <input type="text" class="form-control" id="name" name="name"
            value="{{ Auth::user()->name_title . Auth::user()->name . ' ' . Auth::user()->surname }}" readonly>
    </div>
    <div class="mb-3">
        <label for="nickname" class="form-label">ชื่อเล่น</label>
        <input type="text" class="form-control" id="nickname" name="nickname" value="{{ Auth::user()->nickname }}"
            readonly>
    </div>
    @if (Auth::user()->type == 'student')
        <div class="mb-3">
            <label for="major" class="form-label">สาขา</label>
            <input type="text" class="form-control" id="major" name="major" value="{{ Auth::user()->major }}"
                readonly>
        </div>
        <div class="mb-3">
            <label for="class" class="form-label">ระดับชั้น</label>
            <input type="text" class="form-control" id="class" name="class" value="{{ Auth::user()->class }}"
                readonly>
        </div>
    @endif
    <div class="mb-3">
        <label for="student_id" class="form-label">รหัสนักเรียน / นักศึกษา / พนักงาน</label>
        <input type="text" class="form-control" id="student_id" name="student_id"
            value="{{ Auth::user()->student_id }}" readonly>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">อีเมล</label>
        <input type="text" class="form-control" id="email" name="email"
            value="{{ Auth::user()->email ?? 'ไม่ระบุ' }}" readonly>
    </div>
    {{-- Make input to fetch their line username from LineServices using their id, and show status if user already bind it or not --}}
    <div class="mb-3">
        <div class="row align-items-center">
            <div class="col">
                <label for="line-link-status" class="form-label mb-0">สถานะการเชื่อมต่อ LINE</label>
                @if (Auth::user()->line_bound && Auth::user()->line_id)
                    <span class="badge bg-success">เชื่อมต่อแล้ว</span>
                @else
                    <span class="badge bg-danger">ยังไม่เชื่อมต่อ</span>
                @endif
            </div>
            @if (!Auth::user()->line_bound && !Auth::user()->line_id)
                <div class="col-auto">
                    <form action="{{ route('auth.bind.line') }}" method="post" class="mb-0">
                        @csrf
                        <input type="hidden" name="redirect_url" value="{{ route('auth.user.setting') }}">
                        <button type="submit" class="btn btn-primary">เชื่อมต่อ LINE</button>
                    </form>
                </div>
            @endif
        </div>
        @if (Auth::user()->line_bound && Auth::user()->line_id)
            <form action="" method="post">
                @csrf
                <div class="my-3 input-group">
                    <input type="text" class="form-control" id="line-username" name="line-username"
                        value="{{ \App\Services\LineService::getProfileName(Auth::user()->line_id) }}" readonly>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#unbindLineModal">ยกเลิกการเชื่อมต่อ</button>
                </div>
            </form>
        @endif
    </div>
    <hr>
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ Auth::user()->type == 'admin' ? route('admin.dash') : route('dash') }}"
            class="btn btn-primary">กลับสู่หน้าหลัก</a>
        <form action="{{ route('auth.web.logout') }}" method="post" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-danger">ออกจากระบบ</button>
        </form>
    </div>

    <div class="modal fade" id="unbindLineModal" tabindex="-1" aria-labelledby="unbindLineModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="unbindLineModalLabel">ยกเลิกการเชื่อมต่อ LINE</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการยกเลิกการเชื่อมต่อ LINE
                        ({{ \App\Services\LineService::getProfileName(Auth::user()->line_id) }}) หรือไม่</p>
                    <p>* การยกเลิกการเชื่อมต่อ <b class="text-danger">คุณจำเป็นต้องเข้าถึงบัญชี LINE
                            ของคุณได้เพื่อยกเลิกการเชื่อมต่อ
                            หากไม่สามารถเข้าถึงบัญชี LINE ของคุณได้ กรุณาติดต่อผู้ดูแลระบบ</b></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <form action="{{ route('auth.unbind.line') }}" method="post">
                        @csrf
                        <input type="hidden" name="redirect_url" value="{{ route('auth.user.setting') }}">
                        <button type="submit" class="btn btn-danger">ยืนยัน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-auth.layout>
