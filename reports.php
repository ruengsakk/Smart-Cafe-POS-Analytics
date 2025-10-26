<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานและการศึกษา SQL - Coffee Shop Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css" rel="stylesheet">
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
            margin-bottom: 20px;
        }
        .report-section {
            margin-bottom: 30px;
        }
        .nav-pills .nav-link.active {
            background-color: #8B4513;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
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
        .highlight-sql {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
        }
        /* Category Headers */
        .category-section {
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .category-header {
            padding: 16px 20px;
            margin: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            user-select: none;
        }
        .category-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .category-header h6 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .category-header .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }
        .category-header.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }

        /* Product Category - Blue Theme */
        .category-products .category-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-left: 6px solid #3730a3;
        }
        .category-products .nav-link {
            background-color: #f8faff;
            border: 1px solid #e0e7ff;
            color: #4f46e5;
        }
        .category-products .nav-link:hover {
            background-color: #e0e7ff;
            color: #3730a3;
            border-color: #c7d2fe;
        }
        .category-products .nav-link.active {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-color: #4f46e5;
        }

        /* Order Category - Green Theme */
        .category-orders .category-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border-left: 6px solid #047857;
        }
        .category-orders .nav-link {
            background-color: #f0fdf9;
            border: 1px solid #d1fae5;
            color: #059669;
        }
        .category-orders .nav-link:hover {
            background-color: #d1fae5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .category-orders .nav-link.active {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border-color: #059669;
        }

        /* Staff Category - Orange Theme */
        .category-staff .category-header {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            border-left: 6px solid #c2410c;
        }
        .category-staff .nav-link {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            color: #ea580c;
        }
        .category-staff .nav-link:hover {
            background-color: #fed7aa;
            color: #c2410c;
            border-color: #fdba74;
        }
        .category-staff .nav-link.active {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            border-color: #ea580c;
        }

        /* Advanced Category - Dark Theme */
        .category-advanced .category-header {
            background: linear-gradient(135deg, #374151, #4b5563);
            color: white;
            border-left: 6px solid #1f2937;
        }
        .category-advanced .nav-link {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        .category-advanced .nav-link:hover {
            background-color: #e5e7eb;
            color: #1f2937;
            border-color: #d1d5db;
        }
        .category-advanced .nav-link.active {
            background: linear-gradient(135deg, #374151, #4b5563);
            color: white;
            border-color: #374151;
        }

        /* Navigation Links */
        .category-content {
            padding: 8px 12px;
            background-color: #fafbfc;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .nav-pills .nav-link {
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            padding: 10px 16px;
            border: 1px solid transparent;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        .nav-pills .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255,255,255,0.1);
            transition: width 0.3s ease;
        }
        .nav-pills .nav-link:hover::before {
            width: 100%;
        }
        .nav-pills .nav-link i {
            width: 18px;
            margin-right: 10px;
            font-size: 1rem;
        }
        .nav-pills .nav-link .badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            margin-left: 8px;
        }

        /* Collapsible Animation */
        .category-content {
            transition: all 0.4s ease;
            overflow: hidden;
        }
        .category-content.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
        }
        .category-content:not(.collapsed) {
            max-height: 1000px;
            opacity: 1;
        }

        /* Advanced Filters Styling */
        #advanced-filters {
            transition: all 0.3s ease;
        }
        #advanced-filters .form-label {
            font-weight: 600;
            color: #8B4513;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        #advanced-filters .form-label i {
            color: #D2691E;
            margin-right: 5px;
        }
        #advanced-filters input::placeholder {
            color: #999;
            font-style: italic;
        }
        #advanced-filters .text-muted {
            font-size: 0.75rem;
            display: block;
            margin-top: 2px;
        }
        #active-filters {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 3px solid #ff9800;
        }
        #active-filters-list div {
            padding: 2px 0;
            color: #8B4513;
        }
        .card-header[onclick] {
            transition: background-color 0.2s;
        }
        .card-header[onclick]:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-coffee"></i> Coffee Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-cash-register"></i> POS</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <a class="nav-link" href="manage_customers.php"><i class="fas fa-users"></i> จัดการลูกค้า</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <!-- Date Range Filter -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><i class="fas fa-calendar-range"></i> เลือกช่วงเวลา</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">ตั้งแต่วันที่</label>
                            <input type="date" class="form-control" id="startDate" onchange="updateDateRange()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ถึงวันที่</label>
                            <input type="date" class="form-control" id="endDate" onchange="updateDateRange()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ช่วงเวลาที่กำหนดไว้</label>
                            <select class="form-select" id="predefinedRange" onchange="setPredefinedRange()">
                                <option value="">กำหนดเอง</option>
                                <option value="today">วันนี้</option>
                                <option value="yesterday">เมื่อวาน</option>
                                <option value="last7days" selected>7 วันล่าสุด</option>
                                <option value="last30days">30 วันล่าสุด</option>
                                <option value="thismonth">เดือนนี้</option>
                                <option value="lastmonth">เดือนที่แล้ว</option>
                                <option value="thisyear">ปีนี้</option>
                            </select>
                        </div>
                        <button class="btn btn-coffee btn-sm w-100" onclick="refreshCurrentReport()">
                            <i class="fas fa-sync-alt"></i> อัปเดตรายงาน
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="card mb-3">
                    <div class="card-header" onclick="toggleAdvancedFilters()" style="cursor: pointer;">
                        <h6>
                            <i class="fas fa-filter"></i> ตัวกรองขั้นสูง
                            <span class="float-end" id="filter-toggle-icon">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </h6>
                    </div>
                    <div class="card-body" id="advanced-filters" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-utensils"></i> ชื่อเมนู
                            </label>
                            <input type="text" class="form-control form-control-sm" id="filterMenuName"
                                   placeholder="เช่น Latte, กาแฟ">
                            <small class="text-muted">ใช้กับรายงานสินค้า</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i> ชื่อพนักงาน
                            </label>
                            <input type="text" class="form-control form-control-sm" id="filterStaffName"
                                   placeholder="เช่น สมชาย, กานดา">
                            <small class="text-muted">ใช้กับรายงานพนักงาน</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-users"></i> ชื่อลูกค้า
                            </label>
                            <input type="text" class="form-control form-control-sm" id="filterCustomerName"
                                   placeholder="เช่น สมหญิง">
                            <small class="text-muted">ใช้กับรายงานลูกค้า</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-day"></i> เดือน
                            </label>
                            <select class="form-select form-select-sm" id="filterMonth">
                                <option value="">ทุกเดือน</option>
                                <option value="1">มกราคม</option>
                                <option value="2">กุมภาพันธ์</option>
                                <option value="3">มีนาคม</option>
                                <option value="4">เมษายน</option>
                                <option value="5">พฤษภาคม</option>
                                <option value="6">มิถุนายน</option>
                                <option value="7">กรกฎาคม</option>
                                <option value="8">สิงหาคม</option>
                                <option value="9">กันยายน</option>
                                <option value="10">ตุลาคม</option>
                                <option value="11">พฤศจิกายน</option>
                                <option value="12">ธันวาคม</option>
                            </select>
                            <small class="text-muted">กรองเฉพาะเดือนที่เลือก</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="applyAdvancedFilters()">
                                <i class="fas fa-check"></i> ใช้ตัวกรอง
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearAdvancedFilters()">
                                <i class="fas fa-times"></i> ล้างตัวกรอง
                            </button>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded" id="active-filters" style="display: none;">
                            <small class="text-muted">
                                <strong>ตัวกรองที่ใช้:</strong>
                                <div id="active-filters-list"></div>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> รายงานและ SQL</h5>
                        <div class="mt-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="reportSearch" placeholder="ค้นหารายงาน..."
                                       onkeyup="searchReports(this.value)">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="nav nav-pills flex-column" id="report-tabs">

                            <!-- Product Reports Category -->
                            <div class="category-section category-products">
                                <div class="category-header" onclick="toggleCategory('products')">
                                    <h6>
                                        <span><i class="fas fa-box-open"></i> รายงานเกี่ยวกับสินค้า</span>
                                        <span class="toggle-icon">⌄</span>
                                    </h6>
                                </div>
                                <div class="category-content" id="category-products">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#top-products">
                                        <i class="fas fa-star"></i> สินค้าขายดีที่สุด
                                        <span class="badge bg-primary">ยอดนิยม</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#product-inventory">
                                        <i class="fas fa-boxes"></i> สต็อกและสถานะสินค้า
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#product-comparison">
                                        <i class="fas fa-chart-bar"></i> เปรียบเทียบสินค้าตามหมวดหมู่
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#product-performance">
                                        <i class="fas fa-chart-line"></i> ประสิทธิภาพสินค้า
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#product-trends">
                                        <i class="fas fa-trending-up"></i> เทรนด์สินค้า
                                        <span class="badge bg-info">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#slow-moving-products">
                                        <i class="fas fa-turtle"></i> สินค้าขายช้า
                                        <span class="badge bg-warning">สำคัญ</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Order Reports Category -->
                            <div class="category-section category-orders">
                                <div class="category-header" onclick="toggleCategory('orders')">
                                    <h6>
                                        <span><i class="fas fa-shopping-cart"></i> รายงานเกี่ยวกับรายการสั่งซื้อ</span>
                                        <span class="toggle-icon">⌄</span>
                                    </h6>
                                </div>
                                <div class="category-content" id="category-orders">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#daily-sales">
                                        <i class="fas fa-chart-line"></i> ยอดขายรายวัน
                                        <span class="badge bg-success">พื้นฐาน</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#monthly-sales">
                                        <i class="fas fa-calendar-alt"></i> ยอดขายรายเดือน
                                        <span class="badge bg-success">พื้นฐาน</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#order-patterns">
                                        <i class="fas fa-shopping-basket"></i> รูปแบบการสั่งซื้อ
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#order-size-analysis">
                                        <i class="fas fa-chart-pie"></i> วิเคราะห์ขนาดออเดอร์
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#customer-analysis">
                                        <i class="fas fa-users"></i> วิเคราะห์ลูกค้า
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#payment-analysis">
                                        <i class="fas fa-credit-card"></i> วิธีการชำระเงิน
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#hourly-analysis">
                                        <i class="fas fa-clock"></i> วิเคราะห์ตามชั่วโมง
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#peak-hours">
                                        <i class="fas fa-chart-area"></i> ช่วงเวลาเร่าซื้อ
                                        <span class="badge bg-info">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#best-day">
                                        <i class="fas fa-trophy"></i> วันที่ขายดีที่สุด
                                        <span class="badge bg-warning">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#product-summary">
                                        <i class="fas fa-clipboard-list"></i> สรุปยอดขายสินค้า
                                        <span class="badge bg-warning">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#monthly-menu-count">
                                        <i class="fas fa-calendar-alt"></i> จำนวนเมนูแต่ละเดือน
                                        <span class="badge bg-warning">ใหม่</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Staff Reports Category -->
                            <div class="category-section category-staff">
                                <div class="category-header" onclick="toggleCategory('staff')">
                                    <h6>
                                        <span><i class="fas fa-users-cog"></i> รายงานเกี่ยวกับพนักงาน</span>
                                        <span class="toggle-icon">⌄</span>
                                    </h6>
                                </div>
                                <div class="category-content" id="category-staff">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-ranking">
                                        <i class="fas fa-trophy"></i> อันดับพนักงาน
                                        <span class="badge bg-primary">ยอดนิยม</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-performance">
                                        <i class="fas fa-user-tie"></i> ผลงานพนักงาน
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-products">
                                        <i class="fas fa-box"></i> สินค้าที่พนักงานขาย
                                        <span class="badge bg-info">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-orders">
                                        <i class="fas fa-receipt"></i> ออเดอร์ที่พนักงานรับผิดชอบ
                                        <span class="badge bg-info">ใหม่</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-efficiency">
                                        <i class="fas fa-tachometer-alt"></i> ประสิทธิภาพพนักงาน
                                        <span class="badge bg-warning">สำคัญ</span>
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-comparison">
                                        <i class="fas fa-balance-scale"></i> เปรียบเทียบพนักงาน
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-customers">
                                        <i class="fas fa-user-friends"></i> พนักงานขายให้ลูกค้าใคร
                                        <span class="badge bg-warning">ใหม่</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Advanced SQL Category -->
                            <div class="category-section category-advanced">
                                <div class="category-header" onclick="toggleCategory('advanced')">
                                    <h6>
                                        <span><i class="fas fa-database"></i> SQL ขั้นสูง</span>
                                        <span class="toggle-icon">⌄</span>
                                    </h6>
                                </div>
                                <div class="category-content" id="category-advanced">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#advanced-queries">
                                        <i class="fas fa-code"></i> คำสั่ง SQL ขั้นสูง
                                        <span class="badge bg-dark">เรียนรู้</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9">
                <div class="tab-content">

                    <!-- Daily Sales Report -->
                    <div class="tab-pane fade show active" id="daily-sales">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-line"></i> รายงานยอดขายรายวัน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ GROUP BY, DATE functions, และ Aggregate functions
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('daily_sales')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="daily-sales-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Sales Report -->
                    <div class="tab-pane fade" id="monthly-sales">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-calendar-alt"></i> รายงานยอดขายรายเดือน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ DATE_FORMAT, YEAR(), MONTH() functions
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('monthly_sales')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="monthly-sales-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products Report -->
                    <div class="tab-pane fade" id="top-products">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-star"></i> รายงานสินค้าขายดี</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ JOIN, SUM(), COUNT() และ ORDER BY
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('top_products')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="top-products-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Analysis -->
                    <div class="tab-pane fade" id="customer-analysis">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-users"></i> วิเคราะห์ลูกค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ LEFT JOIN, COALESCE, และ CASE WHEN
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('customer_analysis')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="customer-analysis-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Performance -->
                    <div class="tab-pane fade" id="staff-performance">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-user-tie"></i> ผลงานพนักงาน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ INNER JOIN และการวิเคราะห์ผลงาน
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_performance')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="staff-performance-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Analysis -->
                    <div class="tab-pane fade" id="payment-analysis">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-credit-card"></i> วิเคราะห์วิธีการชำระเงิน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ GROUP BY กับ ENUM และ Percentage calculation
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('payment_analysis')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="payment-analysis-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Analysis -->
                    <div class="tab-pane fade" id="hourly-analysis">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clock"></i> วิเคราะห์ตามชั่วโมง</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ HOUR(), TIME functions และ time-based analysis
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('hourly_analysis')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="hourly-analysis-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Inventory Report -->
                    <div class="tab-pane fade" id="product-inventory">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-boxes"></i> รายงานสต็อกและสถิติสินค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์ปริมาณการขายและสินค้าคงเหลือ
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('product_inventory')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="product-inventory-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Patterns Report -->
                    <div class="tab-pane fade" id="order-patterns">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-shopping-basket"></i> รูปแบบการสั่งซื้อ</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์พฤติกรรมการสั่งซื้อของลูกค้า
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('order_patterns')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="order-patterns-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Ranking Report -->
                    <div class="tab-pane fade" id="staff-ranking">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-trophy"></i> อันดับพนักงานยอดเยี่ยม</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การจัดอันดับและเปรียบเทียบผลงานพนักงาน
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_ranking')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="staff-ranking-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Comparison Report -->
                    <div class="tab-pane fade" id="product-comparison">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-bar"></i> เปรียบเทียบสินค้าตามหมวดหมู่</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การเปรียบเทียบผลงานของแต่ละหมวดหมู่สินค้า
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('product_comparison')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="product-comparison-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Size Analysis Report -->
                    <div class="tab-pane fade" id="order-size-analysis">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-pie"></i> วิเคราะห์ขนาดและปริมาณออเดอร์</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์ขนาดและปริมาณออเดอร์ต่างๆ
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('order_size_analysis')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="order-size-analysis-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Queries -->
                    <div class="tab-pane fade" id="advanced-queries">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-database"></i> SQL ขั้นสูง</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> Subqueries, Window Functions, และ Complex Joins
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('advanced_queries')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="advanced-queries-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance Report -->
                    <div class="tab-pane fade" id="product-performance">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-line"></i> ประสิทธิภาพสินค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์ผลตอบแทนและประสิทธิภาพของสินค้า
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('product_performance')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="product-performance-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Trends Report -->
                    <div class="tab-pane fade" id="product-trends">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-trending-up"></i> เทรนด์สินค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์แนวโน้มการขายตามช่วงเวลา
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('product_trends')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="product-trends-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Slow Moving Products Report -->
                    <div class="tab-pane fade" id="slow-moving-products">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-turtle"></i> สินค้าขายช้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การระบุสินค้าที่ต้องการความช่วยเหลือในการขาย
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('slow_moving_products')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="slow-moving-products-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Peak Hours Report -->
                    <div class="tab-pane fade" id="peak-hours">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-area"></i> ช่วงเวลาเร่าซื้อ</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์รูปแบบการซื้อตามเวลา
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('peak_hours')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="peak-hours-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Products Report -->
                    <div class="tab-pane fade" id="staff-products">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-box"></i> สินค้าที่พนักงานขาย</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์ว่าพนักงานแต่ละคนขายสินค้าอะไรบ้าง
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_products')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-products-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Orders Report -->
                    <div class="tab-pane fade" id="staff-orders">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-receipt"></i> ออเดอร์ที่พนักงานรับผิดชอบ</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวิเคราะห์ออเดอร์ที่พนักงานแต่ละคนจัดการ
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_orders')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-orders-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Efficiency Report -->
                    <div class="tab-pane fade" id="staff-efficiency">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-tachometer-alt"></i> ประสิทธิภาพพนักงาน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การวัดประสิทธิภาพและผลิตภาพของพนักงาน
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_efficiency')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-efficiency-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Comparison Report -->
                    <div class="tab-pane fade" id="staff-comparison">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-balance-scale"></i> เปรียบเทียบพนักงาน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การเปรียบเทียบผลงานพนักงานกับค่าเฉลี่ยของทีม
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_comparison')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-comparison-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Best Day Report -->
                    <div class="tab-pane fade" id="best-day">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-trophy"></i> วันที่ขายดีที่สุด</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> หาวันที่มียอดขายสูงสุด พร้อมจัดอันดับ Top 10
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('best_day')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="best-day-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Summary Report -->
                    <div class="tab-pane fade" id="product-summary">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clipboard-list"></i> สรุปยอดขายสินค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> ดูว่าแต่ละสินค้าขายไปกี่ชิ้นแล้ว (เช่น กาแฟไปกี่แก้ว)
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('product_summary')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="product-summary-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Menu Count Report -->
                    <div class="tab-pane fade" id="monthly-menu-count">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-calendar-alt"></i> จำนวนเมนูที่ขายแต่ละเดือน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> หาว่าในแต่ละเดือนขายกี่เมนู (COUNT DISTINCT)
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('monthly_menu_count')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="monthly-menu-count-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Customers Report -->
                    <div class="tab-pane fade" id="staff-customers">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-user-friends"></i> พนักงานขายให้ลูกค้าคนไหนบ้าง</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> ดูว่าพนักงานแต่ละคนขายให้ลูกค้าใดบ้าง (Staff → Customer Mapping)
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('staff_customers')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-customers-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="js/reports.js"></script>
    <script>
        // Toggle category sections
        function toggleCategory(categoryName) {
            const content = document.getElementById(`category-${categoryName}`);
            const header = content.previousElementSibling;
            const toggleIcon = header.querySelector('.toggle-icon');

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                header.classList.remove('collapsed');
                toggleIcon.textContent = '⌄';
            } else {
                content.classList.add('collapsed');
                header.classList.add('collapsed');
                toggleIcon.textContent = '⌃';
            }
        }

        // Initialize categories on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show products category by default, others collapsed
            const categoriesList = ['orders', 'staff', 'advanced'];
            categoriesList.forEach(category => {
                const content = document.getElementById(`category-${category}`);
                const header = content.previousElementSibling;
                const toggleIcon = header.querySelector('.toggle-icon');

                content.classList.add('collapsed');
                header.classList.add('collapsed');
                toggleIcon.textContent = '⌃';
            });

            // Add click animation to nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    // Remove active class from all links
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');
                });
            });

            // Add category summary badges
            updateCategorySummaries();
        });

        // Update category summary information
        function updateCategorySummaries() {
            const categories = {
                'products': { count: 6, icon: 'fas fa-box-open', color: '#4f46e5' },
                'orders': { count: 8, icon: 'fas fa-shopping-cart', color: '#059669' },
                'staff': { count: 6, icon: 'fas fa-users-cog', color: '#ea580c' },
                'advanced': { count: 1, icon: 'fas fa-database', color: '#374151' }
            };

            Object.keys(categories).forEach(categoryName => {
                const header = document.querySelector(`#category-${categoryName}`).previousElementSibling;
                const category = categories[categoryName];

                // Add count badge to header if not exists
                if (!header.querySelector('.count-badge')) {
                    const countBadge = document.createElement('span');
                    countBadge.className = 'count-badge badge ms-2';
                    countBadge.style.backgroundColor = category.color;
                    countBadge.textContent = category.count;

                    const titleSpan = header.querySelector('h6 span:first-child');
                    titleSpan.appendChild(countBadge);
                }
            });
        }

        // Add search functionality for reports
        function searchReports(query) {
            const links = document.querySelectorAll('.nav-link');
            query = query.toLowerCase();

            links.forEach(link => {
                const text = link.textContent.toLowerCase();
                const parent = link.closest('.category-section');

                if (text.includes(query)) {
                    link.style.display = 'block';
                    // Show parent category
                    const content = parent.querySelector('.category-content');
                    const header = parent.querySelector('.category-header');
                    content.classList.remove('collapsed');
                    header.classList.remove('collapsed');
                    header.querySelector('.toggle-icon').textContent = '⌄';
                } else {
                    link.style.display = query ? 'none' : 'block';
                }
            });
        }
    </script>
</body>
</html>