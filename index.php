<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #6B4423, #8B5A3C, #A0826D);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        /* Menu Card Styles */
        .menu-card {
            cursor: pointer;
            height: 320px;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(139,69,19,0.1), rgba(210,105,30,0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-card:hover::before {
            opacity: 1;
        }

        .menu-card:hover {
            border: 3px solid #8B4513;
            transform: scale(1.03);
        }

        .menu-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 20px 0;
        }

        .cart-summary {
            position: sticky;
            top: 20px;
            background: white;
        }

        .order-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 0;
            transition: background 0.3s ease;
        }

        .order-item:hover {
            background: #f8f9fa;
            border-radius: 10px;
            padding-left: 10px;
            margin-left: -10px;
        }

        .btn-coffee {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 500;
            padding: 12px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }

        .btn-coffee:hover {
            background: linear-gradient(135deg, #D2691E, #8B4513);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139,69,19,0.4);
        }

        .btn-coffee:active {
            transform: translateY(0);
        }

        .category-btn {
            border-radius: 30px;
            margin: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
            padding: 10px 20px;
            border: 2px solid transparent;
        }

        .category-btn.active {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border-color: #8B4513;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }

        .category-btn:hover:not(.active) {
            border-color: #8B4513;
            background: #fff;
            transform: translateY(-2px);
        }

        .menu-price {
            font-weight: 700;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5em;
        }

        .total-display {
            background: linear-gradient(135deg, #6B4423, #8B5A3C);
            color: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            font-size: 2em;
            font-weight: 700;
            box-shadow: 0 8px 30px rgba(107,68,35,0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .empty-cart {
            opacity: 0.6;
            text-align: center;
            padding: 40px 20px;
        }

        .empty-cart i {
            font-size: 3rem;
            color: #ccc;
        }

        /* Quantity Controls */
        .btn-group .btn {
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-group .btn:hover {
            transform: scale(1.1);
        }

        /* Category Filter Card */
        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 3px solid #8B4513;
            font-weight: 600;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-card {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-card {
                height: 250px;
            }
            .menu-icon {
                font-size: 2.5rem;
            }
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i> รายงาน
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="reports_products.php"><i class="fas fa-box-open"></i> รายงานสินค้า</a></li>
                            <li><a class="dropdown-item" href="reports_sales.php"><i class="fas fa-chart-line"></i> รายงานยอดขาย</a></li>
                            <li><a class="dropdown-item" href="reports_staff.php"><i class="fas fa-users-cog"></i> รายงานพนักงาน</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="reports.php"><i class="fas fa-list"></i> รายงานทั้งหมด</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_orders.php"><i class="fas fa-shopping-cart"></i> จัดการออเดอร์</a>
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