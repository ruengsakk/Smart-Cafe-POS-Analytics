<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Sarabun', sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .menu-card {
            cursor: pointer;
            height: 280px;
        }
        .menu-card:hover {
            border: 2px solid #8B4513;
        }
        .cart-summary {
            position: sticky;
            top: 20px;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .btn-coffee {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            border: none;
            color: white;
            border-radius: 10px;
        }
        .btn-coffee:hover {
            background: linear-gradient(135deg, #D2691E, #8B4513);
            color: white;
        }
        .category-btn {
            border-radius: 25px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .category-btn.active {
            background: #8B4513;
            color: white;
        }
        .menu-price {
            font-weight: bold;
            color: #8B4513;
            font-size: 1.2em;
        }
        .total-display {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-coffee"></i> Coffee Shop POS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-cash-register"></i> POS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> รายงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_menus.php"><i class="fas fa-utensils"></i> จัดการเมนู</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_customers.php"><i class="fas fa-users"></i> จัดการลูกค้า</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Menu Section -->
            <div class="col-lg-8">
                <!-- Category Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-th-large"></i> หมวดหมู่สินค้า</h5>
                        <div id="categoryButtons">
                            <button class="btn btn-outline-secondary category-btn active" data-category="all">ทั้งหมด</button>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="row" id="menuContainer">
                    <!-- Menu items will be loaded here -->
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card cart-summary">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> รายการสั่งซื้อ</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderItems">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>ยังไม่มีรายการสั่งซื้อ</p>
                            </div>
                        </div>

                        <div class="total-display mt-3" id="totalDisplay">
                            รวม: ฿0.00
                        </div>

                        <!-- Customer Info -->
                        <div class="mt-3">
                            <label class="form-label">ลูกค้า (ไม่บังคับ)</label>
                            <select class="form-select" id="customerSelect">
                                <option value="">ลูกค้าทั่วไป</option>
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div class="mt-3">
                            <label class="form-label">วิธีการชำระเงิน</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">เงินสด</option>
                                <option value="qr">QR Code</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-coffee btn-lg" id="processOrder" disabled>
                                <i class="fas fa-credit-card"></i> ชำระเงิน
                            </button>
                            <button class="btn btn-outline-secondary" id="clearOrder">
                                <i class="fas fa-trash"></i> ล้างรายการ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ใบเสร็จ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="receiptContent">
                    <!-- Receipt content will be generated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-coffee" onclick="printReceipt()">
                        <i class="fas fa-print"></i> พิมพ์ใบเสร็จ
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pos.js"></script>
</body>
</html>