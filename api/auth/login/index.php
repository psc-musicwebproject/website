<?php 
    session_start();
    include '../../db/mysql/connect.php';

    $error = array();

    if (isset($_POST['login_user'])) {
        $stu_id = mysqli_real_escape_string($conn, $_POST['stu_id']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        if (empty($stu_id)) {
            array_push($error, "ไม่ได้ระบุรหัสนักศึกษา, โปรดระบุรหัสนักศึกษา");
        }

        if (empty($password)) {
            array_push($error, "ไม่ได้ระบุรหัสผ่าน, โปรดระบุรหัสผ่าน");
        }

        if (count($error) == 0) {
            $password = hash('sha256', $password);
            $query = "SELECT * FROM users WHERE student_id = '$stu_id' AND password = '$password' ";
            $result = mysqli_query($conn, $query);
            echo mysqli_num_rows($result);

            if (mysqli_num_rows($result) == 1) {
                $_SESSION['stu_id'] = $stu_id;
                header("location: ../../../dash/index.php");
            } else {
                array_push($error, "รหัสนักศึกษา หรือ รหัสผ่านไม่ถูกต้อง");
                $_SESSION['error'] = "รหัสนักศึกษา หรือ รหัสผ่านไม่ถูกต้อง";
                header("location: ../../../auth/login.php");
            }
        } else {
            array_push($error, "กรุณากรอกข้อมูลให้ครบถ้วน");
            $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
            header("location: ../../../auth/login.php");
        }
    }

?>
