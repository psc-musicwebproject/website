<x-dash.admin.layout>
    @php
        // Manually pull session data to retrieve and forget it (simulating flash)
        $successMsg = session()->pull('success');
        $importErrors = session()->pull('import_errors');
        $downloadId = session()->pull('download_id');
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger mt-4 alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <h5><i class="bi bi-ban"></i> พบข้อผิดพลาด!</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($successMsg)
        <div class="alert alert-success mt-4 alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {!! $successMsg !!}
        </div>
    @endif
    
    @if($importErrors)
        <div class="alert alert-danger mt-4 alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <h5><i class="bi bi-ban"></i> พบข้อผิดพลาดในการนำเข้า ({{ count($importErrors) }} รายการ)</h5>
            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-bordered text-danger bg-white">
                    <thead>
                        <tr>
                            <th>บรรทัดที่ (Line)</th>
                            <th>รหัสนักเรียน</th>
                            <th>ข้อมูลเบื้องต้น</th>
                            <th>สาเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importErrors as $error)
                            <tr>
                                <td>{{ $error['line'] }}</td>
                                <td>{{ $error['sid'] }}</td>
                                <td>{{ $error['raw_data'] }}</td>
                                <td>Missing: <strong>{{ $error['missing'] }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($downloadId)
        <div class="alert alert-success mt-4 alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <p>ระบบกำลังดาวน์โหลดไฟล์รหัสผ่านให้อัตโนมัติ (เนื่องจากผู้ใช้ใหม่มีรหัสผ่านถูกสุ่ม)... หากไม่เริ่มดาวน์โหลด <a href="{{ route('admin.user.download_credits', $downloadId) }}" class="alert-link">คลิกที่นี่เพื่อดาวน์โหลด</a></p>
        </div>
        <script type="module">
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    window.location.href = "{{ route('admin.user.download_credits', $downloadId) }}";
                }, 1000); // Small delay to ensure page load
            });
        </script>
    @endif
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title">จัดการผู้ใช้</h3>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-square"></i> เพิ่มผู้ใช้
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>รหัสนักเรียน/ผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ชื่อเล่น</th>
                        <th>คณะ/สาขา</th>
                        <th>สถานะ (Type)</th>
                        <th>สถานะไลน์</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->student_id }}</td>
                            <td>
                                {{ $user->name_title }} {{ $user->name }} {{ $user->surname }}
                                <div class="text-muted text-sm">{{ $user->username }}</div>
                            </td>
                            <td>{{ $user->nickname ?? '-' }}</td>
                            <td>{{ $user->major ?? '-' }}</td>
                            <td><span class="badge bg-info">{{ $user->role_label }}</span></td>
                            <td>
                                @if($user->line_bound)
                                    <span class="badge bg-success">Linked</span>
                                    <br>
                                    <span class="text-muted">{{ \App\Services\LineService::getProfileName($user->line_id) }}</span>
                                @else
                                    <span class="badge bg-secondary">Unlinked</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-primary edit-user-btn" 
                                    data-user="{{ json_encode($user) }}"
                                    data-update-url="{{ route('admin.user.update', ['id' => $user->id]) }}">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>
                                <button class="btn btn-danger delete-user-btn"
                                    data-username="{{ $user->name_title }}{{ $user->name }} {{ $user->surname }} ({{ $user->username }})"
                                    data-delete-url="{{ route('admin.user.delete', ['id' => $user->id]) }}">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">แก้ไขผู้ใช้</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_student_id" class="form-label">รหัสผู้ใช้</label>
                                <input type="text" class="form-control" id="edit_student_id" name="student_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="edit_name_title" class="form-label">คำนำหน้า</label>
                                <input type="text" class="form-control" id="edit_name_title" name="name_title">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="edit_name" class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="edit_surname" class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" id="edit_surname" name="surname" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_nickname" class="form-label">ชื่อเล่น</label>
                                <input type="text" class="form-control" id="edit_nickname" name="nickname">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_major" class="form-label">คณะ/สาขา</label>
                                <input type="text" class="form-control" id="edit_major" name="major">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_class" class="form-label">ระดับชั้น</label>
                                <input type="text" class="form-control" id="edit_class" name="class">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone_number" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                        </div>
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">ประเภทผู้ใช้ (Type)</label>
                            <select class="form-select" id="edit_type" name="type" required>
                                @foreach($userTypes as $type)
                                    <option value="{{ $type->db_type }}">{{ $type->named_type }} ({{ $type->db_type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">เปลี่ยนรหัสผ่าน <span class="text-muted">(เว้นว่างหากไม่ต้องการเปลี่ยน)</span></label>
                            <input type="password" class="form-control" id="edit_password" name="password" placeholder="ระบุรหัสผ่านใหม่..." autocomplete="new-password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_reset_password_on_next_login" name="reset_password_on_next_login" value="1">
                            <label class="form-check-label" for="edit_reset_password_on_next_login">บังคับให้ผู้ใช้เปลี่ยนรหัสผ่านในการเข้าสู่ระบบครั้งถัดไป</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">เพิ่มผู้ใช้ใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="addUserTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom" type="button" role="tab" aria-controls="custom" aria-selected="true">เพิ่มทีละคน (Custom)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="csv-tab" data-bs-toggle="tab" data-bs-target="#csv" type="button" role="tab" aria-controls="csv" aria-selected="false">นำเข้าจาก CSV</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="addUserTabContent">
                        <!-- Custom Tab -->
                        <div class="tab-pane fade show active" id="custom" role="tabpanel" aria-labelledby="custom-tab">
                            <form action="{{ route('admin.user.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">รหัสนักเรียน/ผู้ใช้ *</label>
                                        <input type="text" class="form-control" name="student_id" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้ใช้ (Username) *</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">คำนำหน้า</label>
                                        <input type="text" class="form-control" name="name_title" placeholder="นาย/นางสาว">
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">ชื่อ *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">นามสกุล *</label>
                                        <input type="text" class="form-control" name="surname" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ชื่อเล่น</label>
                                        <input type="text" class="form-control" name="nickname">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">คณะ/สาขา <span class="text-danger" id="req_major">* (เฉพาะนักเรียน)</span></label>
                                        <input type="text" class="form-control" name="major">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ระดับชั้น <span class="text-danger" id="req_class">* (เฉพาะนักเรียน)</span></label>
                                        <input type="text" class="form-control" name="class">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="text" class="form-control" name="phone_number">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ประเภทผู้ใช้ (Type) *</label>
                                    <select class="form-select" name="type" required>
                                        @foreach($userTypes as $type)
                                            <option value="{{ $type->db_type }}">{{ $type->named_type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่าน <span class="text-muted">(เว้นว่างเพื่อสุ่มรหัสผ่านอัตโนมัติ)</span></label>
                                    <input type="password" class="form-control" name="password" placeholder="ปล่อยว่างเพื่อรับรหัสผ่านที่ระบบสุ่มให้">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="add_reset_password_on_next_login" name="reset_password_on_next_login" value="1" checked>
                                    <label class="form-check-label" for="add_reset_password_on_next_login">บังคับให้ผู้ใช้เปลี่ยนรหัสผ่านในการเข้าสู่ระบบครั้งถัดไป</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">บันทึกข้อมูล</button>
                            </form>
                        </div>
                        
                        <!-- CSV Tab -->
                        <div class="tab-pane fade" id="csv" role="tabpanel" aria-labelledby="csv-tab">
                            <form action="{{ route('admin.user.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="alert alert-info">
                                    <strong>รูปแบบไฟล์ CSV (ต้องมี Header บรรทัดแรก):</strong><br>
                                    <code>student_id, username, title, name, surname, type, major, class, phone, nickname, password, force_reset</code><br>
                                    <small class="text-muted">* หากไม่ระบุ password ระบบจะสุ่มให้และดาวน์โหลดไฟล์รหัสผ่านอัตโนมัติ</small><br>
                                    <small class="text-muted">** <b>force_reset</b>: ระบุ 1/yes เพื่อบังคับเปลี่ยนรหัส, 0/no เพื่อไม่บังคับ (หากเว้นว่างจะยึดตาม Checkbox ด้านล่าง)</small><br>
                                    <small class="text-muted">*** สำหรับประเภทนักเรียน <b>ต้องห้ามลืมระบุ major และ class ด้วย!</b></small>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="import_reset_password_on_next_login" name="reset_password_on_next_login" value="1" checked>
                                    <label class="form-check-label" for="import_reset_password_on_next_login">บังคับให้ผู้ใช้ที่นำเข้าทั้งหมดเปลี่ยนรหัสผ่านในการเข้าสู่ระบบครั้งถัดไป</label>
                                </div>
                                <div class="mb-3 text-end">
                                    <a href="{{ route('admin.user.template') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-earmark-arrow-down"></i> ดาวน์โหลด Template
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">เลือกไฟล์ CSV</label>
                                    <input type="file" class="form-control" name="csv_file" accept=".csv, .txt" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">นำเข้าข้อมูล</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteUserModalLabel"><i class="bi bi-exclamation-triangle"></i> ยืนยันการลบผู้ใช้</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteUserForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ที่จะลบผู้ใช้รายนี้?</p>
                        <h4 class="text-center text-danger fw-bold" id="delete_user_name"></h4>
                        <p class="text-muted text-sm text-center mt-2">การกระทำนี้ไม่สามารถเรียกคืนได้</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to handle modals -->
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            // Edit User Modal Logic
            var editButtons = document.querySelectorAll('.edit-user-btn');
            var editModalEl = document.getElementById('editUserModal');
            var bootstrap = window.bootstrap;
            var editModal = new bootstrap.Modal(editModalEl);
            var editForm = document.getElementById('editUserForm');

            editButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var userData = JSON.parse(this.dataset.user);
                    var updateUrl = this.dataset.updateUrl;

                    document.getElementById('edit_student_id').value = userData.student_id;
                    document.getElementById('edit_username').value = userData.username;
                    document.getElementById('edit_name').value = userData.name;
                    document.getElementById('edit_surname').value = userData.surname;
                    document.getElementById('edit_type').value = userData.type;
                    
                    // New Fields
                    document.getElementById('edit_name_title').value = userData.name_title || '';
                    document.getElementById('edit_nickname').value = userData.nickname || '';
                    document.getElementById('edit_major').value = userData.major || '';
                    document.getElementById('edit_class').value = userData.class || '';
                    document.getElementById('edit_phone_number').value = userData.phone_number || '';

                    // Reset password fields
                    document.getElementById('edit_password').value = '';
                    document.getElementById('edit_reset_password_on_next_login').checked = userData.reset_password_on_next_login == 1;

                    editForm.action = updateUrl;
                    editModal.show();
                });
            });

            // Delete User Modal Logic
            var deleteButtons = document.querySelectorAll('.delete-user-btn');
            var deleteModalEl = document.getElementById('deleteUserModal');
            var deleteModal = new bootstrap.Modal(deleteModalEl);
            var deleteForm = document.getElementById('deleteUserForm');
            var deleteNameEl = document.getElementById('delete_user_name');

            deleteButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var username = this.dataset.username;
                    var deleteUrl = this.dataset.deleteUrl;

                    deleteNameEl.textContent = username;
                    deleteForm.action = deleteUrl; // Set form action to the specific user delete URL
                    deleteModal.show();
                });
            });
        });
    </script>
</x-dash.admin.layout>