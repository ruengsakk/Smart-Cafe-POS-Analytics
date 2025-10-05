<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานและการศึกษา SQL - Coffee Shop Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        /* Add overlay pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.03) 35px, rgba(255,255,255,.03) 70px);
            pointer-events: none;
            z-index: 0;
        }

        .container-fluid {
            position: relative;
            z-index: 1;
        }
        .navbar {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 3px solid #dee2e6;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            font-weight: 600;
        }

        .card-header h4, .card-header h5, .card-header h6 {
            margin: 0;
            color: #2d3748;
        }
        /* SQL Code Box Styling */
        .sql-code-container {
            position: relative;
            margin: 15px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .sql-code-container:hover {
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        }

        .sql-code-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #4299e1;
        }

        .sql-code-header h6 {
            color: #fff;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sql-code-header h6 i {
            color: #4299e1;
        }

        .sql-code-actions {
            display: flex;
            gap: 8px;
        }

        .btn-copy-sql {
            background: rgba(66, 153, 225, 0.2);
            border: 1px solid #4299e1;
            color: #4299e1;
            padding: 5px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .btn-copy-sql:hover {
            background: #4299e1;
            color: white;
            transform: translateY(-2px);
        }

        .btn-copy-sql.copied {
            background: #48bb78;
            border-color: #48bb78;
            color: white;
        }

        .btn-toggle-sql {
            background: rgba(237, 137, 54, 0.2);
            border: 1px solid #ed8936;
            color: #ed8936;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-toggle-sql:hover {
            background: #ed8936;
            color: white;
        }

        .sql-code {
            background: #1e1e1e;
            border: none;
            border-radius: 0;
            padding: 20px;
            margin: 0;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 400px;
            overflow-y: auto;
            color: #d4d4d4;
            line-height: 1.6;
            position: relative;
            counter-reset: line;
        }

        .sql-code::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .sql-code::-webkit-scrollbar-track {
            background: #2d2d2d;
        }

        .sql-code::-webkit-scrollbar-thumb {
            background: #4299e1;
            border-radius: 5px;
        }

        .sql-code::-webkit-scrollbar-thumb:hover {
            background: #3182ce;
        }

        /* Line numbers */
        .sql-code-with-lines {
            display: table;
            width: 100%;
        }

        .sql-code-with-lines > div {
            display: table-row;
        }

        .sql-code-with-lines > div::before {
            content: counter(line);
            counter-increment: line;
            display: table-cell;
            text-align: right;
            padding-right: 15px;
            padding-left: 5px;
            color: #6b7280;
            border-right: 2px solid #374151;
            margin-right: 15px;
            user-select: none;
            width: 40px;
            font-size: 0.85em;
        }

        .sql-dynamic {
            background: #1e1e1e;
            border: 2px solid #4299e1;
            padding: 20px;
            border-radius: 0 0 12px 12px;
            margin: 0;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            color: #d4d4d4;
            line-height: 1.7;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);
        }

        .sql-dynamic::-webkit-scrollbar {
            width: 10px;
        }

        .sql-dynamic::-webkit-scrollbar-track {
            background: #2d2d2d;
        }

        .sql-dynamic::-webkit-scrollbar-thumb {
            background: #4299e1;
            border-radius: 5px;
        }

        .sql-keyword {
            color: #569cd6;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(86, 156, 214, 0.3);
        }

        .sql-function {
            color: #c586c0;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(197, 134, 192, 0.3);
        }

        .sql-comment {
            color: #6a9955;
            font-style: italic;
        }

        .sql-string {
            color: #ce9178;
        }

        .sql-number {
            color: #b5cea8;
        }

        .sql-collapsed {
            display: none;
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
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-coffee::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-coffee:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-coffee:hover {
            background: linear-gradient(135deg, #D2691E, #8B4513);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
        }

        .btn-coffee:active {
            transform: translateY(0);
        }

        .btn-coffee i {
            margin-right: 8px;
        }

        /* Loading State */
        .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .highlight-sql {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 5px solid #f59e0b;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 15px 0;
            box-shadow: 0 3px 10px rgba(245, 158, 11, 0.2);
            animation: pulse 2s ease-in-out infinite;
        }

        .highlight-sql strong {
            color: #92400e;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .highlight-sql strong::before {
            content: '🎯';
            font-size: 1.2rem;
        }

        /* Educational Badge */
        .learning-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(102, 126, 234, 0.3);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 3px 10px rgba(245, 158, 11, 0.2);
            }
            50% {
                box-shadow: 0 5px 20px rgba(245, 158, 11, 0.3);
            }
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

        /* Table Improvements */
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .table thead th {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px 12px;
            border: none;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f7fafc;
            transform: scale(1.01);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Navbar improvements */
        .navbar {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
            border-radius: 8px;
            padding: 8px 16px !important;
        }

        .navbar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .navbar .nav-link.active {
            background: rgba(255,255,255,0.3);
            font-weight: 600;
        }

        /* Form controls */
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            transition: all 0.3s ease;
            font-family: 'Kanit', sans-serif;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }

            .card {
                margin-bottom: 15px;
            }

            .card-header h4, .card-header h5 {
                font-size: 1.2rem;
            }

            .sql-code-header {
                flex-direction: column;
                gap: 10px;
            }

            .sql-code-actions {
                width: 100%;
                justify-content: space-between;
            }

            .btn-copy-sql, .btn-toggle-sql {
                flex: 1;
                justify-content: center;
            }

            .sql-dynamic {
                font-size: 0.8em;
                padding: 15px;
            }

            .highlight-sql {
                padding: 12px 15px;
            }

            .category-header h6 {
                font-size: 1rem;
            }

            .nav-pills .nav-link {
                font-size: 0.85rem;
                padding: 8px 12px;
            }

            .table {
                font-size: 0.85rem;
            }

            .table thead th {
                font-size: 0.75rem;
                padding: 10px 8px;
            }

            .table tbody td {
                padding: 8px;
            }

            .btn-coffee {
                width: 100%;
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.1rem;
            }

            .sql-code-header h6 {
                font-size: 0.85rem;
            }

            .btn-copy-sql, .btn-toggle-sql {
                font-size: 0.75rem;
                padding: 4px 10px;
            }
        }

        /* Loading overlay */
        .report-loading {
            position: relative;
        }

        .report-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.4);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.5);
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
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php"><i class="fas fa-chart-bar"></i> รายงาน</a>
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
                    <div class="tab-pane fade" id="daily-sales">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-line"></i> รายงานยอดขายรายวัน</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ GROUP BY, DATE functions, และ Aggregate functions
                                </div>

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('daily-sales-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('daily-sales-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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
                                </div>

                                <button class="btn btn-coffee mt-3" onclick="loadReport('daily_sales')">
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('monthly-sales-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('monthly-sales-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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
                                </div>

                                <button class="btn btn-coffee" onclick="loadReport('monthly_sales')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>

                                <div id="monthly-sales-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products Report -->
                    <div class="tab-pane fade show active" id="top-products">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-star"></i> รายงานสินค้าขายดี</h4>
                            </div>
                            <div class="card-body">
                                <div class="highlight-sql">
                                    <strong>เป้าหมายการเรียนรู้:</strong> การใช้ JOIN, SUM(), COUNT() และ ORDER BY
                                </div>

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('top-products-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('top-products-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(ข้อมูลสะสมทั้งหมด - ไม่ขึ้นกับช่วงเวลา)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('customer-analysis-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('customer-analysis-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-performance-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-performance-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('payment-analysis-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('payment-analysis-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('hourly-analysis-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('hourly-analysis-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('product-inventory-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('product-inventory-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="product-inventory-sql">
<span class="sql-comment">-- รายงานสต็อกสินค้า: วิเคราะห์สินค้าที่ขายดีและคงเหลือ</span>
<span class="sql-keyword">SELECT</span>
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    m.price <span class="sql-keyword">AS</span> ราคา,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <span class="sql-keyword">AS</span> จำนวนที่ขาย,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> <span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 7 <span class="sql-keyword">DAY</span>) <span class="sql-keyword">THEN</span> o.id <span class="sql-keyword">END</span>) <span class="sql-keyword">AS</span> ออเดอร์_7วัน,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) = 0 <span class="sql-keyword">THEN</span> 'ไม่มีการขาย'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> <span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 7 <span class="sql-keyword">DAY</span>) <span class="sql-keyword">THEN</span> o.id <span class="sql-keyword">END</span>) = 0 <span class="sql-keyword">THEN</span> 'สินค้าค้าง'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) >= 100 <span class="sql-keyword">THEN</span> 'ขายดีมาก'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) >= 50 <span class="sql-keyword">THEN</span> 'ขายดีปานกลาง'
        <span class="sql-keyword">ELSE</span> 'ขายน้อย'
    <span class="sql-keyword">END AS</span> สถานะสินค้า
