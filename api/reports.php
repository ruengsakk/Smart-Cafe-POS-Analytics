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

        case 'product_inventory':
            $query = "
                SELECT
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    m.price as 'ราคา',
                    COALESCE(SUM(oi.quantity), 0) as 'จำนวนที่ขาย',
                    COALESCE(SUM(oi.subtotal), 0) as 'ยอดขายรวม',
                    COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) as 'ออเดอร์_7วัน',
                    CASE
                        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ไม่มีการขาย'
                        WHEN COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) = 0 THEN 'สินค้าค้าง'
                        WHEN COALESCE(SUM(oi.quantity), 0) >= 100 THEN 'ขายดีมาก'
                        WHEN COALESCE(SUM(oi.quantity), 0) >= 50 THEN 'ขายดีปานกลาง'
                        ELSE 'ขายน้อย'
                    END as 'สถานะสินค้า'
                FROM menus m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN order_items oi ON m.id = oi.menu_id
                LEFT JOIN orders o ON oi.order_id = o.id $dateFilter
                WHERE m.is_active = 1
                GROUP BY m.id, m.name, c.name, m.price
                ORDER BY 'จำนวนที่ขาย' DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'order_patterns':
            $query = "
                SELECT
                    CASE
                        WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท'
                        WHEN total_amount < 200 THEN '100-199 บาท'
                        WHEN total_amount < 500 THEN '200-499 บาท'
                        WHEN total_amount < 1000 THEN '500-999 บาท'
                        ELSE '1000+ บาท'
                    END as 'ช่วงยอดขาย',
                    COUNT(*) as 'จำนวนออเดอร์',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders WHERE 1=1 $dateFilter)), 2) as 'เปอร์เซ็นต์',
                    AVG(items_count.item_count) as 'จำนวนสินค้าเฉลี่ย'
                FROM orders o
                JOIN (
                    SELECT order_id, COUNT(*) as item_count
                    FROM order_items
                    GROUP BY order_id
                ) items_count ON o.id = items_count.order_id
                WHERE 1=1 $dateFilter
                GROUP BY CASE
                    WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท'
                    WHEN total_amount < 200 THEN '100-199 บาท'
                    WHEN total_amount < 500 THEN '200-499 บาท'
                    WHEN total_amount < 1000 THEN '500-999 บาท'
                    ELSE '1000+ บาท'
                END
                ORDER BY MIN(total_amount)
            ";
            $finalParams = array_merge($dateParams, $dateParams);
            break;

        case 'staff_ranking':
            $query = "
                SELECT
                    RANK() OVER(ORDER BY total_sales DESC) as 'อันดับ',
                    staff_name as 'ชื่อพนักงาน',
                    position as 'ตำแหน่ง',
                    total_orders as 'จำนวนออเดอร์',
                    total_sales as 'ยอดขายรวม',
                    avg_order_value as 'ยอดขายเฉลี่ยต่อออเดอร์',
                    sales_vs_target as 'เปรียบเทียบกับเป้าหมาย',
                    performance_rating as 'ระดับผลงาน',
                    'วันที่เริ่มงาน',
                    'วันล่าสุดขาย'
                FROM (
                    SELECT
                        s.name as staff_name,
                        s.position,
                        COUNT(o.id) as total_orders,
                        COALESCE(SUM(o.total_amount), 0) as total_sales,
                        ROUND(COALESCE(AVG(o.total_amount), 0), 2) as avg_order_value,
                        CONCAT(ROUND((COALESCE(SUM(o.total_amount), 0) / 10000) * 100, 1), '%') as sales_vs_target,
                        CASE
                            WHEN COALESCE(SUM(o.total_amount), 0) >= 15000 THEN 'ดีเยี่ยม 🏆'
                            WHEN COALESCE(SUM(o.total_amount), 0) >= 10000 THEN 'ดี 🌟'
                            WHEN COALESCE(SUM(o.total_amount), 0) >= 5000 THEN 'ปานกลาง 💪'
                            ELSE 'ต้องพัฒนา 🚀'
                        END as performance_rating,
                        DATE(MIN(o.order_date)) as 'วันที่เริ่มงาน',
                        DATE(MAX(o.order_date)) as 'วันล่าสุดขาย'
                    FROM staff s
                    LEFT JOIN orders o ON s.id = o.staff_id $dateFilter
                    WHERE s.is_active = 1
                    GROUP BY s.id, s.name, s.position
                ) staff_stats
                ORDER BY total_sales DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'product_comparison':
            $query = "
                SELECT
                    c.name as 'หมวดหมู่',
                    COUNT(DISTINCT m.id) as 'จำนวนสินค้า',
                    COALESCE(SUM(oi.quantity), 0) as 'จำนวนที่ขายรวม',
                    COALESCE(SUM(oi.subtotal), 0) as 'ยอดขายรวม',
                    ROUND(COALESCE(AVG(oi.unit_price), 0), 2) as 'ราคาเฉลี่ย',
                    ROUND(
                        (COALESCE(SUM(oi.subtotal), 0) * 100.0) /
                        NULLIF((SELECT SUM(subtotal) FROM order_items oi2
                                JOIN orders o2 ON oi2.order_id = o2.id
                                WHERE 1=1 $dateFilter), 0), 2
                    ) as 'สัดส่วนยอดขาย',
                    COUNT(DISTINCT o.id) as 'จำนวนออเดอร์',
                    ROUND(
                        COALESCE(SUM(oi.quantity), 0) /
                        NULLIF(COUNT(DISTINCT o.id), 0), 2
                    ) as 'จำนวนต่อออเดอร์',
                    CASE
                        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ไม่มีการขาย'
                        WHEN COALESCE(SUM(oi.subtotal), 0) >= 5000 THEN 'หมวดหมู่ยอดนิยม 🔥'
                        WHEN COALESCE(SUM(oi.subtotal), 0) >= 2000 THEN 'หมวดหมู่ขายดี ⭐'
                        ELSE 'หมวดหมู่ขายช้า 📊'
                    END as 'สถานะหมวดหมู่'
                FROM categories c
                LEFT JOIN menus m ON c.id = m.category_id AND m.is_active = 1
                LEFT JOIN order_items oi ON m.id = oi.menu_id
                LEFT JOIN orders o ON oi.order_id = o.id $dateFilter
                GROUP BY c.id, c.name
                ORDER BY 'ยอดขายรวม' DESC
            ";
            $finalParams = array_merge($dateParams, $dateParams);
            break;

        case 'order_size_analysis':
            $query = "
                SELECT
                    order_size_category as 'ประเภทขนาดออเดอร์',
                    COUNT(*) as 'จำนวนออเดอร์',
                    ROUND(AVG(total_items), 1) as 'จำนวนสินค้าเฉลี่ย',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND((COUNT(*) * 100.0 /
                        (SELECT COUNT(*) FROM orders WHERE 1=1 $dateFilter)), 2) as 'เปอร์เซ็นต์ออเดอร์',
                    ROUND((SUM(total_amount) * 100.0 /
                        (SELECT SUM(total_amount) FROM orders WHERE 1=1 $dateFilter)), 2) as 'เปอร์เซ็นต์ยอดขาย',
                    MIN(total_amount) as 'ยอดขายต่ำสุด',
                    MAX(total_amount) as 'ยอดขายสูงสุด'
                FROM (
                    SELECT
                        o.id,
                        o.total_amount,
                        SUM(oi.quantity) as total_items,
                        CASE
                            WHEN SUM(oi.quantity) = 1 THEN 'ออเดอร์เดี่ยว (1 ชิ้น)'
                            WHEN SUM(oi.quantity) <= 3 THEN 'ออเดอร์เล็ก (2-3 ชิ้น)'
                            WHEN SUM(oi.quantity) <= 5 THEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)'
                            WHEN SUM(oi.quantity) <= 10 THEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)'
                            ELSE 'ออเดอร์รายใหญ่ (10+ ชิ้น)'
                        END as order_size_category
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    WHERE 1=1 $dateFilter
                    GROUP BY o.id, o.total_amount
                ) order_analysis
                GROUP BY order_size_category
                ORDER BY CASE order_size_category
                    WHEN 'ออเดอร์เดี่ยว (1 ชิ้น)' THEN 1
                    WHEN 'ออเดอร์เล็ก (2-3 ชิ้น)' THEN 2
                    WHEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)' THEN 3
                    WHEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)' THEN 4
                    ELSE 5
                END
            ";
            $finalParams = array_merge($dateParams, $dateParams, $dateParams);
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

        case 'product_performance':
            $query = "
                SELECT
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    m.price as 'ราคา',
                    COALESCE(SUM(oi.quantity), 0) as 'จำนวนที่ขาย',
                    COALESCE(SUM(oi.subtotal), 0) as 'ยอดขายรวม',
                    ROUND(COALESCE(SUM(oi.subtotal), 0) / NULLIF(SUM(oi.quantity), 0), 2) as 'ราคาเฉลี่ยที่ขายได้',
                    ROUND(COALESCE(SUM(oi.quantity), 0) / NULLIF(DATEDIFF(CURDATE(), DATE_SUB(CURDATE(), INTERVAL 30 DAY)), 0), 2) as 'อัตราการขายต่อวัน'
                FROM menus m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN order_items oi ON m.id = oi.menu_id
                LEFT JOIN orders o ON oi.order_id = o.id $dateFilter
                WHERE m.is_active = 1
                GROUP BY m.id, m.name, c.name, m.price
                ORDER BY ยอดขายรวม DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'product_trends':
            $query = "
                SELECT
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) as 'ขาย_7วันล่าสุด',
                    SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) as 'ขาย_7วันก่อน',
                    CASE
                        WHEN SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) = 0 THEN 'สินค้าใหม่/ไม่มีข้อมูล'
                        WHEN SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) > SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) THEN '📈 เพิ่มขึ้น'
                        WHEN SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) < SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) THEN '📉 ลดลง'
                        ELSE '➡️ คงเดิม'
                    END as 'เทรนด์'
                FROM menus m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN order_items oi ON m.id = oi.menu_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                WHERE m.is_active = 1
                GROUP BY m.id, m.name, c.name
                HAVING (ขาย_7วันล่าสุด + ขาย_7วันก่อน) > 0
                ORDER BY ขาย_7วันล่าสุด DESC
            ";
            $finalParams = [];
            break;

        case 'slow_moving_products':
            $query = "
                SELECT
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    m.price as 'ราคา',
                    COALESCE(SUM(oi.quantity), 0) as 'จำนวนที่ขาย',
                    COALESCE(SUM(oi.subtotal), 0) as 'ยอดขาย',
                    COALESCE(MAX(o.order_date), 'ไม่เคยขาย') as 'ขายครั้งล่าสุด',
                    CASE
                        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN '🔴 ไม่เคยขาย'
                        WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN '🟠 ขายน้อยมาก'
                        WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN '🟡 ขายช้า'
                        ELSE '🟢 ปกติ'
                    END as 'สถานะ',
                    CASE
                        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ลดราคา, โปรโมชั่น, หรือพิจารณายกเลิก'
                        WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN 'สร้างโปรโมชั่น หรือ Bundle กับสินค้าอื่น'
                        WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN 'ปรับ Marketing หรือตำแหน่งสินค้า'
                        ELSE 'ไม่ต้องดำเนินการ'
                    END as 'คำแนะนำ'
                FROM menus m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN order_items oi ON m.id = oi.menu_id
                LEFT JOIN orders o ON oi.order_id = o.id $dateFilter
                WHERE m.is_active = 1
                GROUP BY m.id, m.name, c.name, m.price
                HAVING จำนวนที่ขาย <= 5
                ORDER BY จำนวนที่ขาย ASC, m.price DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'peak_hours':
            $query = "
                SELECT
                    HOUR(order_time) as 'ชั่วโมง',
                    CASE DAYOFWEEK(order_date)
                        WHEN 1 THEN 'อาทิตย์'
                        WHEN 2 THEN 'จันทร์'
                        WHEN 3 THEN 'อังคาร'
                        WHEN 4 THEN 'พุธ'
                        WHEN 5 THEN 'พฤหัสบดี'
                        WHEN 6 THEN 'ศุกร์'
                        WHEN 7 THEN 'เสาร์'
                    END as 'วัน',
                    COUNT(*) as 'จำนวนออเดอร์',
                    SUM(total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(total_amount), 2) as 'ยอดขายเฉลี่ย',
                    CASE
                        WHEN COUNT(*) >= (
                            SELECT AVG(hourly_count)
                            FROM (
                                SELECT HOUR(order_time) as hr, COUNT(*) as hourly_count
                                FROM orders
                                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                GROUP BY HOUR(order_time)
                            ) as avg_calc
                        ) * 1.5 THEN '🔥 เร่ามาก'
                        WHEN COUNT(*) >= (
                            SELECT AVG(hourly_count)
                            FROM (
                                SELECT HOUR(order_time) as hr, COUNT(*) as hourly_count
                                FROM orders
                                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                GROUP BY HOUR(order_time)
                            ) as avg_calc
                        ) THEN '📈 เร่า'
                        ELSE '📊 ปกติ'
                    END as 'สถานะ'
                FROM orders
                WHERE 1=1 $dateFilter
                GROUP BY HOUR(order_time), DAYOFWEEK(order_date)
                ORDER BY วัน, ชั่วโมง
            ";
            $finalParams = $dateParams;
            break;

        case 'staff_products':
            $query = "
                SELECT
                    s.name as 'ชื่อพนักงาน',
                    s.position as 'ตำแหน่ง',
                    m.name as 'ชื่อสินค้า',
                    c.name as 'หมวดหมู่',
                    SUM(oi.quantity) as 'จำนวนที่ขาย',
                    SUM(oi.subtotal) as 'ยอดขายรวม',
                    COUNT(DISTINCT o.id) as 'จำนวนออเดอร์',
                    ROUND(AVG(oi.unit_price), 2) as 'ราคาเฉลี่ย',
                    RANK() OVER (PARTITION BY s.id ORDER BY SUM(oi.quantity) DESC) as 'อันดับสินค้าขายดี',
                    CASE
                        WHEN SUM(oi.quantity) >= 50 THEN '⭐ สินค้าเด่น'
                        WHEN SUM(oi.quantity) >= 20 THEN '👍 สินค้าขายดี'
                        WHEN SUM(oi.quantity) >= 10 THEN '🔵 สินค้าปกติ'
                        ELSE '🔴 สินค้าขายน้อย'
                    END as 'ระดับการขาย'
                FROM staff s
                JOIN orders o ON s.id = o.staff_id
                JOIN order_items oi ON o.id = oi.order_id
                JOIN menus m ON oi.menu_id = m.id
                JOIN categories c ON m.category_id = c.id
                WHERE s.is_active = 1 $dateFilter
                GROUP BY s.id, s.name, s.position, m.id, m.name, c.name
                ORDER BY s.name, จำนวนที่ขาย DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'staff_orders':
            $query = "
                SELECT
                    s.name as 'ชื่อพนักงาน',
                    s.position as 'ตำแหน่ง',
                    COUNT(DISTINCT o.id) as 'จำนวนออเดอร์',
                    SUM(o.total_amount) as 'ยอดขายรวม',
                    ROUND(AVG(o.total_amount), 2) as 'ออเดอร์เฉลี่ย',
                    MIN(o.total_amount) as 'ออเดอร์ต่ำสุด',
                    MAX(o.total_amount) as 'ออเดอร์สูงสุด',
                    ROUND(AVG(order_items_count), 1) as 'รายการเฉลี่ยต่อออเดอร์',
                    COUNT(CASE WHEN o.total_amount >= 500 THEN 1 END) as 'ออเดอร์ใหญ่_500_บาทขึ้นไป',
                    COUNT(CASE WHEN o.total_amount < 100 THEN 1 END) as 'ออเดอร์เล็ก_ต่ำกว่า100บาท',
                    CASE
                        WHEN AVG(o.total_amount) >= 300 THEN '🏆 ขายออเดอร์ใหญ่'
                        WHEN AVG(o.total_amount) >= 200 THEN '⭐ ขายออเดอร์ปานกลาง'
                        WHEN AVG(o.total_amount) >= 100 THEN '👍 ขายออเดอร์เล็ก'
                        ELSE '📊 ออเดอร์ขนาดเล็กมาก'
                    END as 'ประเภทการขาย'
                FROM staff s
                JOIN orders o ON s.id = o.staff_id
                JOIN (
                    SELECT order_id, COUNT(*) as order_items_count
                    FROM order_items
                    GROUP BY order_id
                ) oi_count ON o.id = oi_count.order_id
                WHERE s.is_active = 1 $dateFilter
                GROUP BY s.id, s.name, s.position
                ORDER BY ยอดขายรวม DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'staff_efficiency':
            $query = "
                SELECT
                    s.name as 'ชื่อพนักงาน',
                    s.position as 'ตำแหน่ง',
                    COUNT(DISTINCT DATE(o.order_date)) as 'จำนวนวันที่ทำงาน',
                    COUNT(DISTINCT o.id) as 'จำนวนออเดอร์',
                    SUM(o.total_amount) as 'ยอดขายรวม',
                    ROUND(COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0), 2) as 'ออเดอร์ต่อวัน',
                    ROUND(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0), 2) as 'ยอดขายต่อวัน',
                    ROUND(COUNT(DISTINCT o.id) / (COUNT(DISTINCT DATE(o.order_date)) * 8), 2) as 'ออเดอร์ต่อชั่วโมง',
                    ROUND(SUM(o.total_amount) / (COUNT(DISTINCT DATE(o.order_date)) * 8), 2) as 'ยอดขายต่อชั่วโมง',
                    CASE
                        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 20 THEN '🚀 ประสิทธิภาพสูงมาก'
                        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 15 THEN '⭐ ประสิทธิภาพสูง'
                        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 10 THEN '👍 ประสิทธิภาพปานกลาง'
                        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 5 THEN '📊 ประสิทธิภาพต่ำ'
                        ELSE '🔴 ต้องพัฒนา'
                    END as 'ระดับประสิทธิภาพ'
                FROM staff s
                JOIN orders o ON s.id = o.staff_id
                WHERE s.is_active = 1 $dateFilter
                GROUP BY s.id, s.name, s.position
                ORDER BY ออเดอร์ต่อวัน DESC
            ";
            $finalParams = $dateParams;
            break;

        case 'staff_comparison':
            $query = "
                WITH staff_performance AS (
                    SELECT
                        s.id,
                        s.name,
                        s.position,
                        COUNT(DISTINCT o.id) AS total_orders,
                        SUM(o.total_amount) AS total_sales,
                        ROUND(AVG(o.total_amount), 2) AS avg_order_value
                    FROM staff s
                    JOIN orders o ON s.id = o.staff_id
                    WHERE s.is_active = 1 $dateFilter
                    GROUP BY s.id, s.name, s.position
                ),
                team_averages AS (
                    SELECT
                        AVG(total_orders) AS avg_team_orders,
                        AVG(total_sales) AS avg_team_sales,
                        AVG(avg_order_value) AS avg_team_order_value
                    FROM staff_performance
                )
                SELECT
                    sp.name as 'ชื่อพนักงาน',
                    sp.position as 'ตำแหน่ง',
                    sp.total_orders as 'จำนวนออเดอร์',
                    ROUND(ta.avg_team_orders, 0) as 'ค่าเฉลี่ยทีม_ออเดอร์',
                    ROUND(((sp.total_orders - ta.avg_team_orders) / ta.avg_team_orders) * 100, 1) as 'เปอร์เซ็นต์เปรียบเทียบ_ออเดอร์',
                    sp.total_sales as 'ยอดขายรวม',
                    ROUND(ta.avg_team_sales, 0) as 'ค่าเฉลี่ยทีม_ยอดขาย',
                    ROUND(((sp.total_sales - ta.avg_team_sales) / ta.avg_team_sales) * 100, 1) as 'เปอร์เซ็นต์เปรียบเทียบ_ยอดขาย',
                    sp.avg_order_value as 'ค่าออเดอร์เฉลี่ย',
                    ROUND(ta.avg_team_order_value, 2) as 'ค่าเฉลี่ยทีม_ค่าออเดอร์',
                    CASE
                        WHEN sp.total_sales > ta.avg_team_sales * 1.2 THEN '🏆 เหนือค่าเฉลี่ยมาก'
                        WHEN sp.total_sales > ta.avg_team_sales THEN '⭐ เหนือค่าเฉลี่ย'
                        WHEN sp.total_sales > ta.avg_team_sales * 0.8 THEN '📊 ใกล้ค่าเฉลี่ย'
                        ELSE '📈 ต่ำกว่าค่าเฉลี่ย'
                    END as 'ผลงานเปรียบเทียบ'
                FROM staff_performance sp
                CROSS JOIN team_averages ta
                ORDER BY sp.total_sales DESC
            ";
            $finalParams = $dateParams;
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