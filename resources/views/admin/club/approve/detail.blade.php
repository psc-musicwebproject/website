@extends('layouts.admin')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @foreach ($clubMember as $approval)
        @php
            $contactInfo = $approval->contact_info ?? [];
            $instruments = $approval->instrument ?? [];
            $experience = $approval->experience ?? [];
            $duties = $approval->wanted_duty ?? [];
            $user = $approval->user;

            $hasSocial = function ($type) use ($contactInfo) {
                return collect($contactInfo)->contains('type', $type);
            };
            $getSocial = function ($type) use ($contactInfo) {
                return collect($contactInfo)->firstWhere('type', $type)['data'] ?? '';
            };

            $hasInst = function ($type) use ($instruments) {
                return collect($instruments)->contains('type', $type);
            };
            $getInst = function ($type) use ($instruments) {
                return collect($instruments)->firstWhere('type', $type)['data'] ?? '';
            };

            $expType = collect($experience)->first()['type'] ?? '';
            $expData = collect($experience)->first()['data'] ?? '';

            $hasDuty = function ($type) use ($duties) {
                return collect($duties)->contains('type', $type);
            };
            $getDuty = function ($type) use ($duties) {
                return collect($duties)->firstWhere('type', $type)['data'] ?? '';
            };
        @endphp

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">รายละเอียดใบสมัคร: {{ $member->member_id ?? $approval->member_id }}</h3>
            </div>

            <div class="card-body">
                {{-- 1. Profile Info --}}
                <div class="mb-3 mt-2">
                    <h4 class="fw-bold border-bottom pb-2">ข้อมูลส่วนตัว</h4>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" readonly
                            value="{{ $user->name_title . $user->name }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" readonly value="{{ $user->surname }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">ชื่อเล่น</label>
                        <input type="text" class="form-control" readonly value="{{ $user->nickname }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">รหัสประจำตัว</label>
                        <input type="text" class="form-control" readonly value="{{ $user->student_id }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">ระดับชั้น</label>
                        <input type="text" class="form-control" readonly value="{{ $user->class }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">สาขาวิชาชีพ</label>
                        <input type="text" class="form-control" readonly value="{{ $user->major }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" readonly value="{{ $user->phone_number }}">
                    </div>
                </div>

                {{-- 2. Socials & Image --}}
                <div class="mb-3 mt-4">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <h4 class="fw-bold border-bottom pb-2">ช่องทางติดต่อโซเชียลมีเดีย</h4>
                            <div class="social-input-group mt-3">
                                @foreach (['facebook', 'line', 'instagram', 'discord', 'other'] as $social)
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" disabled
                                                    {{ $hasSocial($social) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    style="width: 80px; text-transform: capitalize;">
                                                    {{ $social }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" disabled
                                                value="{{ $getSocial($social) }}" placeholder="-">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <h4 class="fw-bold border-bottom pb-2 mb-3">รูปถ่ายประจำตัว</h4>
                            <div class="mb-3 text-center">
                                @if ($approval->member_id)
                                    <img src="{{ route('dash.club.photo', $approval->member_id) }}"
                                        class="img-fluid rounded shadow-sm"
                                        style="max-width: 250px; max-height: 300px; object-fit: cover;" alt="รูปถ่าย">
                                @else
                                    <div class="p-5 border bg-light text-muted rounded">ไม่มีรูปถ่าย</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Instruments --}}
                <div class="mb-3 mt-4">
                    <h4 class="fw-bold border-bottom pb-2">ความสนใจทางด้านดนตรี</h4>
                    <label class="form-label mb-3 mt-2">เครื่องดนตรีที่สามารถเล่นได้ / หรือมีความสนใจ</label>
                    <div class="row g-3 instrument-group">
                        @php
                            $instList = [
                                'guitar' => 'กีตาร์ (โปร่ง / ไฟฟ้า)',
                                'bass' => 'เบส',
                                'drums' => 'กลองชุด',
                                'keyboard' => 'คีย์บอร์ด / เปียโน',
                                'vocal' => 'ร้องเพลง',
                            ];
                        @endphp
                        @foreach ($instList as $key => $label)
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled
                                        {{ $hasInst($key) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" disabled
                                            {{ $hasInst('wind') ? 'checked' : '' }}>
                                        <label class="form-check-label">เครื่องเป่าลม</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" disabled value="{{ $getInst('wind') }}"
                                        placeholder="-">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" disabled
                                            {{ $hasInst('other') ? 'checked' : '' }}>
                                        <label class="form-check-label">อื่นๆ</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" disabled value="{{ $getInst('other') }}"
                                        placeholder="-">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Experience --}}
                <div class="mb-3 mt-4">
                    <h4 class="fw-bold border-bottom pb-2">ประสบการณ์การเล่นดนตรี</h4>
                    <div class="experience-group mt-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" disabled
                                {{ $expType == 'none' ? 'checked' : '' }}>
                            <label class="form-check-label">ไม่มีประสบการณ์</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" disabled
                                {{ $expType == 'hobby' ? 'checked' : '' }}>
                            <label class="form-check-label">เล่นเพื่อความบันเทิง</label>
                        </div>
                        <div class="row align-items-center g-2">
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" disabled
                                        {{ $expType == 'contest' ? 'checked' : '' }}>
                                    <label class="form-check-label">เล่นแสดง / ประกวด</label>
                                </div>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" disabled
                                    value="{{ $expType == 'contest' ? $expData : '' }}" placeholder="-">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Duties --}}
                <div class="mb-3 mt-4">
                    <h4 class="fw-bold border-bottom pb-2">บทบาทที่ต้องการในชมรม</h4>
                    <div class="row g-3 duty-group mt-2">
                        @php
                            $dutyList = [
                                'musician' => 'นักดนตรี',
                                'singer' => 'นักร้อง',
                                'sound_stage' => 'ดูแลเครื่องเสียง / จัดเวที',
                                'media' => 'ถ่ายภาพ / วิดีโอ / ประชาสัมพันธ์',
                            ];
                        @endphp
                        @foreach ($dutyList as $key => $label)
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled
                                        {{ $hasDuty($key) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" disabled
                                            {{ $hasDuty('manager') ? 'checked' : '' }}>
                                        <label class="form-check-label">ผู้ดูแลวงดนตรี</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" disabled
                                        value="{{ $getDuty('manager') }}" placeholder="-">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" disabled
                                            {{ $hasDuty('other') ? 'checked' : '' }}>
                                        <label class="form-check-label">อื่นๆ</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" disabled
                                        value="{{ $getDuty('other') }}" placeholder="-">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-muted">
                วันที่สมัคร: {{ $approval->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <form action="{{ route('admin.club.approve.update', $approval->member_id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header bg-light">
                    <span class="fw-bold">พิจารณาใบสมัคร</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="approve_reason" class="form-label">เหตุผล / ความคิดเห็น (ถ้ามี)</label>
                        <input type="text" class="form-control" placeholder="ระบุเหตุผลในการอนุมัติหรือไม่อนุมัติ"
                            name="approve_reason">
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="action" value="approve" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-check-fill me-1"></i>
                            <span>อนุมัติ</span>
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i>
                            <span>ไม่อนุมัติ</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endforeach
@endsection