<span class="sql-keyword">FROM</span> menus m
<span class="sql-keyword">LEFT JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">LEFT JOIN</span> order_items oi <span class="sql-keyword">ON</span> m.id = oi.menu_id
<span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id
<span class="sql-keyword">WHERE</span> m.is_active = 1
<span class="sql-keyword">GROUP BY</span> m.id, m.name, c.name, m.price
<span class="sql-keyword">ORDER BY</span> จำนวนที่ขาย <span class="sql-keyword">DESC</span>;
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('order-patterns-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('order-patterns-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="order-patterns-sql">
<span class="sql-comment">-- รูปแบบการสั่งซื้อ: วิเคราะห์ขนาดออเดอร์และพฤติกรรมลูกค้า</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> total_amount < 100 <span class="sql-keyword">THEN</span> 'น้อยกว่า 100 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 200 <span class="sql-keyword">THEN</span> '100-199 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 500 <span class="sql-keyword">THEN</span> '200-499 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 1000 <span class="sql-keyword">THEN</span> '500-999 บาท'
        <span class="sql-keyword">ELSE</span> '1000+ บาท'
    <span class="sql-keyword">END AS</span> ช่วงยอดขาย,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>((<span class="sql-function">COUNT</span>(*) * 100.0 / (<span class="sql-keyword">SELECT</span> <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">FROM</span> orders <span class="sql-keyword">WHERE</span> order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 30 <span class="sql-keyword">DAY</span>))), 2) <span class="sql-keyword">AS</span> เปอร์เซ็นต์,
    <span class="sql-function">AVG</span>(items_count.item_count) <span class="sql-keyword">AS</span> จำนวนสินค้าเฉลี่ย
<span class="sql-keyword">FROM</span> orders o
<span class="sql-keyword">JOIN</span> (
    <span class="sql-keyword">SELECT</span> order_id, <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> item_count
    <span class="sql-keyword">FROM</span> order_items
    <span class="sql-keyword">GROUP BY</span> order_id
) items_count <span class="sql-keyword">ON</span> o.id = items_count.order_id
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="order-patterns-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span>
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> total_amount < 100 <span class="sql-keyword">THEN</span> 'น้อยกว่า 100 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 200 <span class="sql-keyword">THEN</span> '100-199 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 500 <span class="sql-keyword">THEN</span> '200-499 บาท'
        <span class="sql-keyword">WHEN</span> total_amount < 1000 <span class="sql-keyword">THEN</span> '500-999 บาท'
        <span class="sql-keyword">ELSE</span> '1000+ บาท'
    <span class="sql-keyword">END</span>
<span class="sql-keyword">ORDER BY</span> <span class="sql-function">MIN</span>(total_amount);
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-ranking-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-ranking-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="staff-ranking-sql">
<span class="sql-comment">-- อันดับพนักงาน: ใช้ RANK() และ ROW_NUMBER() Window Functions</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-function">RANK</span>() <span class="sql-keyword">OVER</span>(<span class="sql-keyword">ORDER BY</span> total_sales <span class="sql-keyword">DESC</span>) <span class="sql-keyword">AS</span> อันดับ,
    staff_name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    position <span class="sql-keyword">AS</span> ตำแหน่ง,
    total_orders <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    total_sales <span class="sql-keyword">AS</span> ยอดขายรวม,
    avg_order_value <span class="sql-keyword">AS</span> ยอดขายเฉลี่ยต่อออเดอร์,
    sales_vs_target <span class="sql-keyword">AS</span> เปรียบเทียบกับเป้าหมาย,
    performance_rating <span class="sql-keyword">AS</span> ระดับผลงาน,
    วันที่เริ่มงาน,
    วันล่าสุดขาย
<span class="sql-keyword">FROM</span> (
    <span class="sql-keyword">SELECT</span>
        s.name <span class="sql-keyword">AS</span> staff_name,
        s.position,
        <span class="sql-function">COUNT</span>(o.id) <span class="sql-keyword">AS</span> total_orders,
        <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(o.total_amount), 0) <span class="sql-keyword">AS</span> total_sales,
        <span class="sql-function">ROUND</span>(<span class="sql-function">COALESCE</span>(<span class="sql-function">AVG</span>(o.total_amount), 0), 2) <span class="sql-keyword">AS</span> avg_order_value,
        <span class="sql-function">CONCAT</span>(<span class="sql-function">ROUND</span>((<span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(o.total_amount), 0) / 10000) * 100, 1), '%') <span class="sql-keyword">AS</span> sales_vs_target,
        <span class="sql-keyword">CASE</span>
            <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(o.total_amount), 0) >= 15000 <span class="sql-keyword">THEN</span> 'ดีเยี่ยม 🏆'
            <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(o.total_amount), 0) >= 10000 <span class="sql-keyword">THEN</span> 'ดี 🌟'
            <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(o.total_amount), 0) >= 5000 <span class="sql-keyword">THEN</span> 'ปานกลาง 💪'
            <span class="sql-keyword">ELSE</span> 'ต้องพัฒนา 🚀'
        <span class="sql-keyword">END AS</span> performance_rating,
        <span class="sql-function">DATE</span>(<span class="sql-function">MIN</span>(o.order_date)) <span class="sql-keyword">AS</span> วันที่เริ่มงาน,
        <span class="sql-function">DATE</span>(<span class="sql-function">MAX</span>(o.order_date)) <span class="sql-keyword">AS</span> วันล่าสุดขาย
    <span class="sql-keyword">FROM</span> staff s
    <span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
        <span class="sql-keyword">AND</span> <span class="date-filter-highlight" id="staff-ranking-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
    <span class="sql-keyword">WHERE</span> s.is_active = 1
    <span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position
) staff_stats
<span class="sql-keyword">ORDER BY</span> total_sales <span class="sql-keyword">DESC</span>;
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('product-comparison-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('product-comparison-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="product-comparison-sql">
<span class="sql-comment">-- เปรียบเทียบสินค้า: ใช้ PIVOT-like query เพื่อเปรียบเทียบหมวดหมู่</span>
<span class="sql-keyword">SELECT</span>
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> m.id) <span class="sql-keyword">AS</span> จำนวนสินค้า,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <span class="sql-keyword">AS</span> จำนวนที่ขายรวม,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">COALESCE</span>(<span class="sql-function">AVG</span>(oi.unit_price), 0), 2) <span class="sql-keyword">AS</span> ราคาเฉลี่ย,
    <span class="sql-function">ROUND</span>(
        (<span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) * 100.0) /
        <span class="sql-function">NULLIF</span>((<span class="sql-keyword">SELECT</span> <span class="sql-function">SUM</span>(subtotal) <span class="sql-keyword">FROM</span> order_items oi2
                    <span class="sql-keyword">JOIN</span> orders o2 <span class="sql-keyword">ON</span> oi2.order_id = o2.id
                    <span class="sql-keyword">WHERE</span> o2.order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 30 <span class="sql-keyword">DAY</span>)), 0), 2
    ) <span class="sql-keyword">AS</span> สัดส่วนยอดขาย,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">ROUND</span>(
        <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) /
        <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-keyword">DISTINCT</span> o.id), 0), 2
    ) <span class="sql-keyword">AS</span> จำนวนต่อออเดอร์,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) = 0 <span class="sql-keyword">THEN</span> 'ไม่มีการขาย'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) >= 5000 <span class="sql-keyword">THEN</span> 'หมวดหมู่ยอดนิยม 🔥'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) >= 2000 <span class="sql-keyword">THEN</span> 'หมวดหมู่ขายดี ⭐'
        <span class="sql-keyword">ELSE</span> 'หมวดหมู่ขายช้า 📊'
    <span class="sql-keyword">END AS</span> สถานะหมวดหมู่
