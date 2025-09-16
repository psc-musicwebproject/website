<?php
session_start();
include '../api/db/mysql/connect.php';
$error = array();

$id_q = '';

$user_name = '';
$user_surname = '';

if (isset($_SESSION['stu_id'])) {
    $id_q = mysqli_real_escape_string($conn, $_SESSION['stu_id']);
    $query = "SELECT name, surname FROM users WHERE student_id = '$id_q'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $user_name = $row['name'];
        $user_surname = $row['surname'];
    } else {
        header('location: ../auth/login.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preload" href="/lib/adminlte/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="/lib/adminlte/css/adminlte.css" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <title>Dashboard Tests</title>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!--begin::Brand Logo-->
            <div class="sidebar-brand justify-content-start">
                <div class="mx-2 d-flex align-items-center">
                <img src="/assets/image/logo/psc/psc.png" alt="Logo" class="brand-image img-fluid shadow" style="max-height: 2.25rem;">
                <span class="brand-text fw-light"><b>PSC Music</b></span>
                </div>
            </div>
            <!--end::Brand Logo-->
            <div class="sidebar-wrapper">
                <div class="user-panel mx-2 my-2 pb-3 mb-3 d-flex">
                    <div class="info w-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <a class="d-block link-underline link-underline-opacity-0"><?php echo htmlspecialchars($user_name . ' ' . $user_surname); ?></a>
                            </div>
                            <div>
                                <a href="/api/auth/logout/index.php"><i class="bi bi-box-arrow-left"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <nav>
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main Navigation" data-accordian="false" id="navigation">
                        <li class="nav-item">
                            <a href="/dash" class="nav-link active">
                                <i class="bi bi-house-door"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-header">ห้องดนตรี</li>
                        <li class="nav-item">
                            <a href="/dash" class="nav-link">
                                <i class="bi bi-pencil-square"></i>
                                <p>จองเข้าใช้ห้อง</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/dash" class="nav-link">
                                <i class="bi bi-journals"></i>
                                <p>ประวัติการจอง</p>
                            </a>
                        </li>
                        <li class="nav-header">ชมรมดนตรี</li>
                        <li class="nav-item">
                            <a href="/dash" class="nav-link">
                                <i class="bi bi-file-ruled"></i>
                                <p>สมัครสมาชิก</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="app-main">
            <div class="app-content-header">
                <h3>Dashboard</h3>
            </div>
            <div class="app-content">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-primary shadow-sm">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">จำนวนการจอง</span>
                                <span class="info-box-number">25</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-success shadow-sm">
                                <i class="bi bi-check-lg"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">การจองที่อนุมัติ</span>
                                <span class="info-box-number">20</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-warning shadow-sm">
                                <i class="bi bi-files"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">การจองที่รออนุมัติ</span>
                                <span class="info-box-number">3</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-danger shadow-sm">
                                <i class="bi bi-x-lg"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">การจองที่ถูกปฎิเสธ</span>
                                <span class="info-box-number">2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script src="lib/bootstrap5/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="/lib/adminlte/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
</body>
</html>