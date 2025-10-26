<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรายการสั่งซื้อ - Coffee Shop Analytics</title>
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
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
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
        .badge-payment {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        .order-id {
            font-weight: bold;
            color: #8B4513;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .pagination {
            margin-top: 20px;
        }
        .modal-header {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
        }
        .item-row {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 0;
        }
        .item-row:last-child {
            border-bottom: none;
        }
        .total-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
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
                        <a class="nav-link active" href="manage_orders.php"><i class="fas fa-shopping-cart"></i> จัดการออเดอร์</a>
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
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart"></i> รายการสั่งซื้อทั้งหมด</h2>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label"><i class="fas fa-search"></i> ค้นหา</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="เลขออเดอร์, ลูกค้า, พนักงาน...">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> วันที่เริ่มต้น</label>
                    <input type="date" class="form-control" id="startDate">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> วันที่สิ้นสุด</label>
                    <input type="date" class="form-control" id="endDate">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label"><i class="fas fa-credit-card"></i> วิธีชำระเงิน</label>
                    <select class="form-select" id="paymentType">
                        <option value="">ทั้งหมด</option>
                        <option value="cash">เงินสด</option>
                        <option value="qr">QR Code</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button class="btn btn-coffee" onclick="loadOrders(1)">
                            <i class="fas fa-filter"></i> กรองข้อมูล
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">เลขออเดอร์</th>
                                <th width="12%">วันที่/เวลา</th>
                                <th width="15%">ลูกค้า</th>
                                <th width="15%">พนักงาน</th>
                                <th width="10%">จำนวนรายการ</th>
                                <th width="12%">ยอดรวม</th>
                                <th width="13%">วิธีชำระเงิน</th>
                                <th width="13%">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">
                        <i class="fas fa-receipt"></i> รายละเอียดออเดอร์
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-coffee" onclick="printOrder()">
                        <i class="fas fa-print"></i> พิมพ์ใบเสร็จ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        let currentOrderId = null;
        const itemsPerPage = 20;

        // Load orders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders(1);
        });

        // Load orders function
        function loadOrders(page = 1) {
            currentPage = page;
            const search = document.getElementById('searchInput').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const paymentType = document.getElementById('paymentType').value;

            const params = new URLSearchParams({
                page: page,
                limit: itemsPerPage,
                search: search,
                start_date: startDate,
                end_date: endDate,
                payment_type: paymentType
            });

            fetch(`api/get_orders.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrders(data.data);
                        displayPagination(data.pagination);
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('ไม่สามารถโหลดข้อมูลได้');
                });
        }

        // Display orders in table
        function displayOrders(orders) {
            const tbody = document.getElementById('ordersTableBody');

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">ไม่พบข้อมูล</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(order => {
                const paymentBadge = getPaymentBadge(order.payment_type);
                const customerName = order.customer_name || '<span class="text-muted">ไม่ระบุ</span>';
                const date = new Date(order.order_date + ' ' + order.order_time);
                const formattedDate = date.toLocaleDateString('th-TH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                const formattedTime = order.order_time.substring(0, 5);

                return `
                    <tr onclick="viewOrderDetails(${order.id})">
                        <td class="order-id">#${order.id}</td>
                        <td>
                            <div>${formattedDate}</div>
                            <small class="text-muted">${formattedTime}</small>
                        </td>
                        <td>
                            ${customerName}
                            ${order.customer_phone ? `<br><small class="text-muted">${order.customer_phone}</small>` : ''}
                        </td>
                        <td>
                            ${order.staff_name || '-'}
                            ${order.staff_position ? `<br><small class="text-muted">${order.staff_position}</small>` : ''}
                        </td>
                        <td class="text-center">${order.items_count} รายการ</td>
                        <td><strong>฿${parseFloat(order.total_amount).toLocaleString('th-TH', {minimumFractionDigits: 2})}</strong></td>
                        <td>${paymentBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewOrderDetails(${order.id})">
                                <i class="fas fa-eye"></i> ดูรายละเอียด
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Get payment badge
        function getPaymentBadge(paymentType) {
            const badges = {
                'cash': '<span class="badge bg-success badge-payment"><i class="fas fa-money-bill"></i> เงินสด</span>',
                'qr': '<span class="badge bg-info badge-payment"><i class="fas fa-qrcode"></i> QR Code</span>',
                'online': '<span class="badge bg-primary badge-payment"><i class="fas fa-globe"></i> Online</span>'
            };
            return badges[paymentType] || `<span class="badge bg-secondary badge-payment">${paymentType}</span>`;
        }

        // Display pagination
        function displayPagination(pagination) {
            const paginationElement = document.getElementById('pagination');

            if (pagination.total_pages <= 1) {
                paginationElement.innerHTML = '';
                return;
            }

            let html = '';

            // Previous button
            if (pagination.page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${pagination.page - 1}); return false;">หน้าก่อน</a></li>`;
            }

            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (
                    i === 1 ||
                    i === pagination.total_pages ||
                    (i >= pagination.page - 2 && i <= pagination.page + 2)
                ) {
                    const active = i === pagination.page ? 'active' : '';
                    html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadOrders(${i}); return false;">${i}</a></li>`;
                } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Next button
            if (pagination.page < pagination.total_pages) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${pagination.page + 1}); return false;">หน้าถัดไป</a></li>`;
            }

            paginationElement.innerHTML = html;
        }

        // View order details
        function viewOrderDetails(orderId) {
            currentOrderId = orderId;
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();

            // Show loading
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Fetch order details
            fetch(`api/get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.data);
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('ไม่สามารถโหลดข้อมูลได้');
                });
        }

        // Display order details
        function displayOrderDetails(order) {
            const date = new Date(order.order_date + ' ' + order.order_time);
            const formattedDateTime = date.toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-receipt"></i> เลขที่ออเดอร์</h6>
                        <p class="order-id">#${order.id}</p>

                        <h6><i class="fas fa-calendar"></i> วันที่/เวลา</h6>
                        <p>${formattedDateTime}</p>

                        <h6><i class="fas fa-user"></i> ลูกค้า</h6>
                        <p>${order.customer_name || 'ไม่ระบุ'}</p>
                        ${order.customer_phone ? `<p class="text-muted">โทร: ${order.customer_phone}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-user-tie"></i> พนักงาน</h6>
                        <p>${order.staff_name || '-'}</p>
                        ${order.staff_position ? `<p class="text-muted">${order.staff_position}</p>` : ''}

                        <h6><i class="fas fa-credit-card"></i> วิธีชำระเงิน</h6>
                        <p>${getPaymentBadge(order.payment_type)}</p>
                    </div>
                </div>

                <hr>

                <h6><i class="fas fa-shopping-basket"></i> รายการสินค้า</h6>
                <div class="mb-3">
            `;

            // Add items
            order.items.forEach(item => {
                html += `
                    <div class="item-row">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${item.menu_name}</strong>
                                <br>
                                <small class="text-muted">${item.category_name || ''}</small>
                            </div>
                            <div class="text-end">
                                <div>${item.quantity} x ฿${parseFloat(item.unit_price).toLocaleString('th-TH', {minimumFractionDigits: 2})}</div>
                                <strong>฿${parseFloat(item.subtotal).toLocaleString('th-TH', {minimumFractionDigits: 2})}</strong>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;

            // Add totals
            html += `
                <div class="total-section">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>ยอดรวมทั้งหมด:</strong>
                        <strong class="text-primary fs-5">฿${parseFloat(order.total_amount).toLocaleString('th-TH', {minimumFractionDigits: 2})}</strong>
                    </div>
                </div>
            `;

            document.getElementById('orderDetailsContent').innerHTML = html;
        }

        // Print order
        function printOrder() {
            if (!currentOrderId) return;

            // Implement print functionality
            window.print();
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loadOrders(1);
            }
        });
    </script>
</body>
</html>