<span class="sql-keyword">FROM</span> categories c
<span class="sql-keyword">LEFT JOIN</span> menus m <span class="sql-keyword">ON</span> c.id = m.category_id <span class="sql-keyword">AND</span> m.is_active = 1
<span class="sql-keyword">LEFT JOIN</span> order_items oi <span class="sql-keyword">ON</span> m.id = oi.menu_id
<span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id
    <span class="sql-keyword">AND</span> <span class="date-filter-highlight" id="product-comparison-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> c.id, c.name
<span class="sql-keyword">ORDER BY</span> ยอดขายรวม <span class="sql-keyword">DESC</span>;
                                    </div>
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

                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('order-size-analysis-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('order-size-analysis-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="order-size-analysis-sql">
<span class="sql-comment">-- วิเคราะห์ขนาดออเดอร์: การจัดกลุ่มตามจำนวนสินค้าและยอดขาย</span>
<span class="sql-keyword">SELECT</span>
    order_size_category <span class="sql-keyword">AS</span> ประเภทขนาดออเดอร์,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_items), 1) <span class="sql-keyword">AS</span> จำนวนสินค้าเฉลี่ย,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>((<span class="sql-function">COUNT</span>(*) * 100.0 /
        (<span class="sql-keyword">SELECT</span> <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">FROM</span> orders <span class="sql-keyword">WHERE</span> order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 30 <span class="sql-keyword">DAY</span>))), 2) <span class="sql-keyword">AS</span> เปอร์เซ็นต์ออเดอร์,
    <span class="sql-function">ROUND</span>((<span class="sql-function">SUM</span>(total_amount) * 100.0 /
        (<span class="sql-keyword">SELECT</span> <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">FROM</span> orders <span class="sql-keyword">WHERE</span> order_date >= <span class="sql-function">DATE_SUB</span>(<span class="sql-function">CURDATE</span>(), <span class="sql-keyword">INTERVAL</span> 30 <span class="sql-keyword">DAY</span>))), 2) <span class="sql-keyword">AS</span> เปอร์เซ็นต์ยอดขาย,
    <span class="sql-function">MIN</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายต่ำสุด,
    <span class="sql-function">MAX</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายสูงสุด
<span class="sql-keyword">FROM</span> (
    <span class="sql-keyword">SELECT</span>
        o.id,
        o.total_amount,
        <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">AS</span> total_items,
        <span class="sql-keyword">CASE</span>
            <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) = 1 <span class="sql-keyword">THEN</span> 'ออเดอร์เดี่ยว (1 ชิ้น)'
            <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) <= 3 <span class="sql-keyword">THEN</span> 'ออเดอร์เล็ก (2-3 ชิ้น)'
            <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) <= 5 <span class="sql-keyword">THEN</span> 'ออเดอร์ปานกลาง (4-5 ชิ้น)'
            <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) <= 10 <span class="sql-keyword">THEN</span> 'ออเดอร์ใหญ่ (6-10 ชิ้น)'
            <span class="sql-keyword">ELSE</span> 'ออเดอร์รายใหญ่ (10+ ชิ้น)'
        <span class="sql-keyword">END AS</span> order_size_category
    <span class="sql-keyword">FROM</span> orders o
    <span class="sql-keyword">JOIN</span> order_items oi <span class="sql-keyword">ON</span> o.id = oi.order_id
    <span class="sql-keyword">WHERE</span> <span class="date-filter-highlight" id="order-size-analysis-filter">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
    <span class="sql-keyword">GROUP BY</span> o.id, o.total_amount
) order_analysis
<span class="sql-keyword">GROUP BY</span> order_size_category
<span class="sql-keyword">ORDER BY</span>
    <span class="sql-keyword">CASE</span> order_size_category
        <span class="sql-keyword">WHEN</span> 'ออเดอร์เดี่ยว (1 ชิ้น)' <span class="sql-keyword">THEN</span> 1
        <span class="sql-keyword">WHEN</span> 'ออเดอร์เล็ก (2-3 ชิ้น)' <span class="sql-keyword">THEN</span> 2
        <span class="sql-keyword">WHEN</span> 'ออเดอร์ปานกลาง (4-5 ชิ้น)' <span class="sql-keyword">THEN</span> 3
        <span class="sql-keyword">WHEN</span> 'ออเดอร์ใหญ่ (6-10 ชิ้น)' <span class="sql-keyword">THEN</span> 4
        <span class="sql-keyword">ELSE</span> 5
    <span class="sql-keyword">END</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('product-performance-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('product-performance-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="product-performance-sql">
<span class="sql-comment">-- ประสิทธิภาพสินค้า: วิเคราะห์อัตราการหมุนเวียนและผลตอบแทน</span>
<span class="sql-keyword">SELECT</span>
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    m.price <span class="sql-keyword">AS</span> ราคา,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <span class="sql-keyword">AS</span> จำนวนที่ขาย,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) / <span class="sql-function">NULLIF</span>(<span class="sql-function">SUM</span>(oi.quantity), 0), 2) <span class="sql-keyword">AS</span> ราคาเฉลี่ยที่ขายได้,
    <span class="sql-function">ROUND</span>(<span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) / <span class="sql-function">NULLIF</span>(<span class="sql-function">DATEDIFF</span>(<span class="date-filter-highlight">CURDATE(), DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>), 0), 2) <span class="sql-keyword">AS</span> อัตราการขายต่อวัน
