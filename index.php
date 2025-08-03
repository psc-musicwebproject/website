<?php 
    session_start();

    if (!isset($_SESSION['username'])) {
        header('location: auth/login.php');
    }

    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['username']);
        header('location: login.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/lib/css/bootstrap.min.css" rel=stylesheet>
    <title>Document</title>
</head>
<body>
    
</body>
</html>