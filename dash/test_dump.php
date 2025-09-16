<?php
session_start();
include '../api/db/mysql/connect.php';
$error = array();

$id_q = '';

$user_name = '';
$user_surname = '';
$user_status = '';

if (isset($_SESSION['stu_id'])) {
    $id_q = mysqli_real_escape_string($conn, $_SESSION['stu_id']);
    $query = "SELECT name, surname, type FROM users WHERE student_id = '$id_q'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $user_name = $row['name'];
        $user_surname = $row['surname'];
        $user_status = $row['type'];
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
    <h1>Test Dashboard</h1>
    <p>Current ID: <?php echo(htmlspecialchars($id_q)) ?></p>
    <p>Name: <?php echo(htmlspecialchars($user_name).' '.htmlspecialchars($user_surname)) ?></p>
    <p>Status: <?php echo(htmlspecialchars($user_status)) ?></p>
    <button type="button" class="btn btn-primary"><a href="/api/auth/logout">Logout</a></button>
</body>
</html>