<span class="sql-keyword">FROM</span> menus m
<span class="sql-keyword">LEFT JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">LEFT JOIN</span> order_items oi <span class="sql-keyword">ON</span> m.id = oi.menu_id
<span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">WHERE</span> m.is_active = 1
<span class="sql-keyword">GROUP BY</span> m.id, m.name, c.name, m.price
<span class="sql-keyword">ORDER BY</span> ยอดขายรวม <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('product-trends-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('product-trends-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="product-trends-sql">
<span class="sql-comment">-- เทรนด์สินค้า: เปรียบเทียบการขายระหว่างช่วงเวลา</span>
<span class="sql-keyword">SELECT</span>
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) <span class="sql-keyword">AS</span> ขาย_7วันล่าสุด,
    <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date < <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">AND</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 14 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) <span class="sql-keyword">AS</span> ขาย_7วันก่อน,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date < <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">AND</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 14 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) = 0 <span class="sql-keyword">THEN</span> 'สินค้าใหม่/ไม่มีข้อมูล'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) > <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date < <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">AND</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 14 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) <span class="sql-keyword">THEN</span> '📈 เพิ่มขึ้น'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) < <span class="sql-function">SUM</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.order_date < <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 7 DAY)</span> <span class="sql-keyword">AND</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 14 DAY)</span> <span class="sql-keyword">THEN</span> oi.quantity <span class="sql-keyword">ELSE</span> 0 <span class="sql-keyword">END</span>) <span class="sql-keyword">THEN</span> '📉 ลดลง'
        <span class="sql-keyword">ELSE</span> '➡️ คงเดิม'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> เทรนด์
