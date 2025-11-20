<x-dash.admin.layout>
    <div class="accordion" id="appSettingsAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    ตั้งค่าหน้าตา
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#appSettingsAccordion">
                <div class="accordion-body">
                    <form>
                        @csrf
                        <div class="mb-3">
                            <label for="app_name" class="form-label">ชื่อแอปพลิเคชัน</label>
                            <input type="text" class="form-control" id="app_name" placeholder="ชื่อแอปพลิเคชัน"
                                value="{{ config('app.name', 'PSC-MusicWeb') }}"
                                {{ config('app.name') ? 'readonly' : '' }}>
                        </div>
                        @if (config('app.name'))
                            <p class="text-muted mt-0">
                                ต้องการเปลี่ยนชื่อแอปพลิเคชัน กรุณาแก้ไขใน Environment "APP_NAME"
                            </p>
                        @endif
                        <div class="mb-3">
                            <label for="app_logo" class="form-label">โลโก้แอปพลิเคชัน</label>
                            <div class="border rounded p-3 text-center" style="min-height: 200px; cursor: pointer;" onclick="document.getElementById('app_logo').click()">
                                <div id="logo_preview">
                                    @if(asset("/assets/image/logo.png"))
                                        <img src="{{ asset("/assets/image/logo.png") }}" alt="App Logo" class="img-fluid" style="max-height: 180px;">
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                                            <p>คลิกเพื่ออัปโหลดรูปภาพ</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <input class="form-control d-none" type="file" id="app_logo" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="theme_color" class="form-label">สีธีมหลัก</label>
                            <input type="color" class="form-control form-control-color" id="theme_color" value="#563d7c" title="Choose your color">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
    </script>
</x-dash.admin.layout>