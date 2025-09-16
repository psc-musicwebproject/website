<?php 
    session_destroy();
    unset($_SESSION['stu_id']);
    header('location: ../../../auth/login.php');
?>