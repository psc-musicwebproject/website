<x-dash.layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">แบบฟอร์มสมัครเข้าชมรมดนตรี</h3>
        </div>
        
        @if($clubMembership && $clubMembership->status === 'approved')
            <div class="card-body">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> คุณเป็นสมาชิกชมรมดนตรีแล้ว</h4>
                    <p>คุณได้รับการอนุมัติให้เป็นสมาชิกชมรมดนตรีเมื่อ: {{ $clubMembership->approval_time?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}</p>
                    @if($clubMembership->approvalPerson)
                        <p>ผู้อนุมัติ: {{ $clubMembership->approvalPerson->name }} {{ $clubMembership->approvalPerson->surname }}</p>
                    @endif
                    @if($clubMembership->approval_comment)
                        <p>ความเห็น: {{ $clubMembership->approval_comment }}</p>
                    @endif
                </div>
                <div class="row">
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="name" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="name" disabled value="{{ Auth::user()->name }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="surname" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="surname" disabled value="{{ Auth::user()->surname }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="student_id" class="form-label">รหัสประจำตัว</label>
                        <input type="text" class="form-control" id="student_id" disabled value="{{ Auth::user()->student_id }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="class" class="form-label">ระดับชั้น</label>
                        <input type="text" class="form-control" id="class" disabled value="{{ Auth::user()->class }}">
                    </div>
                </div>
            </div>
        @elseif($clubMembership && $clubMembership->status === 'waiting')
            <div class="card-body">
                <div class="alert alert-info">
                    <h4><i class="fas fa-clock"></i> ใบสมัครของคุณกำลังรอการอนุมัติ</h4>
                    <p>วันที่ส่งใบสมัคร: {{ $clubMembership->created_at->format('d/m/Y H:i') }}</p>
                    <p>สถานะ: รอการอนุมัติจากผู้ดูแลชมรม</p>
                </div>
                <div class="row">
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="name" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="name" disabled value="{{ Auth::user()->name }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="surname" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="surname" disabled value="{{ Auth::user()->surname }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="student_id" class="form-label">รหัสประจำตัว</label>
                        <input type="text" class="form-control" id="student_id" disabled value="{{ Auth::user()->student_id }}">
                    </div>
                    <div class="mb-3 col-12 col-lg-3">
                        <label for="class" class="form-label">ระดับชั้น</label>
                        <input type="text" class="form-control" id="class" disabled value="{{ Auth::user()->class }}">
                    </div>
                </div>
            </div>
        @elseif($clubMembership && $clubMembership->status === 'rejected')
            <div class="card-body">
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> ใบสมัครของคุณไม่ได้รับการอนุมัติ</h4>
                    <p>วันที่พิจารณา: {{ $clubMembership->approval_time?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}</p>
                    @if($clubMembership->approvalPerson)
                        <p>ผู้พิจารณา: {{ $clubMembership->approvalPerson->name }} {{ $clubMembership->approvalPerson->surname }}</p>
                    @endif
                    @if($clubMembership->approval_comment)
                        <p>เหตุผล: {{ $clubMembership->approval_comment }}</p>
                    @endif
                    <p class="mb-0">คุณสามารถสมัครใหม่ได้อีกครั้ง</p>
                </div>
        @endif
        
        @if(!$clubMembership || $clubMembership->status === 'rejected')
        <form action="{{ route('dash.club.register.submit') }}" method="POST">
            @csrf
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                    <div class="row">
                        <div class="mb-3 col-12 col-lg-3">
                            <label for="name" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" id="name" disabled value="{{ Auth::user()->name }}">
                        </div>
                        <div class="mb-3 col-12 col-lg-3">
                            <label for="surname" class="form-label">นามสกุล</label>
                            <input type="text" class="form-control" id="surname" disabled value="{{ Auth::user()->surname }}">
                        </div>
                        <div class="mb-3 col-12 col-lg-3">
                            <label for="student_id" class="form-label">รหัสประจำตัว</label>
                            <input type="text" class="form-control" id="student_id" disabled value="{{ Auth::user()->student_id }}">
                        </div>
                        <div class="mb-3 col-12 col-lg-3">
                            <label for="class" class="form-label">ระดับชั้น</label>
                            <input type="text" class="form-control" id="class" disabled value="{{ Auth::user()->class }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ability" class="form-label">ความสามารถที่มี <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ability') is-invalid @enderror" id="ability" name="ability" value="{{ old('ability') }}" required>
                        @error('ability')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
            </div>
            <div class="card-footer">
                <div class="d-flex align-items-start mb-3">
                    <input id="agree-rules" type="checkbox" class="me-2 mt-1" required>
                    <label for="agree-rules" class="form-check-label">นักเรียน / นักศึกษามีเวลาฝึกซ้อมในช่วงหลักเลิกเรียนได้ รวมถึงสามารถเดินทางมายังวิทยาลัยในวันหยุดที่จำเป็นต้องเข้ามาได้</label>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" name="send-application" class="btn btn-primary">ส่ง</button>
                </div>
            </div>
        </form>
        @endif
    </div>
</x-dash.layout>
