<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>PSC Music Login</title>
</head>
<body>
    <div class="flex flex-col w-full min-h-screen">
        <!-- Hero section - responsive height -->
        <div class="flex flex-col h-64 md:h-80 lg:h-96">
            <div class="hero h-full" style="background-image: url(/assets/image/wallpaper/images.jpg);">
                <div class="hero-overlay bg-opacity-60"></div>
                <div class="hero-content text-center items-center p-4">
                    <div class="flex flex-col items-center space-y-2">
                        <img src="/assets/image/logo/Blue_Archive_EN_logo.svg" class="max-h-16 md:max-h-20 lg:max-h-24 w-auto object-contain">
                        <p class="text-white text-sm md:text-base lg:text-lg font-medium">โรงเรียนเกลือบริโภคเสริมไอโอดีน วิทยาลัยเขตบูลอาไคด์</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login form section - flexible height -->
        <div class="flex flex-col items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                <form action="/api/auth/login/index.php" method="post" class="space-y-4">
                    <?php      
                    if (isset($_SESSION['error'])) {
                        echo '<div role="alert" class="alert alert-error mb-4">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 shrink-0 stroke-current"
                                    fill="none"
                                    viewBox="0 0 24 24">
                                    <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>' . htmlspecialchars($_SESSION['error']) . '</span>
                              </div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">เลขประจำตัว:</label>
                        <input type="number" name="stu_id" placeholder="03xxxx, 02xxxx, 99xxxx" class="input validator input-bordered w-full" required title="กรุณากรอกรหัสนักศึกษาเป็นตัวเลขเท่านั้น" maxlength="6" minlength="6">
                        <p class="validator-hint">กรุณากรอกรหัสนักศึกษาเป็นตัวเลขเท่านั้น</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">รหัสผ่าน:</label>
                        <input type="password" name="password" placeholder="Password" 
                               class="input input-bordered w-full" required>
                    </div>
                    
                    <button type="submit" name="login_user" 
                            class="btn btn-primary w-full mt-6">Login</button>
                </form>
                
                <p class="text-center mt-6 text-sm">
                    <a href="https://student.pongsawadi.ac.th/check-password/" 
                       class="link link-primary">ลืมรหัสผ่าน / เพิ่งเข้าระบบครั้งแรกใช่ไหม?</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>