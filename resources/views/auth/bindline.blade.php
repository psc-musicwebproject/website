<x-auth.layout>
    <form id="" method="POST" action="{{ route('auth.bind.line', ['guard' => request()->query('guard')]) }}">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div>
            <span>เพื่อการใช้งานที่สะดวกยิ่งขึ้น {{ $AppSetting::getSetting('name') ?? config('app.name', "PSC-MusicWeb Project") }} ขอแนะนำให้ท่านเข้าสู่ระบบผ่านแอปพลิเคชันไลน์ เพื่อรับการแจ้งเตือนต่างๆ ได้อย่างรวดเร็ว</span>
        </div>
        <div class="form-group d-flex justify-content-center">
        <button type="submit" class="btn btn-primary btn-block mt-3">ผูกบัญชีไลน์</button>
        </div>
    </form>
</x-auth.layout>