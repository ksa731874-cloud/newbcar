<?php
session_start();
require_once 'config.php';
require_once 'classes/db.php';
require_once 'classes/user.php';

$User = new User();

if ($User->isLoggedIn()) {
    $User->redirect('index.php');
}

$error_msg = null;
if (isset($_POST['login'])) {
    $data = array(
        'email' => $_POST['email'],
        'username' => $_POST['email'],
        'password' => md5($_POST['password']),
    );

    if ($User->login($data)) {
        $User->redirect('index.php');
    } else {
        $error_msg = "البيانات المدخلة غير صحيحة، يرجى التأكد والمحاولة مرة أخرى.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>B-CARE | تسجيل الدخول</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #156394;
            --accent-color: #2a88c4;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(21, 99, 148, 0.3);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(42, 136, 196, 0.2);
            color: white;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .btn-login {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(21, 99, 148, 0.4);
            color: white;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h4 class="text-white fw-800 mb-1">B-CARE</h4>
            <p class="text-white-50 small mb-0">لوحة التحكم السحابية 2.0</p>
        </div>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني / اسم المستخدم</label>
                <div class="input-group">
                    <input type="text" name="email" class="form-control text-start" placeholder="أدخل بياناتك هنا..."
                        required style="border-radius: 0 12px 12px 0;">
                    <span class="input-group-text bg-transparent border-0 text-white-50"
                        style="border-radius: 12px 0 0 12px;"><i class="bi bi-envelope"></i></span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">كلمة المرور</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control text-start" placeholder="••••••••"
                        required style="border-radius: 0 12px 12px 0;">
                    <span class="input-group-text bg-transparent border-0 text-white-50"
                        style="border-radius: 12px 0 0 12px;"><i class="bi bi-key"></i></span>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-login">
                تسجيل الدخول <i class="bi bi-arrow-left-short ms-1 fs-5"></i>
            </button>
        </form>

        <div class="footer-text">
            حقوق الطبع والنشر © 2025 B-CARE للحلول الأمنية
        </div>
    </div>

    <?php if ($error_msg): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'خطأ في الدخول',
                text: '<?= $error_msg ?>',
                confirmButtonColor: '#156394',
                confirmButtonText: 'حسناً'
            });
        </script>
    <?php endif; ?>
</body>

</html>