<span class="sql-keyword">FROM</span> menus m
<span class="sql-keyword">LEFT JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">LEFT JOIN</span> order_items oi <span class="sql-keyword">ON</span> m.id = oi.menu_id
<span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id <span class="sql-keyword">AND</span> o.order_date >= <span class="date-filter-highlight">DATE_SUB(CURDATE(), INTERVAL 14 DAY)</span>
<span class="sql-keyword">WHERE</span> m.is_active = 1
<span class="sql-keyword">GROUP BY</span> m.id, m.name, c.name
<span class="sql-keyword">HAVING</span> (ขาย_7วันล่าสุด + ขาย_7วันก่อน) > 0
<span class="sql-keyword">ORDER BY</span> ขาย_7วันล่าสุด <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('slow-moving-products-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('slow-moving-products-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="slow-moving-products-sql">
<span class="sql-comment">-- สินค้าขายช้า: สินค้าที่ขายน้อยหรือไม่มีการขายเลย</span>
<span class="sql-keyword">SELECT</span>
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    m.price <span class="sql-keyword">AS</span> ราคา,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <span class="sql-keyword">AS</span> จำนวนที่ขาย,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.subtotal), 0) <span class="sql-keyword">AS</span> ยอดขาย,
    <span class="sql-function">COALESCE</span>(<span class="sql-function">MAX</span>(o.order_date), 'ไม่เคยขาย') <span class="sql-keyword">AS</span> ขายครั้งล่าสุด,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) = 0 <span class="sql-keyword">THEN</span> '🔴 ไม่เคยขาย'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <= 2 <span class="sql-keyword">THEN</span> '🟠 ขายน้อยมาก'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <= 5 <span class="sql-keyword">THEN</span> '🟡 ขายช้า'
        <span class="sql-keyword">ELSE</span> '🟢 ปกติ'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> สถานะ,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) = 0 <span class="sql-keyword">THEN</span> 'ลดราคา, โปรโมชั่น, หรือพิจารณายกเลิก'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <= 2 <span class="sql-keyword">THEN</span> 'สร้างโปรโมชั่น หรือ Bundle กับสินค้าอื่น'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COALESCE</span>(<span class="sql-function">SUM</span>(oi.quantity), 0) <= 5 <span class="sql-keyword">THEN</span> 'ปรับ Marketing หรือตำแหน่งสินค้า'
        <span class="sql-keyword">ELSE</span> 'ไม่ต้องดำเนินการ'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> คำแนะนำ
