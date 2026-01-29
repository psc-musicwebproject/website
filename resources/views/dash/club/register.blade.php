<x-dash.layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">แบบฟอร์มสมัครเข้าชมรมดนตรี</h3>
        </div>

        @if ($clubMembership && $clubMembership->status === 'approved')
            <div class="card-body">
                <div class="alert alert-success">
                    <h4><i class="bi bi-check-circle-fill"></i> คุณเป็นสมาชิกชมรมดนตรีแล้ว</h4>
                    <p>คุณได้รับการอนุมัติให้เป็นสมาชิกชมรมดนตรีเมื่อ:
                        {{ $clubMembership->approval_time?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}
                    </p>
                    <p>ผู้อนุมัติ: {{ $clubMembership->approvalPerson->name }}
                        {{ $clubMembership->approvalPerson->surname }}
                    </p>
                    @if ($clubMembership->approval_comment)
                        <p>ความเห็น: {{ $clubMembership->approval_comment }}</p>
                    @endif
                </div>
            </div>
        @elseif($clubMembership && $clubMembership->status === 'waiting')
            <div class="card-body">
                <div class="alert alert-info">
                    <h4><i class="bi bi-clock-fill"></i> ใบสมัครของคุณกำลังรอการอนุมัติ</h4>
                    <p>วันที่ส่งใบสมัคร: {{ $clubMembership->created_at->format('d/m/Y H:i') }}</p>
                    <p>สถานะ: รอการอนุมัติจากผู้ดูแลชมรม</p>
                </div>
            </div>
        @else
            {{-- Form for New Application or Rejected --}}
            <form action="{{ route('dash.club.register.submit') }}" method="POST" id="club-register-form"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="contact_info" id="contact_info_input">
                <input type="hidden" name="instrument" id="instrument_input">
                <input type="hidden" name="experience" id="experience_input">
                <input type="hidden" name="wanted_duty" id="wanted_duty_input">

                <div class="card-body">
                    @if ($clubMembership && $clubMembership->status === 'rejected')
                        <div class="alert alert-warning">
                            <h4><i class="bi bi-exclamation-triangle-fill"></i> ใบสมัครของคุณไม่ได้รับการอนุมัติ</h4>
                            <p>วันที่พิจารณา: {{ $clubMembership->approval_time?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}
                            </p>
                            <p>ผู้พิจารณา: {{ $clubMembership->approvalPerson->name }}
                                {{ $clubMembership->approvalPerson->surname }}
                            </p>
                            @if ($clubMembership->approval_comment)
                                <p>เหตุผล: {{ $clubMembership->approval_comment }}</p>
                            @endif
                            <p class="mb-0">คุณสามารถสมัครใหม่ได้อีกครั้ง</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mb-3 mt-2">
                        <h4 class="fw-bold border-bottom pb-2">ข้อมูลส่วนตัว</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" readonly
                                value="{{ Auth::user()->name_title . Auth::user()->name }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">นามสกุล</label>
                            <input type="text" class="form-control" readonly value="{{ Auth::user()->surname }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">ชื่อเล่น</label>
                            <input type="text" class="form-control" readonly value="{{ Auth::user()->nickname }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">รหัสประจำตัว</label>
                            <input type="text" class="form-control" readonly value="{{ Auth::user()->student_id }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">ระดับชั้น</label>
                            <input type="text" class="form-control" readonly value="{{ Auth::user()->class }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">สาขาวิชาชีพ</label>
                            <input type="text" class="form-control" readonly value="{{ Auth::user()->major }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" readonly
                                value="{{ Auth::user()->phone_number }}">
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h4 class="fw-bold border-bottom pb-2">ช่องทางติดต่อโซเชียลมีเดีย <span
                                        class="text-danger">*</span></h4>
                                <div id="error-socials" class="text-danger mt-1 d-none"><small><i
                                            class="bi bi-exclamation-circle-fill"></i> กรุณาระบุอย่างน้อย 1
                                        ช่องทาง</small></div>
                                <div class="social-input-group">
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input social-check" type="checkbox"
                                                    data-type="facebook" id="check_facebook">
                                                <label class="form-check-label" for="check_facebook"
                                                    style="width: 80px;">Facebook</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control social-text" data-type="facebook"
                                                id="text_social_facebook" placeholder="ชื่อ Facebook / Link Profile"
                                                disabled>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input social-check" type="checkbox"
                                                    data-type="line" id="check_line">
                                                <label class="form-check-label" for="check_line"
                                                    style="width: 80px;">Line</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control social-text" data-type="line"
                                                id="text_social_line" placeholder="Line ID" disabled>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input social-check" type="checkbox"
                                                    data-type="instagram" id="check_ig">
                                                <label class="form-check-label" for="check_ig"
                                                    style="width: 80px;">Instagram</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control social-text"
                                                data-type="instagram" id="text_social_instagram"
                                                placeholder="Instagram Account" disabled>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input social-check" type="checkbox"
                                                    data-type="discord" id="check_discord">
                                                <label class="form-check-label" for="check_discord"
                                                    style="width: 80px;">Discord</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control social-text"
                                                data-type="discord" id="text_social_discord"
                                                placeholder="Discord Username" disabled>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input social-check" type="checkbox"
                                                    data-type="other" id="check_social_other">
                                                <label class="form-check-label" for="check_social_other"
                                                    style="width: 80px;">อื่นๆ</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control social-text" data-type="other"
                                                id="text_social_other" placeholder="ระบุช่องทางติดต่อเพิ่มเติม"
                                                disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4 class="fw-bold border-bottom pb-2 mb-3">รูปถ่ายประจำตัว <span
                                        class="text-danger">*</span></h4>
                                <div id="error-image" class="text-danger mt-1 mb-2 d-none"><small><i
                                            class="bi bi-exclamation-circle-fill"></i> กรุณาอัปโหลดรูปถ่าย</small>
                                </div>
                                <div class="mb-3 text-center">
                                    <img id="preview-image" src="{{ asset('assets/image/astonholder.png') }}"
                                        class="img-fluid rounded shadow-sm"
                                        style="width: 180px; height: 240px; object-fit: cover; cursor: pointer;"
                                        onclick="document.getElementById('input_image').click();" alt="รูปถ่าย">
                                </div>
                                <div class="mb-3 text-center">
                                    <label for="input_image" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-camera-fill me-1"></i> เลือกรูปภาพ
                                    </label>
                                    <input type="file" class="d-none" id="input_image" name="image_file"
                                        accept="image/*">
                                    <input type="hidden" name="image_base64" id="image_base64">
                                    <div class="mt-2">
                                        <small class="text-muted d-block">คลิกที่รูปหรือปุ่มเพื่ออัปโหลด</small>
                                        <small class="text-muted d-block">ขนาดไฟล์ไม่เกิน 2MB</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Cropper Modal -->
                            <div class="modal fade" id="modal-crop" tabindex="-1" aria-labelledby="modalCropLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalCropLabel">ปรับแต่งรูปภาพ</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="img-container" style="max-height: 500px;">
                                                <img id="image-to-crop" src="" alt="Picture"
                                                    style="max-width: 100%;">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">ยกเลิก</button>
                                            <button type="button" class="btn btn-primary" id="btn-crop">
                                                <i class="bi bi-crop me-1"></i> ตัดรูปภาพ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <h4 class="fw-bold border-bottom pb-2">ความสนใจทางด้านดนตรี <span class="text-danger">*</span>
                        </h4>
                        <div id="error-instruments" class="text-danger mt-1 mb-2 d-none"><small><i
                                    class="bi bi-exclamation-circle-fill"></i> กรุณาระบุเครื่องดนตรี</small></div>
                        <label class="form-label mb-3">เครื่องดนตรีที่สามารถเล่นได้ / หรือมีความสนใจ</label>
                        <div class="row g-3 instrument-group">
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input instrument-check" type="checkbox"
                                        data-type="guitar" id="inst_guitar">
                                    <label class="form-check-label" for="inst_guitar">กีตาร์ (โปร่ง / ไฟฟ้า)</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input instrument-check" type="checkbox" data-type="bass"
                                        id="inst_bass">
                                    <label class="form-check-label" for="inst_bass">เบส</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input instrument-check" type="checkbox"
                                        data-type="drums" id="inst_drums">
                                    <label class="form-check-label" for="inst_drums">กลองชุด</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input instrument-check" type="checkbox"
                                        data-type="keyboard" id="inst_keyboard">
                                    <label class="form-check-label" for="inst_keyboard">คีย์บอร์ด / เปียโน</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input instrument-check" type="checkbox"
                                        data-type="vocal" id="inst_vocal">
                                    <label class="form-check-label" for="inst_vocal">ร้องเพลง</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input instrument-check" type="checkbox"
                                                data-type="wind" id="inst_wind">
                                            <label class="form-check-label" for="inst_wind">เครื่องเป่าลม</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control instrument-text" data-type="wind"
                                            id="text_instrument_wind" placeholder="ระบุชนิดเครื่องเป่า" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input instrument-check" type="checkbox"
                                                data-type="other" id="inst_other">
                                            <label class="form-check-label" for="inst_other">อื่นๆ</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control instrument-text" data-type="other"
                                            id="text_instrument_other" placeholder="ระบุเครื่องดนตรีอื่น ๆ" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <h4 class="fw-bold border-bottom pb-2">ประสบการณ์การเล่นดนตรี <span
                                class="text-danger">*</span></h4>
                        <div id="error-experience" class="text-danger mt-1 mb-2 d-none"><small><i
                                    class="bi bi-exclamation-circle-fill"></i> กรุณาระบุประสบการณ์</small></div>
                        <div class="experience-group">
                            <div class="form-check mb-2">
                                <input class="form-check-input exp-check" type="radio" name="exp_option"
                                    data-type="no_experience" id="exp_none" value="none">
                                <label class="form-check-label" for="exp_none">ไม่มีประสบการณ์</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input exp-check" type="radio" name="exp_option"
                                    data-type="hobby" id="exp_hobby" value="hobby">
                                <label class="form-check-label" for="exp_hobby">เล่นเพื่อความบันเทิง</label>
                            </div>
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input exp-check" type="radio" name="exp_option"
                                            data-type="contest" id="exp_contest" value="contest">
                                        <label class="form-check-label" for="exp_contest">เล่นแสดง / ประกวด</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control exp-text" data-type="contest"
                                        id="text_exp_contest" placeholder="ระบุรายการประกวด หรือประสบการณ์ที่สำคัญ"
                                        disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <h4 class="fw-bold border-bottom pb-2">บทบาทที่ต้องการในชมรม <span
                                class="text-danger">*</span></h4>
                        <div id="error-duties" class="text-danger mt-1 mb-2 d-none"><small><i
                                    class="bi bi-exclamation-circle-fill"></i> กรุณาระบุบทบาท</small></div>
                        <div class="row g-3 duty-group">
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input duty-check" type="checkbox" data-type="musician"
                                        id="duty_musician">
                                    <label class="form-check-label" for="duty_musician">นักดนตรี</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input duty-check" type="checkbox" data-type="singer"
                                        id="duty_singer">
                                    <label class="form-check-label" for="duty_singer">นักร้อง</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input duty-check" type="checkbox"
                                        data-type="sound_stage" id="duty_sound">
                                    <label class="form-check-label" for="duty_sound">ดูแลเครื่องเสียง /
                                        จัดเวที</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input duty-check" type="checkbox" data-type="media"
                                        id="duty_media">
                                    <label class="form-check-label" for="duty_media">ถ่ายภาพ / วิดีโอ /
                                        ประชาสัมพันธ์</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input duty-check" type="checkbox"
                                                data-type="manager" id="duty_manager">
                                            <label class="form-check-label" for="duty_manager">ผู้ดูแลวงดนตรี</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control duty-text" data-type="manager"
                                            id="text_duty_manager" placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input duty-check" type="checkbox"
                                                data-type="other" id="duty_other">
                                            <label class="form-check-label" for="duty_other">อื่นๆ</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control duty-text" data-type="other"
                                            id="text_duty_other" placeholder="ระบุบทบาทที่ต้องการ" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer pt-4">
                    <div class="form-check mb-3">
                        <input id="agree-rules" type="checkbox" class="form-check-input" required>
                        <label for="agree-rules" class="form-check-label">
                            ข้าพเจ้ายินดีปฏิบัติตามกฎระเบียบของชมรม
                            และสามารถเข้าร่วมกิจกรรมหรือการฝึกซ้อมได้ตามตารางที่ชมรมกำหนด
                            รวมถึงในกรณีที่มีความจำเป็นเร่งด่วน
                        </label>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i
                                class="bi bi-send-fill me-2"></i>ส่งใบสมัคร</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Helper to toggle inputs based on checkbox/radio
                function setupToggle(checkClass, textClass, prefix) {
                    const checks = document.querySelectorAll(checkClass);
                    checks.forEach(check => {
                        check.addEventListener('change', function() {
                            const type = this.getAttribute('data-type');
                            if (this.type === 'checkbox') {
                                const textInput = document.getElementById(prefix + type);
                                if (textInput) {
                                    textInput.disabled = !this.checked;
                                    if (!this.checked) textInput.value = '';
                                }
                            }
                        });
                    });
                }

                // Experience radio logic specific
                const expRadios = document.querySelectorAll('.exp-check');
                expRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Disable all text inputs in exp group first
                        document.querySelectorAll('.exp-text').forEach(t => {
                            t.disabled = true;
                            t.value = '';
                        });

                        // Enable relevant one if exists
                        const type = this.getAttribute('data-type');
                        const textInput = document.getElementById('text_exp_' + type);
                        if (textInput) {
                            textInput.disabled = false;
                        }
                    });
                });

                setupToggle('.social-check', '.social-text', 'text_social_');
                setupToggle('.instrument-check', '.instrument-text', 'text_instrument_');
                setupToggle('.duty-check', '.duty-text', 'text_duty_');


                function resetErrors() {
                    document.querySelectorAll('.text-danger.d-none').forEach(el => el.classList.add('d-none'));
                    document.querySelectorAll('.text-danger:not(.d-none)').forEach(el => {
                        if (el.id.startsWith('error-')) el.classList.add('d-none');
                    });
                }

                function showError(id, message = null) {
                    const el = document.getElementById(id);
                    if (el) {
                        if (message) {
                            el.innerHTML = `<small><i class="bi bi-exclamation-circle-fill"></i> ${message}</small>`;
                        }
                        el.classList.remove('d-none');
                    }
                }

                const form = document.getElementById('club-register-form');
                form.addEventListener('submit', function(e) {
                    resetErrors();
                    let hasError = false;

                    // 1. Collect Socials & Validate
                    const socials = [];
                    let socialMissingText = false;
                    document.querySelectorAll('.social-check:checked').forEach(chk => {
                        const type = chk.getAttribute('data-type');
                        const textInput = document.getElementById('text_social_' + type);
                        const data = textInput ? textInput.value.trim() : '';

                        if (textInput && data === '') {
                            socialMissingText = true;
                        }

                        if (data !== '') {
                            socials.push({
                                type: type,
                                data: data
                            });
                        }
                    });
                    document.getElementById('contact_info_input').value = JSON.stringify(socials);

                    // 2. Collect Instruments & Validate
                    const instruments = [];
                    let instrumentMissingText = false;
                    document.querySelectorAll('.instrument-check:checked').forEach(chk => {
                        const type = chk.getAttribute('data-type');
                        const textInput = document.getElementById('text_instrument_' + type);
                        const data = textInput ? textInput.value.trim() : 'checked';

                        // If text input exists, it must be filled
                        if (textInput && data === '') {
                            instrumentMissingText = true;
                        }

                        instruments.push({
                            type: type,
                            data: data
                        });
                    });
                    document.getElementById('instrument_input').value = JSON.stringify(instruments);

                    // 3. Collect Experience & Validate
                    const experience = [];
                    let expMissingText = false;
                    const checkedExp = document.querySelector('.exp-check:checked');
                    if (checkedExp) {
                        const type = checkedExp.getAttribute('data-type');
                        const textInput = document.getElementById('text_exp_' + type);
                        const data = textInput ? textInput.value.trim() : 'checked';

                        if (textInput && data === '') {
                            expMissingText = true;
                        }

                        experience.push({
                            type: type,
                            data: data
                        });
                    }
                    document.getElementById('experience_input').value = JSON.stringify(experience);

                    // 4. Collect Duty & Validate
                    const duties = [];
                    let dutyMissingText = false;
                    document.querySelectorAll('.duty-check:checked').forEach(chk => {
                        const type = chk.getAttribute('data-type');
                        const textInput = document.getElementById('text_duty_' + type);
                        const data = textInput ? textInput.value.trim() : 'checked';

                        if (textInput && data === '') {
                            dutyMissingText = true;
                        }

                        duties.push({
                            type: type,
                            data: data
                        });
                    });
                    document.getElementById('wanted_duty_input').value = JSON.stringify(duties);

                    // Validation
                    if (socials.length === 0 && !socialMissingText) {
                        showError('error-socials');
                        hasError = true;
                    } else if (socialMissingText) {
                        showError('error-socials', 'กรุณาระบุรายละเอียดให้ครบถ้วน');
                        hasError = true;
                    }

                    if (instruments.length === 0) {
                        showError('error-instruments');
                        hasError = true;
                    } else if (instrumentMissingText) {
                        showError('error-instruments', 'กรุณาระบุรายละเอียดให้ครบถ้วน');
                        hasError = true;
                    }

                    if (!checkedExp) {
                        showError('error-experience');
                        hasError = true;
                    } else if (expMissingText) {
                        showError('error-experience', 'กรุณาระบุรายละเอียดให้ครบถ้วน');
                        hasError = true;
                    }

                    if (duties.length === 0) {
                        showError('error-duties');
                        hasError = true;
                    } else if (dutyMissingText) {
                        showError('error-duties', 'กรุณาระบุรายละเอียดให้ครบถ้วน');
                        hasError = true;
                    }

                    if (!document.getElementById('image_base64').value && !document.getElementById(
                            'input_image').value) {
                        showError('error-image');
                        hasError = true;
                    }

                    if (hasError) {
                        e.preventDefault();
                        // Scroll to first error
                        const firstError = document.querySelector('.text-danger:not(.d-none)[id^="error-"]');
                        if (firstError) {
                            firstError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                });

                // Image Cropping Logic
                let cropper;
                const inputImage = document.getElementById('input_image');
                const modalCrop = document.getElementById('modal-crop');
                const imageToCrop = document.getElementById('image-to-crop');
                const previewImage = document.getElementById('preview-image');
                const imageBase64Input = document.getElementById('image_base64');
                const btnCrop = document.getElementById('btn-crop');
                let bsModalCrop;

                if (inputImage) {
                    inputImage.addEventListener('change', function(e) {
                        resetErrors();
                        const files = e.target.files;
                        if (files && files.length > 0) {
                            const file = files[0];
                            if (/^image\/\w+/.test(file.type)) {
                                if (file.size > 2 * 1024 * 1024) { // 2MB
                                    showError('error-image', 'ขนาดไฟล์ต้องไม่เกิน 2MB');
                                    this.value = '';
                                    return;
                                }

                                const reader = new FileReader();
                                reader.onload = function() {
                                    imageToCrop.src = reader.result;
                                    if (!bsModalCrop) {
                                        bsModalCrop = new bootstrap.Modal(modalCrop, {
                                            backdrop: 'static'
                                        });
                                    }
                                    bsModalCrop.show();
                                };
                                reader.readAsDataURL(file);
                            } else {
                                showError('error-image', 'กรุณาเลือกไฟล์รูปภาพเท่านั้น');
                                this.value = '';
                            }
                        }
                    });

                    modalCrop.addEventListener('shown.bs.modal', function() {
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 3 / 4,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            responsive: true,
                        });
                    });

                    modalCrop.addEventListener('hidden.bs.modal', function() {
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        inputImage.value = ''; // Reset input so same file can be selected again
                    });

                    btnCrop.addEventListener('click', function() {
                        if (cropper) {
                            const canvas = cropper.getCroppedCanvas({
                                width: 600,
                                height: 800,
                            });

                            const base64Url = canvas.toDataURL('image/jpeg');
                            previewImage.src = base64Url;
                            imageBase64Input.value = base64Url;
                            bsModalCrop.hide();
                        }
                    });
                }
            });
        </script>
    @endpush
</x-dash.layout>
