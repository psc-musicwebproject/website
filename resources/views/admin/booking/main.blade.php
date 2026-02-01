@extends('layouts.admin')

@section('content')
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">รหัสการจอง</th>
                <th scope="col">ชื่อการจอง</th>
                <th scope="col">เวลาการจอง</th>
                <th scope="col">สถานะ</th>
                <th scope="col">
                    <button class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#addBooking">เพิ่มการจองห้อง</button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_id }}</td>
                    <td>{{ $booking->booking_name }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($booking->booked_from)->format('d/m/Y H:i') }} -
                        {{ \Carbon\Carbon::parse($booking->booked_to)->format('d/m/Y H:i') }}
                    </td>
                    <td>{{ App\Models\Booking::bookingStatusToText($booking->approval_status) }}</td>
                    <td>
                        <a href="{{ route('admin.booking.detail', ['id' => $booking->booking_id]) }}"
                            class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteBooking-{{ $booking->booking_id }}">ลบ</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="addBooking" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">เพิ่มการจองห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.booking.submit') }} ">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>ผู้จอง (Booker)</h6>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="booker_input"
                                        placeholder="ค้นหาผู้จอง (รหัสนักศึกษา / อีเมล)"
                                        value="{{ Auth::user()->student_id }}">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="button-search-booker"><i class="bi bi-search"></i></button>
                                </div>
                                <div class="invalid-feedback" id="bookerFeedback"></div>
                                <div class="alert alert-info py-2" id="booker-info">
                                    <strong>ผู้จองปัจจุบัน:</strong> <span id="booker-name">{{ Auth::user()->name }}
                                        {{ Auth::user()->surname }}</span> (<span
                                        id="booker-status">{{ Auth::user()->role_label }}</span>)
                                </div>
                                <input type="hidden" name="book_owner_id" id="book_owner_id"
                                    value="{{ Auth::id() }}">
                            </div>
                            <hr>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">ห้องที่ต้องการจอง</label>
                                    <select class="form-select" id="room_id" name="room_id" required
                                        @if (count($rooms) == 0) disabled @endif>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->room_id }}">{{ $room->room_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ระยะเวลาการจอง</label>
                                    <div class="row g-2">
                                        <div class="col-12 mb-2">
                                            <label class="form-label small">วันที่</label>
                                            <div class="input-group" id="date_picker" data-td-target-input="nearest"
                                                data-td-target-toggle="nearest">
                                                <input type="text" class="form-control" id="date_display"
                                                    data-td-target="#date_picker" readonly placeholder="เลือกวันที่"
                                                    required>
                                                <span class="input-group-text" data-td-target="#date_picker"
                                                    data-td-toggle="datetimepicker">
                                                    <i class="bi bi-calendar"></i>
                                                </span>
                                            </div>
                                            <input type="hidden" id="date" name="date">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">เวลาเริ่ม</label>
                                            <div class="input-group" id="time_from_picker"
                                                data-td-target-input="nearest" data-td-target-toggle="nearest">
                                                <input type="text" class="form-control" id="time_from_display"
                                                    data-td-target="#time_from_picker" readonly placeholder="--:--"
                                                    required>
                                                <span class="input-group-text" data-td-target="#time_from_picker"
                                                    data-td-toggle="datetimepicker">
                                                    <i class="bi bi-clock"></i>
                                                </span>
                                            </div>
                                            <input type="hidden" id="time_from" name="time_from">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">เวลาสิ้นสุด</label>
                                            <div class="input-group" id="time_to_picker"
                                                data-td-target-input="nearest" data-td-target-toggle="nearest">
                                                <input type="text" class="form-control" id="time_to_display"
                                                    data-td-target="#time_to_picker" readonly placeholder="--:--"
                                                    required>
                                                <span class="input-group-text" data-td-target="#time_to_picker"
                                                    data-td-toggle="datetimepicker">
                                                    <i class="bi bi-clock"></i>
                                                </span>
                                            </div>
                                            <input type="hidden" id="time_to" name="time_to">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อการจอง</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="หัวข้อการจอง / ใช้ห้อง" required>
                                </div>
                            </div>
                            <hr>
                            <!-- Attendee Section -->
                            <div class="col-12">
                                <h6>รายชื่อผู้เข้าร่วม</h6>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="attendee_input"
                                        placeholder="รหัสนักศึกษา / อีเมล">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="button-add-attendee"><i class="bi bi-plus-square-fill"></i></button>
                                </div>
                                <div class="invalid-feedback" id="attendee-feedback" style="display: none;"></div>

                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ชื่อ-สกุล</th>
                                            <th>สถานะ</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendees-table-body">
                                        <!-- Dynamic Content -->
                                    </tbody>
                                </table>
                                <input type="hidden" name="attendees" id="attendees_json">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึกการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Guest Name Modal for Admin -->
    <div class="modal fade" id="guestNameModalAdmin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ระบุชื่อผู้เข้าร่วม (Guest)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="guest_name_input" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="guest_name_input"
                            placeholder="กรอกชื่อผู้เข้าร่วม">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" id="saveGuestNameBtn">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Date Picker Options (Calendar only)
            const datePickerOptions = {
                display: {
                    viewMode: 'calendar',
                    components: {
                        calendar: true,
                        date: true,
                        month: true,
                        year: true,
                        decades: true,
                        clock: false,
                        hours: false,
                        minutes: false,
                        seconds: false
                    },
                    icons: {
                        date: 'bi bi-calendar',
                        previous: 'bi bi-chevron-left',
                        next: 'bi bi-chevron-right',
                        today: 'bi bi-calendar-check'
                    },
                    buttons: {
                        today: true,
                        clear: false,
                        close: true
                    }
                },
                localization: {
                    ...TempusDominusLocale,
                    format: 'dd/MM/yyyy'
                }
            };

            // Time Picker Options (Clock only, 24-hour)
            const timePickerOptions = {
                display: {
                    viewMode: 'clock',
                    components: {
                        calendar: false,
                        date: false,
                        month: false,
                        year: false,
                        decades: false,
                        clock: true,
                        hours: true,
                        minutes: true,
                        seconds: false
                    },
                    icons: {
                        time: 'bi bi-clock',
                        up: 'bi bi-chevron-up',
                        down: 'bi bi-chevron-down'
                    }
                },
                localization: {
                    ...TempusDominusLocale,
                    format: 'HH:mm'
                },
                stepping: 5
            };

            // Initialize Pickers
            const datePicker = new TempusDominus(document.getElementById('date_picker'), datePickerOptions);
            const timeFromPicker = new TempusDominus(document.getElementById('time_from_picker'),
                timePickerOptions);
            const timeToPicker = new TempusDominus(document.getElementById('time_to_picker'), timePickerOptions);

            // Sync picker values to hidden inputs
            document.getElementById('date_picker').addEventListener('change.td', function(e) {
                if (e.detail.date) {
                    document.getElementById('date').value = e.detail.date.format('YYYY-MM-DD');
                }
            });

            document.getElementById('time_from_picker').addEventListener('change.td', function(e) {
                if (e.detail.date) {
                    document.getElementById('time_from').value = e.detail.date.format('HH:mm');
                }
            });

            document.getElementById('time_to_picker').addEventListener('change.td', function(e) {
                if (e.detail.date) {
                    document.getElementById('time_to').value = e.detail.date.format('HH:mm');
                }
            });

            // Modal instances
            const addBookingModalEl = document.getElementById('addBooking');
            const guestModalEl = document.getElementById('guestNameModalAdmin');

            const addBookingModal = bootstrap.Modal.getOrCreateInstance(addBookingModalEl);
            const guestModal = new bootstrap.Modal(guestModalEl);

            // Booker Elements
            const bookerInput = document.getElementById('booker_input');
            const searchBookerBtn = document.getElementById('button-search-booker');
            const bookerNameSpan = document.getElementById('booker-name');
            const bookOwnerIdInput = document.getElementById('book_owner_id');

            // Attendee Elements
            const attendeeInput = document.getElementById('attendee_input');
            const addButton = document.getElementById('button-add-attendee');
            const attendeesTableBody = document.getElementById('attendees-table-body');
            const attendeesJsonInput = document.getElementById('attendees_json');
            const feedbackDiv = document.getElementById('attendee-feedback');

            const guestNameInput = document.getElementById('guest_name_input');
            const saveGuestBtn = document.getElementById('saveGuestNameBtn');

            // Handle Guest Modal Close -> Reopen Booking Modal
            guestModalEl.addEventListener('hidden.bs.modal', function() {
                // Check if addBooking is already open to avoid flickering or errors (though .show() is usually idempotent)
                // We always want to return to the main modal after the sub-modal closes
                addBookingModal.show();
            });

            // Current Booker State (Defaults to Admin)
            let currentBooker = {
                id: '{{ Auth::user()->student_id }}', // Assuming admin has student_id or use fallback
                email: '{{ Auth::user()->email }}',
                db_id: '{{ Auth::id() }}'
            };

            let attendees = [];
            let cachedUserNames = {};
            let pendingGuestEmail = '';

            // ... (Booker Search Logic stays same) ...
            searchBookerBtn.addEventListener('click', async function() {
                const query = bookerInput.value.trim();
                if (!query) return;

                try {
                    const response = await fetch('{{ route('admin.booking.find_user') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            query: query
                        })
                    });
                    const result = await response.json();

                    if (response.ok && result.found) {
                        const user = result.user;
                        // Update Booker State
                        currentBooker = {
                            id: user.student_id,
                            email: user.email,
                            db_id: user.id
                        };
                        // Update UI
                        bookerNameSpan.textContent = `${user.name} ${user.surname}`;
                        document.getElementById('booker-status').textContent = user.role_label;
                        bookOwnerIdInput.value = user.id;
                        bookerInput.classList.remove('is-invalid');
                    } else {
                        bookerInput.classList.add('is-invalid');
                        bookerFeedback.textContent = 'ไม่พบผู้ใช้งาน';
                        bookerFeedback.style.display = 'block';
                    }
                } catch (error) {
                    console.error(error);
                    bookerFeedback.textContent = 'เกิดข้อผิดพลาด:  ' + error.message;
                    bookerFeedback.style.display = 'block';
                }
            });

            function updateAttendeesJson() {
                attendeesJsonInput.value = JSON.stringify({
                    attendee: attendees
                });
            }

            function renderTable() {
                attendeesTableBody.innerHTML = '';
                attendees.forEach((item, index) => {
                    const tr = document.createElement('tr');

                    let displayName = item.user_name || cachedUserNames[item.user_identify] || '-';
                    let displayStatus = item.user_status; // Already label from backend

                    tr.innerHTML = `
                        <td>${displayName} <br><small class="text-muted">${item.user_identify}</small></td>
                        <td><span class="badge bg-secondary">${displayStatus}</span></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-xs btn-danger" onclick="removeAttendee(${index})"><i class="bi bi-trash"></i> ลบ</button>
                            </div>
                        </td>
                    `;
                    attendeesTableBody.appendChild(tr);
                });
            }

            window.removeAttendee = function(index) {
                attendees.splice(index, 1);
                renderTable();
                updateAttendeesJson();
            }

            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            // --- Attendee Add Logic ---
            addButton.addEventListener('click', async function() {
                const query = attendeeInput.value.trim();
                if (!query) return;

                attendeeInput.classList.remove('is-invalid');
                feedbackDiv.style.display = 'none';

                // Self Check
                if (query === currentBooker.id || (currentBooker.email && query === currentBooker
                        .email)) {
                    attendeeInput.classList.add('is-invalid');
                    feedbackDiv.textContent =
                        'คุณไม่สามารถเพิ่มตัวเองเป็นผู้เข้าร่วมได้ (คุณเป็นผู้จองอยู่แล้ว)';
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
                    const response = await fetch('{{ route('admin.booking.find_user') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            query: query
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.found) {
                        const user = result.user;

                        if (user.id == currentBooker.db_id) {
                            attendeeInput.classList.add('is-invalid');
                            feedbackDiv.textContent =
                                'คุณไม่สามารถเพิ่มตัวเองเป็นผู้เข้าร่วมได้ (คุณเป็นผู้จองอยู่แล้ว)';
                            feedbackDiv.style.display = 'block';
                            return;
                        }

                        // Cache user name for display
                        cachedUserNames[user.student_id] = `${user.name} ${user.surname}`;

                        // User found
                        attendees.push({
                            user_from: 'id',
                            user_status: user.role_label,
                            user_identify: user.student_id,
                        });

                        renderTable();
                        updateAttendeesJson();
                        attendeeInput.value = '';
                    } else {
                        // Not found
                        if (hasAtSymbol) {
                            pendingGuestEmail = query;
                            guestNameInput.value = '';

                            // Swap Modals
                            addBookingModal.hide();
                            guestModal.show();
                        } else {
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
                    guestModal.hide(); // This triggers hidden.bs.modal which reopens addBookingModal
                } else {
                    alert('กรุณาระบุชื่อ');
                }
            });
        });
    </script>

    @foreach ($bookings as $booking)
        <div class="modal fade" id="deleteBooking-{{ $booking->booking_id }}" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">ลบการจองห้อง</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form method="POST"
                        action="{{ route('admin.booking.delete', ['id' => $booking->booking_id]) }}">
                        @csrf
                        <div class="modal-body">
                            <p>คุณแน่ใจหรือไม่ว่าต้องการลบการจองห้องนี้?</p>
                            <p>ชื่อการจอง: {{ $booking->booking_name }}</p>
                            <p>โดย: {{ $booking->user->name }} {{ $booking->user->surname }}
                                ({{ $booking->user->student_id }})
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-danger">ลบการจอง</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
