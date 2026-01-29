<x-dash.admin.layout>
    <div class="accordion" id="appSettingsAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                    aria-expanded="true" aria-controls="collapseOne">
                    ตั้งค่าหน้าตา
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#appSettingsAccordion">
                <div class="accordion-body">
                    <form action="{{ route('admin.appsetting.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="action" value="general">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="app_name" class="form-label">ชื่อแอปพลิเคชัน</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name"
                                        placeholder="ชื่อแอปพลิเคชัน"
                                        value="{{ config('app.name', 'PSC-MusicWeb') ?? $AppSetting::getSetting('name') }}"
                                        {{ config('app.name') ? 'readonly' : '' }}>
                                </div>
                                @if (config('app.name'))
                                    <p class="text-muted mt-0">
                                        ต้องการเปลี่ยนชื่อแอปพลิเคชัน กรุณาแก้ไขใน Environment "APP_NAME"
                                    </p>
                                @endif
                                <div class="mb-3">
                                    <label for="app_header" class="form-label">หัวแอป</label>
                                    <input type="text" class="form-control" id="app_header" name="app_header"
                                        placeholder="หัวแอป"
                                        value="{{ $AppSetting::getSetting('header') ?? 'PSC Music' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="app_logo" class="form-label">โลโก้แอปพลิเคชัน</label>
                                    <div class="border rounded p-3 text-center w-25" style="cursor: pointer;"
                                        onclick="document.getElementById('app_logo').click()">
                                        <div id="logo_preview">
                                            @if (asset('/assets/image/logo.png'))
                                                <img src="{{ asset('/assets/image/logo.png') }}" alt="App Logo"
                                                    class="img-fluid">
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                                    <p>คลิกเพื่ออัปโหลดรูปภาพ</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <input class="form-control d-none" type="file" id="app_logo" name="app_logo"
                                        accept="image/*">
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    ประกาศหน้า Login
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#appSettingsAccordion">
                <div class="accordion-body">
                    <form action="{{ route('admin.appsetting.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="note">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notice" class="form-label">ข้อความประกาศ</label>
                                    <textarea id="notice" name="notice">{{ $AppSetting::getNotice() }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    ตั้งค่าประเภทผู้ใช้
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#appSettingsAccordion">
                <div class="accordion-body">
                    <div class="card mb-3">
                        <div class="card-header">รายการประเภทผู้ใช้</div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Database Type</th>
                                        <th>Display Label</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\UserTypeMapping::all() as $mapping)
                                        <tr>
                                            <td>{{ $mapping->db_type }}</td>
                                            <td>{{ $mapping->named_type }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning edit-type-btn"
                                                    data-db-type="{{ $mapping->db_type }}"
                                                    data-named-type="{{ $mapping->named_type }}">
                                                    แก้ไข
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-type-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteMapping-{{ $mapping->db_type }}">
                                                    ลบ
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form action="{{ route('admin.appsetting.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="user_type_update">
                        <div class="card">
                            <div class="card-header">เพิ่ม/แก้ไข ประเภทผู้ใช้</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="db_type" class="form-label">Database Type (เช่น user, admin)</label>
                                    <input type="text" class="form-control" id="db_type" name="db_type"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="named_type" class="form-label">Display Label (เช่น Student,
                                        Staff)</label>
                                    <input type="text" class="form-control" id="named_type" name="named_type"
                                        required>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach (App\Models\UserTypeMapping::all() as $mapping)
        <div class="modal fade" id="deleteMapping-{{ $mapping->db_type }}" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">ลบประเภทผู้ใช้</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        คุณต้องการลบประเภทผู้ใช้ {{ $mapping->named_type }} ใช่หรือไม่?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <form action="{{ route('admin.appsetting.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="db_type" value="{{ $mapping->db_type }}">
                            <button type="submit" name="action" value="user_type_delete"
                                class="btn btn-danger">ลบ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('notice')) {
                const easyMDE = new EasyMDE({
                    element: document.getElementById('notice'),
                    previewClass: ['editor-preview', 'markdown-content'],
                });
            }

            document.getElementById('app_logo').addEventListener('change', function(e) {
                console.log('File selected:', e.target.files[0]);
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    console.log('Valid image file');
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('Image loaded');
                        document.getElementById('logo_preview').innerHTML =
                            `<img src="${e.target.result}" alt="Preview" class="img-fluid" style="max-height: 180px;">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
            document.querySelectorAll('.edit-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('db_type').value = this.dataset.dbType;
                    document.getElementById('named_type').value = this.dataset.namedType;
                    document.getElementById('db_type').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</x-dash.admin.layout>
