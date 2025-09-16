<?php 
    session_start();

    if (!isset($_SESSION['stu_id'])) {
        header('location: auth/login.php');
    } else {
        header('location: dash/index.php');
    }
?>