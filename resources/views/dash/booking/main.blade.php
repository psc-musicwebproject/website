<x-dash.layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">แบบฟอร์มจองห้อง</h3>
        </div>
        <div class="card-body">
            @if(count($rooms) == 0)
                <div class="alert alert-danger">
                    <h4><i class="bi bi-x-circle"></i> ไม่มีห้องให้จอง</h4>
                    <p class="mb-0">ขออภัยในความไม่สะดวก ขณะนี้ยังไม่มีห้องที่สามารถจองได้ กรุณาติดต่อผู้ดูแลระบบสำหรับข้อมูลเพิ่มเติม</p>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <form method="POST" action="{{ route('dash.booking.submit') }}">
                @csrf
            <div class="row">
                        <div class="col">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">ห้องที่ต้องการจอง</label>
                                    <select class="form-select" id="room_id" name="room_id" required @if(count($rooms) == 0) disabled @endif>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->room_id }}">{{ $room->room_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ระยะเวลาการจอง</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label small">วันที่</label>
                                            <input type="date" class="form-control" id="date" name="date" placeholder="วันที่" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาเริ่ม</label>
                                            <input type="time" class="form-control" id="time_from" name="time_from" placeholder="เวลาเริ่ม" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาสิ้นสุด</label>
                                            <input type="time" class="form-control" id="time_to" name="time_to" placeholder="เวลาสิ้นสุด" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อการจอง</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="หัวข้อการจอง / ใช้ห้อง" required>
                                </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="attendee_input" class="form-label">ผู้ร่วมใช้ห้อง</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="attendee_input" rows="8" placeholder="รหัสประจำตัว/อีเมล"></input>
                                    <button class="btn btn-outline-secondary" type="button" id="button-add-attendee"><i class="bi bi-plus-square-fill"></i></button>
                                </div>
                                <div id="attendee_feedback" class="invalid-feedback" style="display: none;"></div>
                            </div>
                            <div class="mb-3">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">ชื่อ - นามสกุล</th>
                                            <th scope="col">สถานะ</th>
                                            <th scope="col">รหัส/อีเมล</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendees-table-body">
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="attendees" id="attendees_json" value="[]">
                        </div>
                    </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>

    <!-- Guest Name Modal -->
    <div class="modal fade" id="guestNameModal" tabindex="-1" aria-labelledby="guestNameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guestNameModalLabel">ระบุชื่อผู้เข้าร่วม (Guest)</h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted">ไม่พบข้อมูลในระบบ กรุณาระบุชื่อของผู้เข้าร่วม</p>
                    <div class="mb-3">
                        <label for="guest_name_input" class="form-label">ชื่อ - นามสกุล</label>
                        <input type="text" class="form-control" id="guest_name_input" placeholder="กรอกชื่อผู้เข้าร่วม">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="save-guest-name">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const attendeeInput = document.getElementById('attendee_input');
            const addButton = document.getElementById('button-add-attendee');
            const attendeesTableBody = document.getElementById('attendees-table-body');
            const attendeesJsonInput = document.getElementById('attendees_json');
            const feedbackDiv = document.getElementById('attendee_feedback');
            
            // Guest Modal Elements
            const guestModalEl = document.getElementById('guestNameModal');
            // Check if bootstrap is available globally, usually it is in AdminLTE/Laravel mix
            const guestModal = new bootstrap.Modal(guestModalEl);
            const guestNameInput = document.getElementById('guest_name_input');
            const saveGuestBtn = document.getElementById('save-guest-name');
            
            let attendees = [];
            let cachedUserNames = {}; // Cache for display names to keep JSON clean
            let pendingGuestEmail = '';
            
            // Current User Info
            const currentUser = {
                id: "{{ auth()->user()->student_id }}",
                email: "{{ auth()->user()->email }}"
            };

            function updateAttendeesJson() {
                const payload = {
                    attendee: attendees
                };
                attendeesJsonInput.value = JSON.stringify(payload);
            }

            function renderTable() {
                attendeesTableBody.innerHTML = '';
                attendees.forEach((attendee, index) => {
                    const tr = document.createElement('tr');
                    
                    let nameDisplay = attendee.user_name || cachedUserNames[attendee.user_identify] || '-';
                    let statusBadge = attendee.user_status === 'guest' ? 'bg-secondary' : 'bg-primary';

                    tr.innerHTML = `
                        <th scope="row">${index + 1}</th>
                        <td>${nameDisplay}</td>
                        <td><span class="badge ${statusBadge}">${attendee.user_status}</span></td>
                        <td>${attendee.user_identify}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-attendee" data-index="${index}">
                                <i class="bi bi-trash"></i> ลบ
                            </button>
                        </td>
                    `;
                    attendeesTableBody.appendChild(tr);
                });
                
                document.querySelectorAll('.remove-attendee').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-index'));
                        attendees.splice(idx, 1);
                        renderTable();
                        updateAttendeesJson();
                    });
                });
            }

            // Save Guest Click Handler
            saveGuestBtn.addEventListener('click', function() {
                const name = guestNameInput.value.trim();
                if (name) {
                    attendees.push({
                        user_from: 'mail',
                        user_status: 'guest',
                        user_identify: pendingGuestEmail,
                        user_name: name
                    });
                    renderTable();
                    updateAttendeesJson();
                    attendeeInput.value = '';
                    guestModal.hide();
                } else {
                    alert('กรุณาระบุชื่อ');
                }
            });

            addButton.addEventListener('click', async function() {
                const query = attendeeInput.value.trim();
                if (!query) return;

                // Reset feedback
                attendeeInput.classList.remove('is-invalid');
                feedbackDiv.style.display = 'none';

                function isValidEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                }

                // Self Check
                if (query === currentUser.id || (currentUser.email && query === currentUser.email)) {
                    attendeeInput.classList.add('is-invalid');
                    feedbackDiv.textContent = 'คุณไม่สามารถเพิ่มตัวเองเป็นผู้เข้าร่วมได้ (คุณเป็นผู้จองอยู่แล้ว)';
                    feedbackDiv.style.display = 'block';
                    return;
                }

                const hasAtSymbol = query.includes('@');
                const isEmail = isValidEmail(query);

                if (hasAtSymbol && !isEmail) {
                    attendeeInput.classList.add('is-invalid');
                    feedbackDiv.textContent = 'รูปแบบอีเมลไม่ถูกต้อง';
                    feedbackDiv.style.display = 'block';
                    return;
                }

                try {
                    const response = await fetch('{{ route('dash.booking.find_user') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ query: query })
                    });
                    
                    const result = await response.json();

                    if (response.ok && result.found) {
                        // User found
                        const user = result.user;
                        
                        // Double check if found user is self (in case searched by alternate ID)
                        if (user.student_id === currentUser.id || user.email === currentUser.email) {
                            attendeeInput.classList.add('is-invalid');
                            feedbackDiv.textContent = 'คุณไม่สามารถเพิ่มตัวเองเป็นผู้เข้าร่วมได้ (คุณเป็นผู้จองอยู่แล้ว)';
                            feedbackDiv.style.display = 'block';
                            return;
                        }

                        let existing = attendees.find(a => a.user_identify === user.student_id || a.user_identify === user.email);
                        if (existing) {
                             alert('ผู้ใช้นี้ถูกเพิ่มไปแล้ว');
                             attendeeInput.value = '';
                             return;
                        }

                        // Cache the name for display purposes
                        cachedUserNames[user.student_id] = `${user.name} ${user.surname || ''}`.trim();

                        attendees.push({
                            user_from: 'id',
                            user_status: user.role_label, 
                            user_identify: user.student_id, 
                        });
                        
                        attendeeInput.value = '';
                        renderTable();
                        updateAttendeesJson();

                    } else {
                        // Not found
                        if (isEmail) {
                             // Match logic: If email not found -> Open Modal for Guest Name
                             pendingGuestEmail = query;
                             guestNameInput.value = ''; // Reset input
                             guestModal.show();
                        } else {
                            // Student ID not found -> Error
                            attendeeInput.classList.add('is-invalid');
                            feedbackDiv.textContent = 'ไม่พบรหัสนักศึกษา/ผู้ใช้นี้ในระบบ';
                            feedbackDiv.style.display = 'block';
                        }
                    }

                } catch (error) {
                    console.error(error);
                    alert('เกิดข้อผิดพลาดในการตรวจสอบข้อมูล');
                }
            });
        });
    </script>
</x-dash.layout>