<span class="sql-keyword">FROM</span> menus m
<span class="sql-keyword">LEFT JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">LEFT JOIN</span> order_items oi <span class="sql-keyword">ON</span> m.id = oi.menu_id
<span class="sql-keyword">LEFT JOIN</span> orders o <span class="sql-keyword">ON</span> oi.order_id = o.id <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">WHERE</span> m.is_active = 1
<span class="sql-keyword">GROUP BY</span> m.id, m.name, c.name, m.price
<span class="sql-keyword">HAVING</span> จำนวนที่ขาย <= 5
<span class="sql-keyword">ORDER BY</span> จำนวนที่ขาย <span class="sql-keyword">ASC</span>, m.price <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('peak-hours-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('peak-hours-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="peak-hours-sql">
<span class="sql-comment">-- ช่วงเวลาเร่าซื้อ: วิเคราะห์การขายตามชั่วโมงและวันในสัปดาห์</span>
<span class="sql-keyword">SELECT</span>
    <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">AS</span> ชั่วโมง,
    <span class="sql-keyword">CASE</span> <span class="sql-function">DAYOFWEEK</span>(order_date)
        <span class="sql-keyword">WHEN</span> 1 <span class="sql-keyword">THEN</span> 'อาทิตย์'
        <span class="sql-keyword">WHEN</span> 2 <span class="sql-keyword">THEN</span> 'จันทร์'
        <span class="sql-keyword">WHEN</span> 3 <span class="sql-keyword">THEN</span> 'อังคาร'
        <span class="sql-keyword">WHEN</span> 4 <span class="sql-keyword">THEN</span> 'พุธ'
        <span class="sql-keyword">WHEN</span> 5 <span class="sql-keyword">THEN</span> 'พฤหัสบดี'
        <span class="sql-keyword">WHEN</span> 6 <span class="sql-keyword">THEN</span> 'ศุกร์'
        <span class="sql-keyword">WHEN</span> 7 <span class="sql-keyword">THEN</span> 'เสาร์'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> วัน,
    <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(total_amount), 2) <span class="sql-keyword">AS</span> ยอดขายเฉลี่ย,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(*) >= (
            <span class="sql-keyword">SELECT</span> <span class="sql-function">AVG</span>(hourly_count)
            <span class="sql-keyword">FROM</span> (
                <span class="sql-keyword">SELECT</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">as</span> hr, <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">as</span> hourly_count
                <span class="sql-keyword">FROM</span> orders
                <span class="sql-keyword">WHERE</span> <span class="date-filter-highlight">order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
                <span class="sql-keyword">GROUP BY</span> <span class="sql-function">HOUR</span>(order_time)
            ) <span class="sql-keyword">as</span> avg_calc
        ) * 1.5 <span class="sql-keyword">THEN</span> '🔥 เร่ามาก'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(*) >= (
            <span class="sql-keyword">SELECT</span> <span class="sql-function">AVG</span>(hourly_count)
            <span class="sql-keyword">FROM</span> (
                <span class="sql-keyword">SELECT</span> <span class="sql-function">HOUR</span>(order_time) <span class="sql-keyword">as</span> hr, <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">as</span> hourly_count
                <span class="sql-keyword">FROM</span> orders
                <span class="sql-keyword">WHERE</span> <span class="date-filter-highlight">order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
                <span class="sql-keyword">GROUP BY</span> <span class="sql-function">HOUR</span>(order_time)
            ) <span class="sql-keyword">as</span> avg_calc
        ) <span class="sql-keyword">THEN</span> '📈 เร่า'
        <span class="sql-keyword">ELSE</span> '📊 ปกติ'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> สถานะ
<span class="sql-keyword">FROM</span> orders
<span class="sql-keyword">WHERE</span> <span class="date-filter-highlight">order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> <span class="sql-function">HOUR</span>(order_time), <span class="sql-function">DAYOFWEEK</span>(order_date)
<span class="sql-keyword">ORDER BY</span> วัน, ชั่วโมง;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-products-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-products-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="staff-products-sql">
<span class="sql-comment">-- สินค้าที่พนักงานขาย: ดูว่าพนักงานแต่ละคนขายสินค้าอะไรบ้าง</span>
<span class="sql-keyword">SELECT</span>
    s.name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    s.position <span class="sql-keyword">AS</span> ตำแหน่ง,
    m.name <span class="sql-keyword">AS</span> ชื่อสินค้า,
    c.name <span class="sql-keyword">AS</span> หมวดหมู่,
    <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">AS</span> จำนวนที่ขาย,
    <span class="sql-function">SUM</span>(oi.subtotal) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(oi.unit_price), 2) <span class="sql-keyword">AS</span> ราคาเฉลี่ย,
    <span class="sql-function">RANK</span>() <span class="sql-keyword">OVER</span> (<span class="sql-keyword">PARTITION BY</span> s.id <span class="sql-keyword">ORDER BY</span> <span class="sql-function">SUM</span>(oi.quantity) <span class="sql-keyword">DESC</span>) <span class="sql-keyword">AS</span> อันดับสินค้าขายดี,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) >= 50 <span class="sql-keyword">THEN</span> '⭐ สินค้าเด่น'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) >= 20 <span class="sql-keyword">THEN</span> '👍 สินค้าขายดี'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">SUM</span>(oi.quantity) >= 10 <span class="sql-keyword">THEN</span> '🔵 สินค้าปกติ'
        <span class="sql-keyword">ELSE</span> '🔴 สินค้าขายน้อย'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> ระดับการขาย
<span class="sql-keyword">FROM</span> staff s
<span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
<span class="sql-keyword">JOIN</span> order_items oi <span class="sql-keyword">ON</span> o.id = oi.order_id
<span class="sql-keyword">JOIN</span> menus m <span class="sql-keyword">ON</span> oi.menu_id = m.id
<span class="sql-keyword">JOIN</span> categories c <span class="sql-keyword">ON</span> m.category_id = c.id
<span class="sql-keyword">WHERE</span> s.is_active = 1 <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position, m.id, m.name, c.name
<span class="sql-keyword">ORDER BY</span> s.name, จำนวนที่ขาย <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-orders-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-orders-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="staff-orders-sql">
<span class="sql-comment">-- ออเดอร์ที่พนักงานรับผิดชอบ: วิเคราะห์ขนาดและรูปแบบออเดอร์</span>
<span class="sql-keyword">SELECT</span>
    s.name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    s.position <span class="sql-keyword">AS</span> ตำแหน่ง,
    <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(o.total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(o.total_amount), 2) <span class="sql-keyword">AS</span> ออเดอร์เฉลี่ย,
    <span class="sql-function">MIN</span>(o.total_amount) <span class="sql-keyword">AS</span> ออเดอร์ต่ำสุด,
    <span class="sql-function">MAX</span>(o.total_amount) <span class="sql-keyword">AS</span> ออเดอร์สูงสุด,
    <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(order_items_count), 1) <span class="sql-keyword">AS</span> รายการเฉลี่ยต่อออเดอร์,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.total_amount >= 500 <span class="sql-keyword">THEN</span> 1 <span class="sql-keyword">END</span>) <span class="sql-keyword">AS</span> ออเดอร์ใหญ่_500_บาทขึ้นไป,
    <span class="sql-function">COUNT</span>(<span class="sql-keyword">CASE</span> <span class="sql-keyword">WHEN</span> o.total_amount < 100 <span class="sql-keyword">THEN</span> 1 <span class="sql-keyword">END</span>) <span class="sql-keyword">AS</span> ออเดอร์เล็ก_ต่ำกว่า100บาท,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">AVG</span>(o.total_amount) >= 300 <span class="sql-keyword">THEN</span> '🏆 ขายออเดอร์ใหญ่'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">AVG</span>(o.total_amount) >= 200 <span class="sql-keyword">THEN</span> '⭐ ขายออเดอร์ปานกลาง'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">AVG</span>(o.total_amount) >= 100 <span class="sql-keyword">THEN</span> '👍 ขายออเดอร์เล็ก'
        <span class="sql-keyword">ELSE</span> '📊 ออเดอร์ขนาดเล็กมาก'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> ประเภทการขาย
