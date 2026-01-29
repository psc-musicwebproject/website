<x-auth.layout>
    <form id="" method="POST" action="{{ route('auth.bind.line', ['guard' => request()->query('guard')]) }}">
        @csrf
        <input type="hidden" name="redirect_url" value="{{ $skipUrl }}">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
        @endif
        <div>
            <span>เพื่อการใช้งานที่สะดวกยิ่งขึ้น
                {{ $AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project') }}
                ขอแนะนำให้ท่านเข้าสู่ระบบผ่านแอปพลิเคชันไลน์ เพื่อรับการแจ้งเตือนต่างๆ ได้อย่างรวดเร็ว</span>
        </div>
        <div class="form-group d-flex justify-content-center">
            <button type="submit" class="btn btn-primary btn-block mt-3">ผูกบัญชีไลน์</button>
        </div>
    </form>
    <div class="d-flex justify-content-center align-items-center text-center mt-3">
        <a href="{{ $skipUrl }}" class="btn btn-link p-0 text-secondary"
            style="text-decoration: none;">ข้ามขั้นตอนนี้</a>
        <span class="mx-2">|</span>
        <form method="POST" action="{{ route('auth.web.logout') }}" class="d-inline m-0">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-danger"
                style="text-decoration: none;">ออกจากระบบ</button>
        </form>
    </div>
</x-auth.layout>
