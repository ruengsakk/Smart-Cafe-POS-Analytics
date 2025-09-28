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
        .sql-code {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 300px;
            overflow-y: auto;
        }
        .sql-dynamic {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border: 2px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
        }
        .sql-keyword {
            color: #1976d2;
            font-weight: bold;
        }
        .sql-function {
            color: #7b1fa2;
            font-weight: bold;
        }
        .sql-comment {
            color: #4caf50;
            font-style: italic;
        }
        .date-filter-highlight {
            background-color: #fff3e0;
            border: 1px solid #ff9800;
            padding: 5px;
            border-radius: 4px;
            font-weight: bold;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-coffee"></i> Coffee Shop POS</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="fas fa-cash-register"></i> POS System</a>
                <a class="nav-link active" href="reports.php"><i class="fas fa-chart-bar"></i> รายงาน</a>
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

                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> รายงานและ SQL</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="nav nav-pills flex-column" id="report-tabs">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#daily-sales">
                                <i class="fas fa-chart-line"></i> ยอดขายรายวัน
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#monthly-sales">
                                <i class="fas fa-calendar-alt"></i> ยอดขายรายเดือน
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#top-products">
                                <i class="fas fa-star"></i> สินค้าขายดี
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#customer-analysis">
                                <i class="fas fa-users"></i> วิเคราะห์ลูกค้า
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#staff-performance">
                                <i class="fas fa-user-tie"></i> ผลงานพนักงาน
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#payment-analysis">
                                <i class="fas fa-credit-card"></i> วิธีการชำระเงิน
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#hourly-analysis">
                                <i class="fas fa-clock"></i> วิเคราะห์ตามชั่วโมง
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#advanced-queries">
                                <i class="fas fa-database"></i> SQL ขั้นสูง
                            </button>
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="daily-sales-sql">
<span class="sql-comment">-- รายงานยอดขายรายวัน: แสดงสถิติการขายในแต่ละวัน</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-function">DATE</span>(order_date) <span class="sql-keyword">AS</span> วันที่,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย,
    <span class="sql-function">MIN</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายต่ำสุด,
    <span class="sql-function">MAX</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายสูงสุด
<span class="sql-keyword">FROM</span> orders
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="daily-sales-filter">order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span>
<span class="sql-keyword">GROUP BY</span> <span class="sql-function">DATE</span>(order_date)
<span class="sql-keyword">ORDER BY</span> order_date <span class="sql-keyword">DESC</span>;
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="monthly-sales-sql">
<span class="sql-comment">-- รายงานยอดขายรายเดือน: สรุปข้อมูลการขายในแต่ละเดือน</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-function">YEAR</span>(order_date) <span class="sql-keyword">AS</span> ปี,
    <span class="sql-function">MONTH</span>(order_date) <span class="sql-keyword">AS</span> เดือน,
    <span class="sql-function">MONTHNAME</span>(order_date) <span class="sql-keyword">AS</span> ชื่อเดือน,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย
<span class="sql-keyword">FROM</span> orders
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="monthly-sales-filter">1=1</span>
<span class="sql-keyword">GROUP BY</span> <span class="sql-function">YEAR</span>(order_date), <span class="sql-function">MONTH</span>(order_date)
<span class="sql-keyword">ORDER BY</span> ปี <span class="sql-keyword">DESC</span>, เดือน <span class="sql-keyword">DESC</span>;
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="top-products-sql">
<span class="sql-comment">-- รายงานสินค้าขายดี: JOIN ตาราง order_items, menus, categories และ orders</span>
<span class="sql-keyword">SELECT</span>
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">AS</span> จำนวนที่ขาย,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(oi.subtotal) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(oi.unit_price), 2) <span class="sql-keyword">AS</span> ราคาเฉลี่ย
<span class="sql-keyword">FROM</span> order_items oi
<span class="sql-keyword">JOIN</span> menus m <span class="sql-keyword">ON</span> oi.menu_id = m.id
<span class="sql-keyword">JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="top-products-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> m.id, m.name, c.name
<span class="sql-keyword">ORDER BY</span> จำนวนที่ขาย <span class="sql-keyword">DESC</span>
<span class="sql-keyword">LIMIT</span> 10;
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

                                <h6>SQL Query: <small class="text-muted">(ข้อมูลสะสมทั้งหมด - ไม่ขึ้นกับช่วงเวลา)</small></h6>
                                <div class="sql-dynamic" id="customer-analysis-sql">
<span class="sql-comment">-- วิเคราะห์ลูกค้า: ใช้ LEFT JOIN กับ Subquery และ CASE WHEN</span>
<span class="sql-keyword">SELECT</span>
    c.name <span class="sql-keyword">AS</span> ชื่อลูกค้า,
    c.phone <span class="sql-keyword">AS</span> เบอร์โทร,
    c.points <span class="sql-keyword">AS</span> แต้มสะสม,
    c.total_spent <span class="sql-keyword">AS</span> ยอดซื้อสะสม,
    c.visit_count <span class="sql-keyword">AS</span> จำนวนครั้งที่มา,
    <span class="sql-function">COALESCE</span>(recent_orders.last_order, 'ไม่เคยสั่ง') <span class="sql-keyword">AS</span> ออเดอร์ล่าสุด,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> c.total_spent >= 5000 <span class="sql-keyword">THEN</span> 'VIP'
        <span class="sql-keyword">WHEN</span> c.total_spent >= 2000 <span class="sql-keyword">THEN</span> 'Gold'
        <span class="sql-keyword">WHEN</span> c.total_spent >= 1000 <span class="sql-keyword">THEN</span> 'Silver'
        <span class="sql-keyword">ELSE</span> 'Bronze'
    <span class="sql-keyword">END AS</span> ระดับสมาชิก
<span class="sql-keyword">FROM</span> customers c
<span class="sql-keyword">LEFT JOIN</span> (
    <span class="sql-keyword">SELECT</span>
        customer_id,
        <span class="sql-function">MAX</span>(order_date) <span class="sql-keyword">AS</span> last_order
    <span class="sql-keyword">FROM</span> orders
    <span class="sql-keyword">WHERE</span> customer_id <span class="sql-keyword">IS NOT NULL</span>
    <span class="sql-keyword">GROUP BY</span> customer_id
) recent_orders <span class="sql-keyword">ON</span> c.id = recent_orders.customer_id
<span class="sql-keyword">WHERE</span> c.is_active = 1
<span class="sql-keyword">ORDER BY</span> c.total_spent <span class="sql-keyword">DESC</span>;
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="staff-performance-sql">
<span class="sql-comment">-- ผลงานพนักงาน: ใช้ INNER JOIN เพื่อหาพนักงานที่มีออเดอร์เท่านั้น</span>
<span class="sql-keyword">SELECT</span>
    s.name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    s.position <span class="sql-keyword">AS</span> ตำแหน่ง,
    <span class="sql-function">COUNT</span>(o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(o.total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(o.total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย,
    <span class="sql-function">ROUND</span>(<span class="sql-function">SUM</span>(o.total_amount) / <span class="sql-function">COUNT</span>(o.id), 2) <span class="sql-keyword">AS</span> ยอดขายต่อออเดอร์,
    <span class="sql-function">MIN</span>(o.order_date) <span class="sql-keyword">AS</span> วันแรกที่ขาย,
    <span class="sql-function">MAX</span>(o.order_date) <span class="sql-keyword">AS</span> วันล่าสุดที่ขาย
<span class="sql-keyword">FROM</span> staff s
<span class="sql-keyword">INNER JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="staff-performance-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position
<span class="sql-keyword">ORDER BY</span> ยอดขายรวม <span class="sql-keyword">DESC</span>;
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="payment-analysis-sql">
<span class="sql-comment">-- วิเคราะห์วิธีการชำระเงิน: การคำนวณเปอร์เซ็นต์ด้วย Subquery</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> payment_type = 'cash' <span class="sql-keyword">THEN</span> 'เงินสด'
        <span class="sql-keyword">WHEN</span> payment_type = 'qr' <span class="sql-keyword">THEN</span> 'QR Code'
        <span class="sql-keyword">WHEN</span> payment_type = 'online' <span class="sql-keyword">THEN</span> 'Online Payment'
        <span class="sql-keyword">ELSE</span> payment_type
    <span class="sql-keyword">END AS</span> วิธีการชำระเงิน,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย
<span class="sql-keyword">FROM</span> orders
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="payment-analysis-filter">order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> payment_type
<span class="sql-keyword">ORDER BY</span> จำนวนออเดอร์ <span class="sql-keyword">DESC</span>;
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

                                <h6>SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                <div class="sql-dynamic" id="hourly-analysis-sql">
<span class="sql-comment">-- วิเคราะห์ตามชั่วโมง: ใช้ HOUR() function และ CASE WHEN สำหรับจัดกลุ่ม</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">AS</span> ชั่วโมง,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">BETWEEN</span> 6 <span class="sql-keyword">AND</span> 10 <span class="sql-keyword">THEN</span> 'ช่วงเช้า'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">BETWEEN</span> 11 <span class="sql-keyword">AND</span> 14 <span class="sql-keyword">THEN</span> 'ช่วงเที่ยง'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">BETWEEN</span> 15 <span class="sql-keyword">AND</span> 18 <span class="sql-keyword">THEN</span> 'ช่วงบ่าย'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">BETWEEN</span> 19 <span class="sql-keyword">AND</span> 22 <span class="sql-keyword">THEN</span> 'ช่วงเย็น'
        <span class="sql-keyword">ELSE</span> 'ช่วงพิเศษ'
    <span class="sql-keyword">END AS</span> ช่วงเวลา,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย
<span class="sql-keyword">FROM</span> orders
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="hourly-analysis-filter">order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span>
<span class="sql-keyword">GROUP BY</span> <span class="sql-function">HOUR</span>(order_time)
<span class="sql-keyword">ORDER BY</span> ชั่วโมง;
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('hourly_analysis')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="hourly-analysis-result" class="mt-3"></div>
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

                                <h6>ตัวอย่าง 1: ลูกค้าที่ซื้อมากกว่าค่าเฉลี่ย (Subquery)</h6>
                                <div class="sql-dynamic">
<span class="sql-comment">-- การใช้ Subquery เพื่อเปรียบเทียบกับค่าเฉลี่ย</span>
<span class="sql-keyword">SELECT</span>
    c.name <span class="sql-keyword">AS</span> ชื่อลูกค้า,
    c.total_spent <span class="sql-keyword">AS</span> ยอดซื้อสะสม,
    (<span class="sql-keyword">SELECT</span> <span class="sql-function">AVG</span>(total_spent) <span class="sql-keyword">FROM</span> customers <span class="sql-keyword">WHERE</span> is_active = 1) <span class="sql-keyword">AS</span> ค่าเฉลี่ย,
    <span class="sql-function">ROUND</span>(c.total_spent - (<span class="sql-keyword">SELECT</span> <span class="sql-function">AVG</span>(total_spent) <span class="sql-keyword">FROM</span> customers <span class="sql-keyword">WHERE</span> is_active = 1), 2) <span class="sql-keyword">AS</span> ส่วนต่าง
<span class="sql-keyword">FROM</span> customers c
<span class="sql-keyword">WHERE</span> c.is_active = 1
    <span class="sql-keyword">AND</span> c.total_spent > (<span class="sql-keyword">SELECT</span> <span class="sql-function">AVG</span>(total_spent) <span class="sql-keyword">FROM</span> customers <span class="sql-keyword">WHERE</span> is_active = 1)
<span class="sql-keyword">ORDER BY</span> c.total_spent <span class="sql-keyword">DESC</span>;
                                </div>

                                <h6>ตัวอย่าง 2: อันดับสินค้าขายดีในแต่ละหมวดหมู่ (Window Function)</h6>
                                <div class="sql-dynamic">
<span class="sql-comment">-- การใช้ Window Function RANK() OVER() เพื่อจัดอันดับ</span>
<span class="sql-keyword">SELECT</span>
    category_name,
    menu_name,
    total_sold,
    category_rank
<span class="sql-keyword">FROM</span> (
    <span class="sql-keyword">SELECT</span>
        c.name <span class="sql-keyword">AS</span> category_name,
        m.name <span class="sql-keyword">AS</span> menu_name,
        <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">AS</span> total_sold,
        <span class="sql-function">RANK</span>() <span class="sql-keyword">OVER</span> (<span class="sql-keyword">PARTITION BY</span> c.id <span class="sql-keyword">ORDER BY</span> <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">DESC</span>) <span class="sql-keyword">AS</span> category_rank
    <span class="sql-keyword">FROM</span> order_items oi
    <span class="sql-keyword">JOIN</span> menus m <span class="sql-keyword">ON</span> oi.menu_id = m.id
    <span class="sql-keyword">JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
    <span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id
    <span class="sql-keyword">WHERE</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
    <span class="sql-keyword">GROUP BY</span> c.id, c.name, m.id, m.name
) ranked_products
<span class="sql-keyword">WHERE</span> category_rank <= 3
<span class="sql-keyword">ORDER BY</span> category_name, category_rank;
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('advanced_queries')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="advanced-queries-result" class="mt-3"></div>
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
</body>
</html>