<span class="sql-keyword">FROM</span> staff s
<span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
<span class="sql-keyword">JOIN</span> (
    <span class="sql-keyword">SELECT</span> order_id, <span class="sql-function">COUNT</span>(*) <span class="sql-keyword">as</span> order_items_count
    <span class="sql-keyword">FROM</span> order_items
    <span class="sql-keyword">GROUP BY</span> order_id
) oi_count <span class="sql-keyword">ON</span> o.id = oi_count.order_id
<span class="sql-keyword">WHERE</span> s.is_active = 1 <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position
<span class="sql-keyword">ORDER BY</span> ยอดขายรวม <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-efficiency-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-efficiency-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="staff-efficiency-sql">
<span class="sql-comment">-- ประสิทธิภาพพนักงาน: วิเคราะห์ผลิตภาพต่อวันและต่อชั่วโมง</span>
<span class="sql-keyword">SELECT</span>
    s.name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    s.position <span class="sql-keyword">AS</span> ตำแหน่ง,
    <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)) <span class="sql-keyword">AS</span> จำนวนวันที่ทำงาน,
    <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">SUM</span>(o.total_amount) <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0), 2) <span class="sql-keyword">AS</span> ออเดอร์ต่อวัน,
    <span class="sql-function">ROUND</span>(<span class="sql-function">SUM</span>(o.total_amount) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0), 2) <span class="sql-keyword">AS</span> ยอดขายต่อวัน,
    <span class="sql-function">ROUND</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / (<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)) * 8), 2) <span class="sql-keyword">AS</span> ออเดอร์ต่อชั่วโมง,
    <span class="sql-function">ROUND</span>(<span class="sql-function">SUM</span>(o.total_amount) / (<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)) * 8), 2) <span class="sql-keyword">AS</span> ยอดขายต่อชั่วโมง,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0) >= 20 <span class="sql-keyword">THEN</span> '🚀 ประสิทธิภาพสูงมาก'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0) >= 15 <span class="sql-keyword">THEN</span> '⭐ ประสิทธิภาพสูง'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0) >= 10 <span class="sql-keyword">THEN</span> '👍 ประสิทธิภาพปานกลาง'
        <span class="sql-keyword">WHEN</span> <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) / <span class="sql-function">NULLIF</span>(<span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> <span class="sql-function">DATE</span>(o.order_date)), 0) >= 5 <span class="sql-keyword">THEN</span> '📊 ประสิทธิภาพต่ำ'
        <span class="sql-keyword">ELSE</span> '🔴 ต้องพัฒนา'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> ระดับประสิทธิภาพ
<span class="sql-keyword">FROM</span> staff s
<span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
<span class="sql-keyword">WHERE</span> s.is_active = 1 <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
<span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position
<span class="sql-keyword">ORDER BY</span> ออเดอร์ต่อวัน <span class="sql-keyword">DESC</span>;
                                    </div>
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
                                <div class="sql-code-container">
                                    <div class="sql-code-header">
                                        <h6><i class="fas fa-code"></i> SQL Query: <small class="text-muted">(อัปเดตตามช่วงเวลาที่เลือก)</small></h6>
                                        <div class="sql-code-actions">
                                            <button class="btn-copy-sql" onclick="copySQLToClipboard('staff-comparison-sql', this)">
                                                <i class="fas fa-copy"></i> คัดลอก SQL
                                            </button>
                                            <button class="btn-toggle-sql" onclick="toggleSQL('staff-comparison-sql', this)">
                                                <i class="fas fa-chevron-up"></i> <span>ซ่อน</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sql-dynamic" id="staff-comparison-sql">
