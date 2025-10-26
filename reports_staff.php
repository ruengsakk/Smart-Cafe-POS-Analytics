<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานพนักงาน - Coffee Shop Analytics</title>
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
        .page-header {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .btn-coffee {
            background: linear-gradient(135deg, #ea580c, #f97316);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
        }
        .btn-coffee:hover {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            transform: translateY(-2px);
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .report-tabs {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .nav-pills .nav-link {
            color: #ea580c;
            border-radius: 10px;
            padding: 12px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .nav-pills .nav-link:hover {
            background-color: #fed7aa;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
        }
        .date-filter {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
                            <li><a class="dropdown-item active" href="reports_staff.php"><i class="fas fa-users-cog"></i> รายงานพนักงาน</a></li>
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

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-users-cog"></i> รายงานพนักงาน</h2>
            <p class="mb-0">วิเคราะห์ผลงาน ประสิทธิภาพ และเปรียบเทียบพนักงาน</p>
        </div>

        <!-- Date Filter -->
        <div class="date-filter">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> ตั้งแต่วันที่</label>
                    <input type="date" class="form-control" id="startDate" onchange="refreshCurrentReport()">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> ถึงวันที่</label>
                    <input type="date" class="form-control" id="endDate" onchange="refreshCurrentReport()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ช่วงเวลาที่กำหนดไว้</label>
                    <select class="form-select" id="predefinedRange" onchange="setPredefinedRange()">
                        <option value="">กำหนดเอง</option>
                        <option value="today">วันนี้</option>
                        <option value="yesterday">เมื่อวาน</option>
                        <option value="last7days" selected>7 วันล่าสุด</option>
                        <option value="last30days">30 วันล่าสุด</option>
                        <option value="thismonth">เดือนนี้</option>
                        <option value="lastmonth">เดือนที่แล้ว</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-coffee w-100" onclick="refreshCurrentReport()">
                        <i class="fas fa-sync-alt"></i> อัปเดต
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="date-filter">
            <h6 class="mb-3"><i class="fas fa-filter"></i> ตัวกรองเพิ่มเติม</h6>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-user-tie"></i> ชื่อพนักงาน</label>
                    <input type="text" class="form-control" id="filterStaffName" placeholder="พิมพ์เพื่อค้นหา... (เช่น สมชาย, นิดา)" onchange="refreshCurrentReport()">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-calendar-day"></i> เดือน</label>
                    <select class="form-select" id="filterMonth" onchange="refreshCurrentReport()">
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
                </div>
            </div>
        </div>

        <!-- Report Tabs -->
        <div class="report-tabs">
            <ul class="nav nav-pills nav-fill" id="staffReportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="staff-ranking-tab" data-bs-toggle="pill" data-bs-target="#staff-ranking" type="button">
                        <i class="fas fa-trophy"></i> อันดับพนักงาน
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-performance-tab" data-bs-toggle="pill" data-bs-target="#staff-performance" type="button">
                        <i class="fas fa-chart-line"></i> ผลงานพนักงาน
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-products-tab" data-bs-toggle="pill" data-bs-target="#staff-products" type="button">
                        <i class="fas fa-box-open"></i> สินค้าที่ขาย
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-orders-tab" data-bs-toggle="pill" data-bs-target="#staff-orders" type="button">
                        <i class="fas fa-shopping-cart"></i> ออเดอร์ต่อพนักงาน
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-efficiency-tab" data-bs-toggle="pill" data-bs-target="#staff-efficiency" type="button">
                        <i class="fas fa-tachometer-alt"></i> ประสิทธิภาพ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-comparison-tab" data-bs-toggle="pill" data-bs-target="#staff-comparison" type="button">
                        <i class="fas fa-balance-scale"></i> เปรียบเทียบ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-customers-tab" data-bs-toggle="pill" data-bs-target="#staff-customers" type="button">
                        <i class="fas fa-users"></i> ลูกค้าต่อพนักงาน
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="advanced-queries-tab" data-bs-toggle="pill" data-bs-target="#advanced-queries" type="button">
                        <i class="fas fa-database"></i> รายงานขั้นสูง
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="staffReportContent">
            <!-- Staff Ranking -->
            <div class="tab-pane fade show active" id="staff-ranking" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy"></i> อันดับพนักงานยอดเยี่ยม</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-ranking-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Performance -->
            <div class="tab-pane fade" id="staff-performance" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> ผลงานพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-performance-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Products -->
            <div class="tab-pane fade" id="staff-products" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-box-open"></i> สินค้าที่พนักงานขาย</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-products-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Orders -->
            <div class="tab-pane fade" id="staff-orders" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-cart"></i> ออเดอร์ต่อพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-orders-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Efficiency -->
            <div class="tab-pane fade" id="staff-efficiency" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tachometer-alt"></i> ประสิทธิภาพพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-efficiency-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Comparison -->
            <div class="tab-pane fade" id="staff-comparison" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-balance-scale"></i> เปรียบเทียบพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-comparison-result"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Customers -->
            <div class="tab-pane fade" id="staff-customers" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users"></i> ลูกค้าต่อพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <div id="staff-customers-result"></div>
                    </div>
                </div>
            </div>

            <!-- Advanced Queries -->
            <div class="tab-pane fade" id="advanced-queries" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-database"></i> รายงานขั้นสูง</h5>
                    </div>
                    <div class="card-body">
                        <div id="advanced-queries-result"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/simple_reports.js"></script>
    <script>
        function setPredefinedRange() {
            const range = document.getElementById('predefinedRange').value;
            const today = new Date();
            let startDate = new Date();
            let endDate = new Date();

            switch(range) {
                case 'today':
                    startDate = today;
                    endDate = today;
                    break;
                case 'yesterday':
                    startDate.setDate(today.getDate() - 1);
                    endDate.setDate(today.getDate() - 1);
                    break;
                case 'last7days':
                    startDate.setDate(today.getDate() - 7);
                    break;
                case 'last30days':
                    startDate.setDate(today.getDate() - 30);
                    break;
                case 'thismonth':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
                case 'lastmonth':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
            }

            if (range) {
                document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
                document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
                refreshCurrentReport();
            }
        }

        // Auto load report when tab changes
        document.querySelectorAll('#staffReportTabs button[data-bs-toggle="pill"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const targetId = e.target.getAttribute('data-bs-target');
                const reportType = e.target.id.replace('-tab', '');
                const resultDiv = targetId.replace('#', '') + '-result';

                // Map tab IDs to report types
                const reportMap = {
                    'staff-ranking': 'staff_ranking',
                    'staff-performance': 'staff_performance',
                    'staff-products': 'staff_products',
                    'staff-orders': 'staff_orders',
                    'staff-efficiency': 'staff_efficiency',
                    'staff-comparison': 'staff_comparison',
                    'staff-customers': 'staff_customers',
                    'advanced-queries': 'advanced_queries'
                };

                loadReport(reportMap[reportType], resultDiv);
            });
        });

        // Initialize with last 7 days and load first report
        document.addEventListener('DOMContentLoaded', function() {
            setPredefinedRange();
            // Auto load first report
            loadReport('staff_ranking', 'staff-ranking-result');
        });

        // Refresh current active report
        function refreshCurrentReport() {
            const activeTab = document.querySelector('#staffReportTabs .nav-link.active');
            if (activeTab) {
                const event = new Event('shown.bs.tab');
                activeTab.dispatchEvent(event);
            }
        }

    </script>
</body>
</html>
