<?php session_start(); ?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ระบบจองห้อง PSC Music | เข้าสู่ระบบ</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="ระบบจองห้อง PSC Music | เข้าสู่ระบบ" />
    <meta name="author" content="Pongsawadi Technological College" />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="/lib/adminlte/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="/lib/adminlte/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="login-page bg-body-secondary">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header">
            <img src="/assets/image/logo/psc/psc.png" class="mx-auto mb-1 d-block" style="max-height: 3.5rem; width: auto;"/>
            <p class="mb-0 text-center fs-6"><b>ห้อง PSC Music วิทยาลัยเทคโนโลยีพงษ์สวัสดิ์</b></p>
        </div>
        <div class="card-body login-card-body">
          <?php      
          if (isset($_SESSION['error'])) {
              echo '<div class="alert alert-danger" role="alert">
                      ' . htmlspecialchars($_SESSION['error']) . '
                    </div>';
              unset($_SESSION['error']);
          }
          ?>
          <form action="/api/auth/login/index.php" method="post">
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="stu_id" name="stu_id" type="text" class="form-control" value="" placeholder=""
                  data-bs-toggle="tooltip" data-bs-placement="right" title="กรุณากรอกรหัสประจำตัวเป็นตัวเลขเท่านั้น"/>
                <label for="stu_id">รหัสประจำตัว</label>
              </div>
              <div class="invalid-tooltip">
                กรุณากรอกรหัสประจำตัวเป็นตัวเลขเท่านั้น
              </div>
            </div>
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="password" name="password" type="password" class="form-control" placeholder=""
                  data-bs-toggle="tooltip" data-bs-placement="right" title="กรุณากรอกรหัสผ่าน"/>
                <label for="password  ">รหัสผ่าน</label>
              </div>
              <div class="invalid-tooltip">
                กรุณากรอกรหัสผ่าน
              </div>
            </div>
            <!--begin::Row-->
            <div class="row justify-content-end">
              <!-- /.col -->
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" name="login_user" class="btn btn-primary">Sign In</button>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>
          <div class="social-auth-links text-center mb-3 d-grid gap-2">
          </div>
          <!-- /.social-auth-links -->
          <p class="mb-1"><a href="forgot-password.html">ลืมรหัสผ่าน / เพิ่งเข้าระบบครั้งแรกใช่ไหม?</a></p>
        </div>
        <!-- /.login-card-body -->
      </div>
    </div>
    <!-- /.login-box -->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="/lib/adminlte/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin: Form Validation Script -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'manual'
          });
        });

        const loginForm = document.getElementById('loginForm');
        const stuIdInput = document.getElementById('stu_id');
        const passwordInput = document.getElementById('password');

        // Handle student ID input to only allow numbers
        stuIdInput.addEventListener('input', function(e) {
          this.value = this.value.replace(/[^0-9]/g, '');
          validateField(this);
        });

        // Handle password input validation
        passwordInput.addEventListener('input', function(e) {
          validateField(this);
        });

        // Validate individual field
        function validateField(field) {
          const tooltip = bootstrap.Tooltip.getInstance(field);
          
          if (field.value.trim() === '') {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            tooltip?.show();
          } else if (field.id === 'stu_id' && !/^\d+$/.test(field.value)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            tooltip?.show();
          } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            tooltip?.hide();
          }
        }

        // Form submission handler
        loginForm.addEventListener('submit', function(event) {
          event.preventDefault();
          
          let isValid = true;
          
          // Validate Student ID
          if (stuIdInput.value.trim() === '' || !/^\d+$/.test(stuIdInput.value)) {
            stuIdInput.classList.add('is-invalid');
            stuIdInput.classList.remove('is-valid');
            bootstrap.Tooltip.getInstance(stuIdInput)?.show();
            isValid = false;
          }

          // Validate Password
          if (passwordInput.value.trim() === '') {
            passwordInput.classList.add('is-invalid');
            passwordInput.classList.remove('is-valid');
            bootstrap.Tooltip.getInstance(passwordInput)?.show();
            isValid = false;
          }

          if (isValid) {
            // If form is valid, submit it
            this.submit();
          }
        });
      });
    </script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
