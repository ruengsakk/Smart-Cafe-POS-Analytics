<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการลูกค้า - Coffee Shop</title>
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
        }
        .btn-coffee {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            border: none;
            color: white;
        }
        .btn-coffee:hover {
            background: linear-gradient(135deg, #D2691E, #8B4513);
            color: white;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .tier-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .tier-vip {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
        }
        .tier-gold {
            background: linear-gradient(135deg, #DAA520, #B8860B);
            color: #fff;
        }
        .tier-silver {
            background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
            color: #000;
        }
        .tier-bronze {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-coffee"></i> Coffee Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-cash-register"></i> POS</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i> รายงาน
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="reports_products.php"><i class="fas fa-box-open"></i> รายงานสินค้า</a></li>
                            <li><a class="dropdown-item" href="reports_sales.php"><i class="fas fa-chart-line"></i> รายงานยอดขาย</a></li>
                            <li><a class="dropdown-item" href="reports_staff.php"><i class="fas fa-users-cog"></i> รายงานพนักงาน</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_orders.php"><i class="fas fa-shopping-cart"></i> จัดการออเดอร์</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_menus.php"><i class="fas fa-utensils"></i> จัดการเมนู</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_customers.php"><i class="fas fa-users"></i> จัดการลูกค้า</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-users"></i> จัดการลูกค้า</h2>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-coffee" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="openCustomerModal()">
                    <i class="fas fa-plus"></i> เพิ่มลูกค้าใหม่
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">ลูกค้าทั้งหมด</h6>
                        <h3 id="totalCustomers">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">สมาชิก VIP</h6>
                        <h3 id="vipCustomers">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">ยอดใช้จ่ายรวม</h6>
                        <h3 id="totalSpent">฿0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">แต้มสะสมรวม</h6>
                        <h3 id="totalPoints">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchCustomer" placeholder="ค้นหาชื่อหรือเบอร์โทร..." onkeyup="searchCustomers()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterTier" onchange="filterCustomers()">
                            <option value="">ระดับทั้งหมด</option>
                            <option value="VIP">VIP</option>
                            <option value="Gold">Gold</option>
                            <option value="Silver">Silver</option>
                            <option value="Bronze">Bronze</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="filterCustomers()">
                            <option value="">สถานะทั้งหมด</option>
                            <option value="1">ใช้งาน</option>
                            <option value="0">ไม่ใช้งาน</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> รีเซ็ต
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เบอร์โทร</th>
                                <th>แต้มสะสม</th>
                                <th>ยอดใช้จ่าย</th>
                                <th>จำนวนครั้ง</th>
                                <th>ระดับ</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">เพิ่มลูกค้าใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="customerForm">
                        <input type="hidden" id="customerId">

                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="customerPhone" required pattern="[0-9]{10}" maxlength="10" placeholder="0812345678">
                            <small class="text-muted">กรอกเบอร์โทร 10 หลัก</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">แต้มสะสมเริ่มต้น</label>
                            <input type="number" class="form-control" id="customerPoints" value="0" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="customerStatus" checked>
                                <label class="form-check-label" for="customerStatus">ใช้งาน</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-coffee" onclick="saveCustomer()">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> ยืนยันการลบ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ว่าต้องการลบลูกค้า <strong id="deleteCustomerName"></strong>?</p>
                    <p class="text-danger mb-0"><small>การลบจะไม่สามารถย้อนกลับได้</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> ลบ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/manage_customers.js"></script>
</body>
</html>