<span class="sql-comment">-- เปรียบเทียบพนักงาน: เปรียบเทียบกับค่าเฉลี่ยของทีม</span>
<span class="sql-keyword">WITH</span> staff_performance <span class="sql-keyword">AS</span> (
    <span class="sql-keyword">SELECT</span>
        s.id,
        s.name,
        s.position,
        <span class="sql-function">COUNT</span>(<span class="sql-function">DISTINCT</span> o.id) <span class="sql-keyword">AS</span> total_orders,
        <span class="sql-function">SUM</span>(o.total_amount) <span class="sql-keyword">AS</span> total_sales,
        <span class="sql-function">ROUND</span>(<span class="sql-function">AVG</span>(o.total_amount), 2) <span class="sql-keyword">AS</span> avg_order_value
    <span class="sql-keyword">FROM</span> staff s
    <span class="sql-keyword">JOIN</span> orders o <span class="sql-keyword">ON</span> s.id = o.staff_id
    <span class="sql-keyword">WHERE</span> s.is_active = 1 <span class="sql-keyword">AND</span> <span class="date-filter-highlight">o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</span>
    <span class="sql-keyword">GROUP BY</span> s.id, s.name, s.position
),
team_averages <span class="sql-keyword">AS</span> (
    <span class="sql-keyword">SELECT</span>
        <span class="sql-function">AVG</span>(total_orders) <span class="sql-keyword">AS</span> avg_team_orders,
        <span class="sql-function">AVG</span>(total_sales) <span class="sql-keyword">AS</span> avg_team_sales,
        <span class="sql-function">AVG</span>(avg_order_value) <span class="sql-keyword">AS</span> avg_team_order_value
    <span class="sql-keyword">FROM</span> staff_performance
)
<span class="sql-keyword">SELECT</span>
    sp.name <span class="sql-keyword">AS</span> ชื่อพนักงาน,
    sp.position <span class="sql-keyword">AS</span> ตำแหน่ง,
    sp.total_orders <span class="sql-keyword">AS</span> จำนวนออเดอร์,
    <span class="sql-function">ROUND</span>(ta.avg_team_orders, 0) <span class="sql-keyword">AS</span> ค่าเฉลี่ยทีม_ออเดอร์,
    <span class="sql-function">ROUND</span>(((sp.total_orders - ta.avg_team_orders) / ta.avg_team_orders) * 100, 1) <span class="sql-keyword">AS</span> เปอร์เซ็นต์เปรียบเทียบ_ออเดอร์,
    sp.total_sales <span class="sql-keyword">AS</span> ยอดขายรวม,
    <span class="sql-function">ROUND</span>(ta.avg_team_sales, 0) <span class="sql-keyword">AS</span> ค่าเฉลี่ยทีม_ยอดขาย,
    <span class="sql-function">ROUND</span>(((sp.total_sales - ta.avg_team_sales) / ta.avg_team_sales) * 100, 1) <span class="sql-keyword">AS</span> เปอร์เซ็นต์เปรียบเทียบ_ยอดขาย,
    sp.avg_order_value <span class="sql-keyword">AS</span> ค่าออเดอร์เฉลี่ย,
    <span class="sql-function">ROUND</span>(ta.avg_team_order_value, 2) <span class="sql-keyword">AS</span> ค่าเฉลี่ยทีม_ค่าออเดอร์,
    <span class="sql-keyword">CASE</span>
        <span class="sql-keyword">WHEN</span> sp.total_sales > ta.avg_team_sales * 1.2 <span class="sql-keyword">THEN</span> '🏆 เหนือค่าเฉลี่ยมาก'
        <span class="sql-keyword">WHEN</span> sp.total_sales > ta.avg_team_sales <span class="sql-keyword">THEN</span> '⭐ เหนือค่าเฉลี่ย'
        <span class="sql-keyword">WHEN</span> sp.total_sales > ta.avg_team_sales * 0.8 <span class="sql-keyword">THEN</span> '📊 ใกล้ค่าเฉลี่ย'
        <span class="sql-keyword">ELSE</span> '📈 ต่ำกว่าค่าเฉลี่ย'
    <span class="sql-keyword">END</span> <span class="sql-keyword">AS</span> ผลงานเปรียบเทียบ
<span class="sql-keyword">FROM</span> staff_performance sp
<span class="sql-keyword">CROSS JOIN</span> team_averages ta
<span class="sql-keyword">ORDER BY</span> sp.total_sales <span class="sql-keyword">DESC</span>;
                                    </div>
                                </div>
                                <button class="btn btn-coffee" onclick="loadReport('staff_comparison')">
                                    <i class="fas fa-play"></i> รันคำสั่ง SQL
                                </button>
                                <div id="staff-comparison-result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <div class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
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

        // Copy SQL to clipboard
        function copySQLToClipboard(elementId, buttonElement) {
            const sqlElement = document.getElementById(elementId);
            const sqlText = sqlElement.innerText;

            navigator.clipboard.writeText(sqlText).then(() => {
                // Change button appearance
                const originalHTML = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-check"></i> คัดลอกแล้ว!';
                buttonElement.classList.add('copied');

                // Reset after 2 seconds
                setTimeout(() => {
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert('ไม่สามารถคัดลอก SQL ได้');
            });
        }

        // Toggle SQL visibility
        function toggleSQL(elementId, buttonElement) {
            const sqlElement = document.getElementById(elementId);
            const icon = buttonElement.querySelector('i');

            if (sqlElement.classList.contains('sql-collapsed')) {
                sqlElement.classList.remove('sql-collapsed');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                buttonElement.querySelector('span').textContent = 'ซ่อน';
            } else {
                sqlElement.classList.add('sql-collapsed');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                buttonElement.querySelector('span').textContent = 'แสดง';
            }
        }

        // Add smooth scroll animation
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });

            // Scroll to top button functionality
            window.addEventListener('scroll', function() {
                const scrollButton = document.getElementById('scrollToTop');
                if (window.pageYOffset > 300) {
                    scrollButton.classList.add('visible');
                } else {
                    scrollButton.classList.remove('visible');
                }
            });
        });

        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>