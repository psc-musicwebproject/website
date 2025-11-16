<x-auth.layout>
    <form id="" method="POST" action="{{ route('auth.web.login', ['guard' => request()->query('guard')]) }}">
        @csrf
        
        @if (request()->query('error') === 'access_denied')
            <div class="alert alert-danger" role="alert">
                <strong>Access Denied.</strong> Admin credentials required.
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="input-group mb-2">
            <div class="form-floating">
                <input name="student_id" type="numeric" class="form-control" value="{{ old('student_id') }}" placeholder="" data-bs-toggle="tooltip" data-bs-placement="right" title="กรุณากรอกรหัสประจำตัวเป็นตัวเลขเท่านั้น"/>
                <label for="student_id">รหัสประจำตัว</label>
            </div>
        </div>
        <div class="input-group mb-2">
            <div class="form-floating">
                <input name="password" type="password" class="form-control" value="" placeholder="" data-bs-toggle="tooltip" data-bs-placement="right" title="กรุณากรอกรหัสผ่าน"/>
                <label for="password">รหัสผ่าน</label>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" name="login_user" class="btn btn-primary">Sign In</button>
                </div>
            </div>
        </div>
    </form>
    <p class="mt-3 mb-0">
        <a href="{{ env('RESETPASSURI', "https://youtu.be/DAaHYO7PpzQ") }}">ลืมรหัสผ่าน / เพิ่งเข้าระบบครั้งแรกใช่ไหม?</a>
    </p>
</x-auth.layout>