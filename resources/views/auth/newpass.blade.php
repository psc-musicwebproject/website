<x-auth.layout>
    <form method="POST" action="{{ route('auth.web.newpass') }}">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <span>เพื่อความปลอดภัยของบัญชีของท่าน กรุณาตั้งรหัสผ่านใหม่</span>
        </div>
        

        <div class="form-group mb-3">
            <label for="password">รหัสผ่านใหม่</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="form-group mb-3">
            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">ยืนยัน</button>
    </form>
</x-auth.layout>