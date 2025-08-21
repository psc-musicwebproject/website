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
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Dashboard Tests</title>
</head>
<body>
    <div class="navbar bg-base-100 shadow-sm">
        <div class="flex-none">
            <button class="btn btn-square btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path> </svg>
            </button>
        </div>
        <div class="flex-1">
            <div class="inline-flex items-center space-x-2">
                <a href="/dash/index.php" class="mx-2">
                    <img src="/assets/image/logo/psc/psc.png" class="inline-block max-h-14 object-contain">
                </a>
                <p>ห้อง PSC Music วิทยาลัยเทคโนโลยีพงษ์สวัสดิ์</p>
            </div>
        </div>
        <div class="flex-none">
            <div class="inline-flex items-center space-x-2">
                <button class="btn btn-ghost">
                    <p class="text-sm"><?php echo htmlspecialchars($user_name . ' ' . $user_surname . ' (' . $id_q . ')'); ?></p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path> </svg>
                </button>
            </div>
        </div>
    </div>
</body>
</html>