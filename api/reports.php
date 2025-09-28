<?php
// Prevent any output before JSON response
ob_start();

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Clear any previous output
ob_clean();

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }
    $reportType = $_GET['type'] ?? '';
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;

    // Build date filter condition
    $dateFilter = "";
    $dateParams = [];

    if ($startDate && $endDate) {
        $dateFilter = " AND order_date BETWEEN ? AND ?";
        $dateParams = [$startDate, $endDate];
    } elseif ($startDate) {
        $dateFilter = " AND order_date >= ?";
        $dateParams = [$startDate];
    } elseif ($endDate) {
        $dateFilter = " AND order_date <= ?";
        $dateParams = [$endDate];
    } else {
        // Default to last 7 days if no date specified
        $dateFilter = " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $dateParams = [];
    }

    // Prepare final parameters array for execution
    $finalParams = [];

    switch ($reportType) {
        case 'daily_sales':
            $query = "
                SELECT
                    DATE(order_date) as 'วันที่',
                    COUNT(*) as 'จำนวนออเดอร์',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย',
                    MIN(total_amount) as 'ยอดขายต่ำสุด',
                    MAX(total_amount) as 'ยอดขายสูงสุด'
                FROM orders
                WHERE 1=1 $dateFilter
                GROUP BY DATE(order_date)
                ORDER BY order_date DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'monthly_sales':
            $query = "
                SELECT
                    YEAR(order_date) as 'ปี',
                    MONTH(order_date) as 'เดือน',
                    MONTHNAME(order_date) as 'ชื่อเดือน',
                    COUNT(*) as 'จำนวนออเดอร์',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย'
                FROM orders
                WHERE 1=1 $dateFilter
                GROUP BY YEAR(order_date), MONTH(order_date)
                ORDER BY YEAR(order_date) DESC, MONTH(order_date) DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'top_products':
            $query = "
                SELECT
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    SUM(oi.quantity) as 'จำนวนที่ขาย',
                    COUNT(DISTINCT o.id) as 'จำนวนออเดอร์',
                    SUM(oi.subtotal) as 'ยอดขายรวม',
                    ROUND(AVG(oi.unit_price), 2) as 'ราคาเฉลี่ย'
                FROM order_items oi
                JOIN menus m ON oi.menu_id = m.id
                JOIN categories c ON m.category_id = c.id
                JOIN orders o ON oi.order_id = o.id
                WHERE 1=1 $dateFilter
                GROUP BY m.id, m.name, c.name
                ORDER BY SUM(oi.quantity) DESC
                LIMIT 10
            ";
            $finalParams = $dateParams;
            break;

        case 'customer_analysis':
            // Customer analysis doesn't need date filter as it shows overall customer data
            $query = "
                SELECT
                    c.name as 'ชื่อลูกค้า',
                    c.phone as 'เบอร์โทร',
                    c.points as 'แต้มสะสม',
                    c.total_spent as 'ยอดซื้อสะสม',
                    c.visit_count as 'จำนวนครั้งที่มา',
                    COALESCE(recent_orders.last_order, 'ไม่เคยสั่ง') as 'ออเดอร์ล่าสุด',
                    CASE
                        WHEN c.total_spent >= 5000 THEN 'VIP'
                        WHEN c.total_spent >= 2000 THEN 'Gold'
                        WHEN c.total_spent >= 1000 THEN 'Silver'
                        ELSE 'Bronze'
                    END as 'ระดับสมาชิก'
                FROM customers c
                LEFT JOIN (
                    SELECT
                        customer_id,
                        MAX(order_date) as last_order
                    FROM orders
                    WHERE customer_id IS NOT NULL
                    GROUP BY customer_id
                ) recent_orders ON c.id = recent_orders.customer_id
                WHERE c.is_active = 1
                ORDER BY c.total_spent DESC
            ";
            $finalParams = [];
            break;

        case 'staff_performance':
            $query = "
                SELECT
                    s.name as 'ชื่อพนักงาน',
                    s.position as 'ตำแหน่ง',
                    COUNT(o.id) as 'จำนวนออเดอร์',
                    SUM(o.total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(o.total_amount), 2) as 'ยอดขายเฉลี่ย',
                    ROUND(SUM(o.total_amount) / COUNT(o.id), 2) as 'ยอดขายต่อออเดอร์',
                    MIN(o.order_date) as 'วันแรกที่ขาย',
                    MAX(o.order_date) as 'วันล่าสุดที่ขาย'
                FROM staff s
                INNER JOIN orders o ON s.id = o.staff_id
                WHERE 1=1 $dateFilter
                GROUP BY s.id, s.name, s.position
                ORDER BY SUM(o.total_amount) DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'payment_analysis':
            $query = "
                SELECT
                    CASE
                        WHEN payment_type = 'cash' THEN 'เงินสด'
                        WHEN payment_type = 'qr' THEN 'QR Code'
                        WHEN payment_type = 'online' THEN 'Online Payment'
                        ELSE payment_type
                    END as 'วิธีการชำระเงิน',
                    COUNT(*) as 'จำนวนออเดอร์',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย'
                FROM orders
                WHERE 1=1 $dateFilter
                GROUP BY payment_type
                ORDER BY COUNT(*) DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'hourly_analysis':
            $query = "
                SELECT
                    HOUR(order_time) as 'ชั่วโมง',
                    CASE
                        WHEN HOUR(order_time) BETWEEN 6 AND 10 THEN 'ช่วงเช้า'
                        WHEN HOUR(order_time) BETWEEN 11 AND 14 THEN 'ช่วงเที่ยง'
                        WHEN HOUR(order_time) BETWEEN 15 AND 18 THEN 'ช่วงบ่าย'
                        WHEN HOUR(order_time) BETWEEN 19 AND 22 THEN 'ช่วงเย็น'
                        ELSE 'ช่วงพิเศษ'
                    END as 'ช่วงเวลา',
                    COUNT(*) as 'จำนวนออเดอร์',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย'
                FROM orders
                WHERE 1=1 $dateFilter
                GROUP BY HOUR(order_time)
                ORDER BY HOUR(order_time)
            ";
            $finalParams = $dateParams;
            break;

        case 'advanced_queries':
            // Advanced queries doesn't need date filter for this example
            $query = "
                SELECT
                    c.name as 'ชื่อลูกค้า',
                    c.total_spent as 'ยอดซื้อสะสม',
                    ROUND((SELECT AVG(total_spent) FROM customers WHERE is_active = 1), 2) as 'ค่าเฉลี่ย',
                    ROUND(c.total_spent - (SELECT AVG(total_spent) FROM customers WHERE is_active = 1), 2) as 'ส่วนต่าง'
                FROM customers c
                WHERE c.is_active = 1
                    AND c.total_spent > (SELECT AVG(total_spent) FROM customers WHERE is_active = 1)
                ORDER BY c.total_spent DESC
                LIMIT 10
            ";
            $finalParams = [];
            break;

        default:
            throw new Exception("Invalid report type");
    }

    $stmt = $db->prepare($query);

    // Execute with final parameters
    $stmt->execute($finalParams);

    $data = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    // Generate summary based on report type
    $summary = '';
    switch ($reportType) {
        case 'daily_sales':
            if (!empty($data)) {
                $totalSales = array_sum(array_column($data, 'ยอดขายรวม'));
                $totalOrders = array_sum(array_column($data, 'จำนวนออเดอร์'));
                $summary = "ยอดขายรวม 7 วันล่าสุด: ฿" . number_format($totalSales, 2) . " จากออเดอร์ทั้งหมด " . number_format($totalOrders) . " ออเดอร์";
            }
            break;
        case 'top_products':
            if (!empty($data)) {
                $topProduct = $data[0];
                $summary = "สินค้าขายดีอันดับ 1: " . $topProduct['ชื่อสินค้า'] . " ขายได้ " . $topProduct['จำนวนที่ขาย'] . " ชิ้น";
            }
            break;
    }

    // Clear output buffer and send response
    ob_clean();
    echo json_encode(array(
        "success" => true,
        "data" => $data,
        "summary" => $summary,
        "count" => count($data)
    ));

} catch(Exception $exception) {
    // Clear any output buffer
    ob_clean();

    // Send JSON error response
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => $exception->getMessage(),
        "error_type" => "server_error"
    ));
}

// End output buffering
ob_end_flush();
?>