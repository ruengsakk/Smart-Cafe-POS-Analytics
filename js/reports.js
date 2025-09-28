// Reports JavaScript
let currentReportType = 'daily_sales';

// SQL Query templates for dynamic updates (for new reports)
const newSqlTemplates = {
    product_inventory: {
        base: `-- รายงานสต็อกสินค้า: วิเคราะห์สินค้าที่ขายดีและคงเหลือ
SELECT m.name AS ชื่อสินค้า, c.name AS หมวดหมู่, m.price AS ราคา,
       COALESCE(SUM(oi.quantity), 0) AS จำนวนที่ขาย,
       COALESCE(SUM(oi.subtotal), 0) AS ยอดขายรวม,
       COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) AS ออเดอร์_7วัน,
       CASE
           WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ไม่มีการขาย'
           WHEN COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) = 0 THEN 'สินค้าค้าง'
           WHEN COALESCE(SUM(oi.quantity), 0) >= 100 THEN 'ขายดีมาก'
           WHEN COALESCE(SUM(oi.quantity), 0) >= 50 THEN 'ขายดีปานกลาง'
           ELSE 'ขายน้อย'
       END AS สถานะสินค้า
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
ORDER BY จำนวนที่ขาย DESC;`
    },
    order_patterns: {
        base: `-- รูปแบบการสั่งซื้อ: วิเคราะห์ขนาดออเดอร์และพฤติกรรมลูกค้า
SELECT CASE
           WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท'
           WHEN total_amount < 200 THEN '100-199 บาท'
           WHEN total_amount < 500 THEN '200-499 บาท'
           WHEN total_amount < 1000 THEN '500-999 บาท'
           ELSE '1000+ บาท'
       END AS ช่วงยอดขาย,
       COUNT(*) AS จำนวนออเดอร์,
       ROUND(AVG(total_amount), 2) AS ยอดขายเฉลี่ย,
       SUM(total_amount) AS ยอดขายรวม,
       AVG(items_count.item_count) AS จำนวนสินค้าเฉลี่ย
FROM orders o
JOIN (SELECT order_id, COUNT(*) AS item_count FROM order_items GROUP BY order_id) items_count ON o.id = items_count.order_id
WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY CASE WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท' WHEN total_amount < 200 THEN '100-199 บาท' WHEN total_amount < 500 THEN '200-499 บาท' WHEN total_amount < 1000 THEN '500-999 บาท' ELSE '1000+ บาท' END
ORDER BY MIN(total_amount);`
    },
    staff_ranking: {
        base: `-- อันดับพนักงาน: ใช้ RANK() และ ROW_NUMBER() Window Functions
SELECT RANK() OVER(ORDER BY total_sales DESC) AS อันดับ,
       staff_name AS ชื่อพนักงาน, position AS ตำแหน่ง,
       total_orders AS จำนวนออเดอร์, total_sales AS ยอดขายรวม,
       avg_order_value AS ยอดขายเฉลี่ยต่อออเดอร์,
       sales_vs_target AS เปรียบเทียบกับเป้าหมาย,
       performance_rating AS ระดับผลงาน,
       วันที่เริ่มงาน, วันล่าสุดขาย
FROM (
    SELECT s.name AS staff_name, s.position,
           COUNT(o.id) AS total_orders,
           COALESCE(SUM(o.total_amount), 0) AS total_sales,
           ROUND(COALESCE(AVG(o.total_amount), 0), 2) AS avg_order_value,
           CONCAT(ROUND((COALESCE(SUM(o.total_amount), 0) / 10000) * 100, 1), '%') AS sales_vs_target,
           CASE
               WHEN COALESCE(SUM(o.total_amount), 0) >= 15000 THEN 'ดีเยี่ยม 🏆'
               WHEN COALESCE(SUM(o.total_amount), 0) >= 10000 THEN 'ดี 🌟'
               WHEN COALESCE(SUM(o.total_amount), 0) >= 5000 THEN 'ปานกลาง 💪'
               ELSE 'ต้องพัฒนา 🚀'
           END AS performance_rating,
           DATE(MIN(o.order_date)) AS วันที่เริ่มงาน,
           DATE(MAX(o.order_date)) AS วันล่าสุดขาย
    FROM staff s
    LEFT JOIN orders o ON s.id = o.staff_id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    WHERE s.is_active = 1
    GROUP BY s.id, s.name, s.position
) staff_stats
ORDER BY total_sales DESC;`
    },
    product_comparison: {
        base: `-- เปรียบเทียบสินค้า: ใช้ PIVOT-like query เพื่อเปรียบเทียบหมวดหมู่
SELECT c.name AS หมวดหมู่,
       COUNT(DISTINCT m.id) AS จำนวนสินค้า,
       COALESCE(SUM(oi.quantity), 0) AS จำนวนที่ขายรวม,
       COALESCE(SUM(oi.subtotal), 0) AS ยอดขายรวม,
       ROUND(COALESCE(AVG(oi.unit_price), 0), 2) AS ราคาเฉลี่ย,
       COUNT(DISTINCT o.id) AS จำนวนออเดอร์,
       CASE
           WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ไม่มีการขาย'
           WHEN COALESCE(SUM(oi.subtotal), 0) >= 5000 THEN 'หมวดหมู่ยอดนิยม 🔥'
           WHEN COALESCE(SUM(oi.subtotal), 0) >= 2000 THEN 'หมวดหมู่ขายดี ⭐'
           ELSE 'หมวดหมู่ขายช้า 📊'
       END AS สถานะหมวดหมู่
FROM categories c
LEFT JOIN menus m ON c.id = m.category_id AND m.is_active = 1
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY c.id, c.name
ORDER BY ยอดขายรวม DESC;`
    },
    order_size_analysis: {
        base: `-- วิเคราะห์ขนาดออเดอร์: การจัดกลุ่มตามจำนวนสินค้าและยอดขาย
SELECT order_size_category AS ประเภทขนาดออเดอร์,
       COUNT(*) AS จำนวนออเดอร์,
       ROUND(AVG(total_items), 1) AS จำนวนสินค้าเฉลี่ย,
       ROUND(AVG(total_amount), 2) AS ยอดขายเฉลี่ย,
       SUM(total_amount) AS ยอดขายรวม,
       MIN(total_amount) AS ยอดขายต่ำสุด,
       MAX(total_amount) AS ยอดขายสูงสุด
FROM (
    SELECT o.id, o.total_amount, SUM(oi.quantity) AS total_items,
           CASE
               WHEN SUM(oi.quantity) = 1 THEN 'ออเดอร์เดี่ยว (1 ชิ้น)'
               WHEN SUM(oi.quantity) <= 3 THEN 'ออเดอร์เล็ก (2-3 ชิ้น)'
               WHEN SUM(oi.quantity) <= 5 THEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)'
               WHEN SUM(oi.quantity) <= 10 THEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)'
               ELSE 'ออเดอร์รายใหญ่ (10+ ชิ้น)'
           END AS order_size_category
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY o.id, o.total_amount
) order_analysis
GROUP BY order_size_category
ORDER BY CASE order_size_category
    WHEN 'ออเดอร์เดี่ยว (1 ชิ้น)' THEN 1
    WHEN 'ออเดอร์เล็ก (2-3 ชิ้น)' THEN 2
    WHEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)' THEN 3
    WHEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)' THEN 4
    ELSE 5
END;`
    },
    product_performance: {
        base: `-- ประสิทธิภาพสินค้า: วิเคราะห์อัตราการหมุนเวียนและผลตอบแทน
SELECT m.name AS ชื่อสินค้า, c.name AS หมวดหมู่, m.price AS ราคา,
       COALESCE(SUM(oi.quantity), 0) AS จำนวนที่ขาย,
       COALESCE(SUM(oi.subtotal), 0) AS ยอดขายรวม,
       ROUND(COALESCE(SUM(oi.subtotal), 0) / NULLIF(SUM(oi.quantity), 0), 2) AS ราคาเฉลี่ยที่ขายได้,
       ROUND(COALESCE(SUM(oi.quantity), 0) / NULLIF(DATEDIFF(CURDATE(), DATE_SUB(CURDATE(), INTERVAL 30 DAY)), 0), 2) AS อัตราการขายต่อวัน
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
ORDER BY ยอดขายรวม DESC;`
    },
    product_trends: {
        base: `-- เทรนด์สินค้า: เปรียบเทียบการขายระหว่างช่วงเวลา
SELECT m.name AS ชื่อสินค้า, c.name AS หมวดหมู่,
       SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) AS ขาย_7วันล่าสุด,
       SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) AS ขาย_7วันก่อน,
       CASE
           WHEN SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) = 0 THEN 'สินค้าใหม่/ไม่มีข้อมูล'
           WHEN SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) > SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) THEN '📈 เพิ่มขึ้น'
           WHEN SUM(CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN oi.quantity ELSE 0 END) < SUM(CASE WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) THEN oi.quantity ELSE 0 END) THEN '📉 ลดลง'
           ELSE '➡️ คงเดิม'
       END AS เทรนด์
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name
HAVING (ขาย_7วันล่าสุด + ขาย_7วันก่อน) > 0
ORDER BY ขาย_7วันล่าสุด DESC;`
    },
    slow_moving_products: {
        base: `-- สินค้าขายช้า: สินค้าที่ขายน้อยหรือไม่มีการขายเลย
SELECT m.name AS ชื่อสินค้า, c.name AS หมวดหมู่, m.price AS ราคา,
       COALESCE(SUM(oi.quantity), 0) AS จำนวนที่ขาย,
       COALESCE(SUM(oi.subtotal), 0) AS ยอดขาย,
       COALESCE(MAX(o.order_date), 'ไม่เคยขาย') AS ขายครั้งล่าสุด,
       CASE
           WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN '🔴 ไม่เคยขาย'
           WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN '🟠 ขายน้อยมาก'
           WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN '🟡 ขายช้า'
           ELSE '🟢 ปกติ'
       END AS สถานะ,
       CASE
           WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ลดราคา, โปรโมชั่น, หรือพิจารณายกเลิก'
           WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN 'สร้างโปรโมชั่น หรือ Bundle กับสินค้าอื่น'
           WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN 'ปรับ Marketing หรือตำแหน่งสินค้า'
           ELSE 'ไม่ต้องดำเนินการ'
       END AS คำแนะนำ
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
HAVING จำนวนที่ขาย <= 5
ORDER BY จำนวนที่ขาย ASC, m.price DESC;`
    },
    peak_hours: {
        base: `-- ช่วงเวลาเร่าซื้อ: วิเคราะห์การขายตามชั่วโมงและวันในสัปดาห์
SELECT HOUR(order_time) AS ชั่วโมง,
       CASE DAYOFWEEK(order_date)
           WHEN 1 THEN 'อาทิตย์' WHEN 2 THEN 'จันทร์' WHEN 3 THEN 'อังคาร'
           WHEN 4 THEN 'พุธ' WHEN 5 THEN 'พฤหัสบดี' WHEN 6 THEN 'ศุกร์' WHEN 7 THEN 'เสาร์'
       END AS วัน,
       COUNT(*) AS จำนวนออเดอร์,
       SUM(total_amount) AS ยอดขายรวม,
       ROUND(AVG(total_amount), 2) AS ยอดขายเฉลี่ย,
       CASE
           WHEN COUNT(*) >= (SELECT AVG(hourly_count) FROM (SELECT HOUR(order_time) as hr, COUNT(*) as hourly_count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY HOUR(order_time)) as avg_calc) * 1.5 THEN '🔥 เร่ามาก'
           WHEN COUNT(*) >= (SELECT AVG(hourly_count) FROM (SELECT HOUR(order_time) as hr, COUNT(*) as hourly_count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY HOUR(order_time)) as avg_calc) THEN '📈 เร่า'
           ELSE '📊 ปกติ'
       END AS สถานะ
FROM orders
WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY HOUR(order_time), DAYOFWEEK(order_date)
ORDER BY วัน, ชั่วโมง;`
    },
    staff_products: {
        base: `-- สินค้าที่พนักงานขาย: ดูว่าพนักงานแต่ละคนขายสินค้าอะไรบ้าง
SELECT s.name AS ชื่อพนักงาน, s.position AS ตำแหน่ง,
       m.name AS ชื่อสินค้า, c.name AS หมวดหมู่,
       SUM(oi.quantity) AS จำนวนที่ขาย,
       SUM(oi.subtotal) AS ยอดขายรวม,
       COUNT(DISTINCT o.id) AS จำนวนออเดอร์,
       ROUND(AVG(oi.unit_price), 2) AS ราคาเฉลี่ย,
       RANK() OVER (PARTITION BY s.id ORDER BY SUM(oi.quantity) DESC) AS อันดับสินค้าขายดี,
       CASE
           WHEN SUM(oi.quantity) >= 50 THEN '⭐ สินค้าเด่น'
           WHEN SUM(oi.quantity) >= 20 THEN '👍 สินค้าขายดี'
           WHEN SUM(oi.quantity) >= 10 THEN '🔵 สินค้าปกติ'
           ELSE '🔴 สินค้าขายน้อย'
       END AS ระดับการขาย
FROM staff s
JOIN orders o ON s.id = o.staff_id
JOIN order_items oi ON o.id = oi.order_id
JOIN menus m ON oi.menu_id = m.id
JOIN categories c ON m.category_id = c.id
WHERE s.is_active = 1 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY s.id, s.name, s.position, m.id, m.name, c.name
ORDER BY s.name, จำนวนที่ขาย DESC;`
    },
    staff_orders: {
        base: `-- ออเดอร์ที่พนักงานรับผิดชอบ: วิเคราะห์ขนาดและรูปแบบออเดอร์
SELECT s.name AS ชื่อพนักงาน, s.position AS ตำแหน่ง,
       COUNT(DISTINCT o.id) AS จำนวนออเดอร์,
       SUM(o.total_amount) AS ยอดขายรวม,
       ROUND(AVG(o.total_amount), 2) AS ออเดอร์เฉลี่ย,
       MIN(o.total_amount) AS ออเดอร์ต่ำสุด,
       MAX(o.total_amount) AS ออเดอร์สูงสุด,
       ROUND(AVG(order_items_count), 1) AS รายการเฉลี่ยต่อออเดอร์,
       COUNT(CASE WHEN o.total_amount >= 500 THEN 1 END) AS ออเดอร์ใหญ่_500_บาทขึ้นไป,
       COUNT(CASE WHEN o.total_amount < 100 THEN 1 END) AS ออเดอร์เล็ก_ต่ำกว่า100บาท,
       CASE
           WHEN AVG(o.total_amount) >= 300 THEN '🏆 ขายออเดอร์ใหญ่'
           WHEN AVG(o.total_amount) >= 200 THEN '⭐ ขายออเดอร์ปานกลาง'
           WHEN AVG(o.total_amount) >= 100 THEN '👍 ขายออเดอร์เล็ก'
           ELSE '📊 ออเดอร์ขนาดเล็กมาก'
       END AS ประเภทการขาย
FROM staff s
JOIN orders o ON s.id = o.staff_id
JOIN (SELECT order_id, COUNT(*) as order_items_count FROM order_items GROUP BY order_id) oi_count ON o.id = oi_count.order_id
WHERE s.is_active = 1 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY s.id, s.name, s.position
ORDER BY ยอดขายรวม DESC;`
    },
    staff_efficiency: {
        base: `-- ประสิทธิภาพพนักงาน: วิเคราะห์ผลิตภาพต่อวันและต่อชั่วโมง
SELECT s.name AS ชื่อพนักงาน, s.position AS ตำแหน่ง,
       COUNT(DISTINCT DATE(o.order_date)) AS จำนวนวันที่ทำงาน,
       COUNT(DISTINCT o.id) AS จำนวนออเดอร์,
       SUM(o.total_amount) AS ยอดขายรวม,
       ROUND(COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0), 2) AS ออเดอร์ต่อวัน,
       ROUND(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0), 2) AS ยอดขายต่อวัน,
       ROUND(COUNT(DISTINCT o.id) / (COUNT(DISTINCT DATE(o.order_date)) * 8), 2) AS ออเดอร์ต่อชั่วโมง,
       ROUND(SUM(o.total_amount) / (COUNT(DISTINCT DATE(o.order_date)) * 8), 2) AS ยอดขายต่อชั่วโมง,
       CASE
           WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 20 THEN '🚀 ประสิทธิภาพสูงมาก'
           WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 15 THEN '⭐ ประสิทธิภาพสูง'
           WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 10 THEN '👍 ประสิทธิภาพปานกลาง'
           WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 5 THEN '📊 ประสิทธิภาพต่ำ'
           ELSE '🔴 ต้องพัฒนา'
       END AS ระดับประสิทธิภาพ
FROM staff s
JOIN orders o ON s.id = o.staff_id
WHERE s.is_active = 1 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY s.id, s.name, s.position
ORDER BY ออเดอร์ต่อวัน DESC;`
    },
    staff_comparison: {
        base: `-- เปรียบเทียบพนักงาน: เปรียบเทียบกับค่าเฉลี่ยของทีม
WITH staff_performance AS (
    SELECT s.id, s.name, s.position,
           COUNT(DISTINCT o.id) AS total_orders,
           SUM(o.total_amount) AS total_sales,
           ROUND(AVG(o.total_amount), 2) AS avg_order_value
    FROM staff s
    JOIN orders o ON s.id = o.staff_id
    WHERE s.is_active = 1 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY s.id, s.name, s.position
),
team_averages AS (
    SELECT AVG(total_orders) AS avg_team_orders,
           AVG(total_sales) AS avg_team_sales,
           AVG(avg_order_value) AS avg_team_order_value
    FROM staff_performance
)
SELECT sp.name AS ชื่อพนักงาน, sp.position AS ตำแหน่ง,
       sp.total_orders AS จำนวนออเดอร์,
       ROUND(ta.avg_team_orders, 0) AS ค่าเฉลี่ยทีม_ออเดอร์,
       ROUND(((sp.total_orders - ta.avg_team_orders) / ta.avg_team_orders) * 100, 1) AS เปอร์เซ็นต์เปรียบเทียบ_ออเดอร์,
       sp.total_sales AS ยอดขายรวม,
       ROUND(ta.avg_team_sales, 0) AS ค่าเฉลี่ยทีม_ยอดขาย,
       ROUND(((sp.total_sales - ta.avg_team_sales) / ta.avg_team_sales) * 100, 1) AS เปอร์เซ็นต์เปรียบเทียบ_ยอดขาย,
       sp.avg_order_value AS ค่าออเดอร์เฉลี่ย,
       ROUND(ta.avg_team_order_value, 2) AS ค่าเฉลี่ยทีม_ค่าออเดอร์,
       CASE
           WHEN sp.total_sales > ta.avg_team_sales * 1.2 THEN '🏆 เหนือค่าเฉลี่ยมาก'
           WHEN sp.total_sales > ta.avg_team_sales THEN '⭐ เหนือค่าเฉลี่ย'
           WHEN sp.total_sales > ta.avg_team_sales * 0.8 THEN '📊 ใกล้ค่าเฉลี่ย'
           ELSE '📈 ต่ำกว่าค่าเฉลี่ย'
       END AS ผลงานเปรียบเทียบ
FROM staff_performance sp
CROSS JOIN team_averages ta
ORDER BY sp.total_sales DESC;`
    }
};

async function loadReport(reportType) {
    console.log('Loading report:', reportType);

    // Validate reportType
    if (!reportType || reportType.trim() === '') {
        console.error('Empty or undefined reportType passed to loadReport');
        showNotification('ประเภทรายงานไม่ถูกต้อง', 'error');
        return;
    }

    currentReportType = reportType;
    const resultContainer = document.getElementById(reportType.replace('_', '-') + '-result');

    // Check if container exists
    if (!resultContainer) {
        console.error('Result container not found for reportType:', reportType);
        showNotification('ไม่พบพื้นที่แสดงผลรายงาน', 'error');
        return;
    }

    // Show loading
    resultContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</div>';

    try {
        // Get date range values
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Build URL with date parameters
        let url = `api/reports.php?type=${reportType}`;
        if (startDate) url += `&start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;

        console.log('API URL:', url);
        const response = await fetch(url);
        const result = await response.json();
        console.log('API Response:', result);

        if (result.success) {
            if (result.data && result.data.length > 0) {
                let tableHTML = '<div class="table-responsive"><table class="table table-striped table-hover">';

                // Table header
                tableHTML += '<thead class="table-dark"><tr>';
                Object.keys(result.data[0]).forEach(key => {
                    tableHTML += `<th>${key}</th>`;
                });
                tableHTML += '</tr></thead>';

                // Table body
                tableHTML += '<tbody>';
                result.data.forEach(row => {
                    tableHTML += '<tr>';
                    Object.values(row).forEach(value => {
                        // Format numbers if they look like currency
                        if (typeof value === 'string' && !isNaN(value) && value.includes('.')) {
                            const num = parseFloat(value);
                            if (num > 0) {
                                value = `฿${num.toFixed(2)}`;
                            }
                        }
                        tableHTML += `<td>${value || '-'}</td>`;
                    });
                    tableHTML += '</tr>';
                });
                tableHTML += '</tbody></table></div>';

                tableHTML += '</div>';

                // Add summary if available
                if (result.summary) {
                    tableHTML += `<div class="alert alert-info mt-3"><strong>สรุป:</strong> ${result.summary}</div>`;
                }

                // Add export button with options
                tableHTML += `
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i> ส่งออกข้อมูล
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header">ไฟล์ Excel</h6></li>
                                <li><a class="dropdown-item" href="#" onclick="exportExcel('${reportType}')">
                                    <i class="fas fa-file-excel text-primary"></i> ส่งออก Excel (.xls) - แนะนำ
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportCSVForExcel('${reportType}')">
                                    <i class="fas fa-file-csv text-success"></i> CSV สำหรับ Excel (UTF-8)
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportCSVThaiExcel('${reportType}')">
                                    <i class="fas fa-file-csv text-warning"></i> CSV แก้ไขภาษาไทย
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">รูปแบบอื่นๆ</h6></li>
                                <li><a class="dropdown-item" href="#" onclick="exportReport('${reportType}')">
                                    <i class="fas fa-file-alt text-info"></i> CSV มาตรฐาน
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="printReport('${reportType}')">
                                    <i class="fas fa-print text-secondary"></i> พิมพ์รายงาน
                                </a></li>
                            </ul>
                        </div>
                        <small class="text-muted">ช่วงเวลา: ${getDateRangeText()}</small>
                    </div>
                `;

                resultContainer.innerHTML = tableHTML;
            } else {
                resultContainer.innerHTML = '<div class="alert alert-warning">ไม่พบข้อมูลสำหรับรายงานนี้</div>';
            }
        } else {
            resultContainer.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาด: ${result.message}</div>`;
        }
    } catch (error) {
        console.error('Error loading report:', error);
        resultContainer.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดรายงาน</div>';
    }
}

// Date range management functions
function setPredefinedRange() {
    const rangeSelect = document.getElementById('predefinedRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const today = new Date();

    let startDate, endDate;

    switch (rangeSelect.value) {
        case 'today':
            startDate = endDate = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = endDate = yesterday.toISOString().split('T')[0];
            break;
        case 'last7days':
            const last7days = new Date(today);
            last7days.setDate(last7days.getDate() - 7);
            startDate = last7days.toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'last30days':
            const last30days = new Date(today);
            last30days.setDate(last30days.getDate() - 30);
            startDate = last30days.toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'thismonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'lastmonth':
            const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = lastMonthStart.toISOString().split('T')[0];
            endDate = lastMonthEnd.toISOString().split('T')[0];
            break;
        case 'thisyear':
            startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        default:
            return; // Custom range, don't change inputs
    }

    startDateInput.value = startDate;
    endDateInput.value = endDate;
}

function updateDateRange() {
    // Reset predefined range to custom when dates are manually changed
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (startDate || endDate) {
        document.getElementById('predefinedRange').value = '';
    }
}

function refreshCurrentReport() {
    if (currentReportType) {
        loadReport(currentReportType);
    }
}

function getDateRangeText() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const predefinedRange = document.getElementById('predefinedRange').value;

    if (predefinedRange) {
        const rangeLabels = {
            'today': 'วันนี้',
            'yesterday': 'เมื่อวาน',
            'last7days': '7 วันล่าสุด',
            'last30days': '30 วันล่าสุด',
            'thismonth': 'เดือนนี้',
            'lastmonth': 'เดือนที่แล้ว',
            'thisyear': 'ปีนี้'
        };
        return rangeLabels[predefinedRange] || 'ไม่ระบุ';
    }

    if (startDate && endDate) {
        return `${startDate} ถึง ${endDate}`;
    } else if (startDate) {
        return `ตั้งแต่ ${startDate}`;
    } else if (endDate) {
        return `ถึง ${endDate}`;
    }

    return '7 วันล่าสุด (ค่าเริ่มต้น)';
}

// Auto-load first report when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range (last 7 days)
    setPredefinedRange();

    // Load first report
    loadReport('daily_sales');

    // Add click events to report tabs
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#', '').replace('-', '_');
            currentReportType = targetId;
            loadReport(targetId);
        });
    });

    // Add explanation popup for learning
    addSqlExplanationFeature();

    // Add copy SQL buttons
    addCopySqlButtons();
});

// Format currency function
function formatCurrency(amount) {
    return new Intl.NumberFormat('th-TH', {
        style: 'currency',
        currency: 'THB'
    }).format(amount);
}

// Export report function with proper Thai encoding
function exportReport(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
        return;
    }

    try {
        // Add BOM for proper Thai encoding in Excel (UTF-8 BOM)
        const BOM = '\uFEFF';
        let csv = BOM;

        // Add report title and timestamp
        const reportTitles = {
            'daily_sales': 'รายงานยอดขายรายวัน',
            'monthly_sales': 'รายงานยอดขายรายเดือน',
            'top_products': 'รายงานสินค้าขายดีที่สุด',
            'customer_analysis': 'รายงานวิเคราะห์ลูกค้า',
            'staff_performance': 'รายงานผลงานพนักงาน',
            'payment_analysis': 'รายงานวิธีการชำระเงิน',
            'hourly_analysis': 'รายงานวิเคราะห์ตามชั่วโมง',
            'product_inventory': 'รายงานสต็อกและสถานะสินค้า',
            'order_patterns': 'รายงานรูปแบบการสั่งซื้อ',
            'staff_ranking': 'รายงานอันดับพนักงาน',
            'product_comparison': 'รายงานเปรียบเทียบสินค้าตามหมวดหมู่',
            'order_size_analysis': 'รายงานวิเคราะห์ขนาดออเดอร์',
            'product_performance': 'รายงานประสิทธิภาพสินค้า',
            'product_trends': 'รายงานเทรนด์สินค้า',
            'slow_moving_products': 'รายงานสินค้าขายช้า',
            'peak_hours': 'รายงานช่วงเวลาเร่าซื้อ',
            'staff_products': 'รายงานสินค้าที่พนักงานขาย',
            'staff_orders': 'รายงานออเดอร์ที่พนักงานรับผิดชอบ',
            'staff_efficiency': 'รายงานประสิทธิภาพพนักงาน',
            'staff_comparison': 'รายงานเปรียบเทียบพนักงาน',
            'advanced_queries': 'รายงาน SQL ขั้นสูง'
        };

        const reportTitle = reportTitles[reportType] || 'รายงาน';
        const currentDate = new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        csv += `"${reportTitle}"\n`;
        csv += `"วันที่ส่งออก: ${currentDate}"\n`;
        csv += `"ช่วงเวลาข้อมูล: ${getDateRangeText()}"\n`;
        csv += `"ระบบ: Coffee Shop Analytics"\n\n`;

        const rows = table.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const cols = row.querySelectorAll('th, td');
            const rowData = Array.from(cols).map(col => {
                let text = col.textContent.trim();

                // Clean up text and handle special characters
                text = text.replace(/"/g, '""'); // Escape quotes
                text = text.replace(/\n/g, ' '); // Replace newlines with spaces
                text = text.replace(/\t/g, ' '); // Replace tabs with spaces

                // Wrap in quotes to preserve formatting
                return `"${text}"`;
            });
            csv += rowData.join(',') + '\n';
        });

        // Create blob with UTF-8 BOM encoding that Excel can properly recognize
        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });

        // Generate safe filename (avoid Thai characters in filename for better compatibility)
        const safeFilename = `${reportType}_${new Date().toISOString().slice(0, 10)}.csv`;

        // Download file
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', safeFilename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Clean up URL object
        URL.revokeObjectURL(url);

        showNotification(`ส่งออกไฟล์ ${safeFilename} เรียบร้อยแล้ว`, 'success');

    } catch (error) {
        console.error('Error exporting CSV:', error);
        showNotification('เกิดข้อผิดพลาดในการส่งออกไฟล์', 'error');
    }
}

// Export CSV specifically formatted for Excel with Thai support
function exportCSVForExcel(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
        return;
    }

    try {
        const reportTitles = {
            'daily_sales': 'รายงานยอดขายรายวัน',
            'monthly_sales': 'รายงานยอดขายรายเดือน',
            'top_products': 'รายงานสินค้าขายดีที่สุด',
            'customer_analysis': 'รายงานวิเคราะห์ลูกค้า',
            'staff_performance': 'รายงานผลงานพนักงาน',
            'payment_analysis': 'รายงานวิธีการชำระเงิน',
            'hourly_analysis': 'รายงานวิเคราะห์ตามชั่วโมง',
            'product_inventory': 'รายงานสต็อกและสถานะสินค้า',
            'order_patterns': 'รายงานรูปแบบการสั่งซื้อ',
            'staff_ranking': 'รายงานอันดับพนักงาน',
            'product_comparison': 'รายงานเปรียบเทียบสินค้าตามหมวดหมู่',
            'order_size_analysis': 'รายงานวิเคราะห์ขนาดออเดอร์',
            'product_performance': 'รายงานประสิทธิภาพสินค้า',
            'product_trends': 'รายงานเทรนด์สินค้า',
            'slow_moving_products': 'รายงานสินค้าขายช้า',
            'peak_hours': 'รายงานช่วงเวลาเร่าซื้อ',
            'staff_products': 'รายงานสินค้าที่พนักงานขาย',
            'staff_orders': 'รายงานออเดอร์ที่พนักงานรับผิดชอบ',
            'staff_efficiency': 'รายงานประสิทธิภาพพนักงาน',
            'staff_comparison': 'รายงานเปรียบเทียบพนักงาน',
            'advanced_queries': 'รายงาน SQL ขั้นสูง'
        };

        const reportTitle = reportTitles[reportType] || 'รายงาน';
        const currentDate = new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Use UTF-8 BOM for Excel compatibility
        const BOM = '\uFEFF';
        let csv = BOM;

        // Add metadata using semicolon separator for better Excel Thai support
        csv += `"${reportTitle}"\n`;
        csv += `"วันที่ส่งออก";"${currentDate}"\n`;
        csv += `"ช่วงเวลาข้อมูล";"${getDateRangeText()}"\n`;
        csv += `"ระบบ";"Coffee Shop Analytics"\n\n`;

        // Get table data
        const rows = table.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const cols = row.querySelectorAll('th, td');
            const rowData = Array.from(cols).map(col => {
                let text = col.textContent.trim();

                // Clean up and escape the text
                text = text.replace(/"/g, '""'); // Escape double quotes
                text = text.replace(/\r?\n/g, ' '); // Remove line breaks
                text = text.replace(/\t/g, ' '); // Remove tabs

                // Wrap all data in quotes for better Excel compatibility with Thai
                return `"${text}"`;
            });
            csv += rowData.join(';') + '\n';
        });

        // Create blob using enhanced UTF-8 approach for maximum Excel compatibility
        // Method 1: Add explicit UTF-8 BOM bytes
        const BOM_BYTES = new Uint8Array([0xEF, 0xBB, 0xBF]);

        // Method 2: Convert content to UTF-8 bytes
        const encoder = new TextEncoder();
        const contentBytes = encoder.encode(csv);

        // Method 3: Combine BOM + content for guaranteed UTF-8 recognition
        const combinedArray = new Uint8Array(BOM_BYTES.length + contentBytes.length);
        combinedArray.set(BOM_BYTES, 0);
        combinedArray.set(contentBytes, BOM_BYTES.length);

        // Create blob with explicit UTF-8 encoding
        const blob = new Blob([combinedArray], {
            type: 'text/csv;charset=utf-8;'
        });

        // Download with .csv extension
        const filename = `${reportType}_excel_utf8_${new Date().toISOString().slice(0, 10)}.csv`;

        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        // Show instructions for Excel
        showExcelImportInstructions(filename);

    } catch (error) {
        console.error('Error exporting CSV for Excel:', error);
        showNotification('เกิดข้อผิดพลาดในการส่งออกไฟล์', 'error');
    }
}

// Show instructions for opening CSV in Excel
function showExcelImportInstructions(filename) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'excelInstructionsModal';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-excel"></i> วิธีการเปิดไฟล์ CSV ใน Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-download"></i> <strong>ไฟล์ ${filename} ถูกดาวน์โหลดแล้ว</strong>
                    </div>

                    <h6 class="text-primary"><i class="fas fa-info-circle"></i> วิธีการเปิดให้ภาษาไทยแสดงผลถูกต้อง:</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">วิธีที่ 1: Import ข้อมูล (แนะนำ)</h6>
                            <ol class="small">
                                <li>เปิด Excel แล้วสร้าง Workbook ใหม่</li>
                                <li>ไปที่ <strong>Data → Get Data → From File → From Text/CSV</strong></li>
                                <li>เลือกไฟล์ ${filename}</li>
                                <li>ที่ <strong>File Origin</strong> เลือก <strong>65001: Unicode (UTF-8)</strong></li>
                                <li>คลิก <strong>Load</strong></li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">วิธีที่ 2: เปิดโดยตรง</h6>
                            <ol class="small">
                                <li>คลิกขวาที่ไฟล์ ${filename}</li>
                                <li>เลือก <strong>Open with → Excel</strong></li>
                                <li>หากภาษาไทยยังเพี้ยน ให้ใช้วิธีที่ 1</li>
                            </ol>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-lightbulb"></i> เคล็ดลับ:</h6>
                        <ul class="mb-0 small">
                            <li>หากยังเพี้ยน ลองใช้ <strong>"ส่งออก Excel (.xls)"</strong> แทน</li>
                            <li>Excel 2019+ รองรับ UTF-8 ได้ดีกว่า</li>
                            <li>Google Sheets เปิดไฟล์ CSV UTF-8 ได้ทันที</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i> เข้าใจแล้ว
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(modal);
    });
}

// Export CSV with proper Thai encoding for Excel (using different approach)
function exportCSVThaiExcel(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
        return;
    }

    try {
        const reportTitles = {
            'daily_sales': 'รายงานยอดขายรายวัน',
            'monthly_sales': 'รายงานยอดขายรายเดือน',
            'top_products': 'รายงานสินค้าขายดีที่สุด',
            'customer_analysis': 'รายงานวิเคราะห์ลูกค้า',
            'staff_performance': 'รายงานผลงานพนักงาน',
            'payment_analysis': 'รายงานวิธีการชำระเงิน',
            'hourly_analysis': 'รายงานวิเคราะห์ตามชั่วโมง',
            'product_inventory': 'รายงานสต็อกและสถานะสินค้า',
            'order_patterns': 'รายงานรูปแบบการสั่งซื้อ',
            'staff_ranking': 'รายงานอันดับพนักงาน',
            'product_comparison': 'รายงานเปรียบเทียบสินค้าตามหมวดหมู่',
            'order_size_analysis': 'รายงานวิเคราะห์ขนาดออเดอร์',
            'product_performance': 'รายงานประสิทธิภาพสินค้า',
            'product_trends': 'รายงานเทรนด์สินค้า',
            'slow_moving_products': 'รายงานสินค้าขายช้า',
            'peak_hours': 'รายงานช่วงเวลาเร่าซื้อ',
            'staff_products': 'รายงานสินค้าที่พนักงานขาย',
            'staff_orders': 'รายงานออเดอร์ที่พนักงานรับผิดชอบ',
            'staff_efficiency': 'รายงานประสิทธิภาพพนักงาน',
            'staff_comparison': 'รายงานเปรียบเทียบพนักงาน',
            'advanced_queries': 'รายงาน SQL ขั้นสูง'
        };

        const reportTitle = reportTitles[reportType] || 'รายงาน';
        const currentDate = new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Create CSV content with proper separators
        let csvContent = '';

        // Add UTF-8 BOM for Excel recognition (enhanced method)
        csvContent = '\uFEFF';

        // Add header information using semicolon separator (better for Thai Excel)
        csvContent += `"${reportTitle}"\n`;
        csvContent += `"วันที่ส่งออก";"${currentDate}"\n`;
        csvContent += `"ช่วงเวลาข้อมูล";"${getDateRangeText()}"\n`;
        csvContent += `"ระบบ";"Coffee Shop Analytics"\n\n`;

        // Process table data
        const rows = table.querySelectorAll('tr');
        rows.forEach((row, rowIndex) => {
            const cols = row.querySelectorAll('th, td');
            const rowData = Array.from(cols).map(col => {
                let text = col.textContent.trim();

                // Clean up and escape the text
                text = text.replace(/"/g, '""'); // Escape double quotes
                text = text.replace(/\r?\n/g, ' '); // Remove line breaks
                text = text.replace(/\t/g, ' '); // Remove tabs

                // Wrap all data in quotes for better Excel compatibility
                return `"${text}"`;
            });

            // Use semicolon as separator for better Thai support in Excel
            csvContent += rowData.join(';') + '\n';
        });

        // Create blob using multiple approaches for maximum compatibility
        // Method 1: Add explicit UTF-8 BOM bytes
        const BOM = new Uint8Array([0xEF, 0xBB, 0xBF]);

        // Method 2: Convert content to UTF-8 bytes
        const encoder = new TextEncoder();
        const contentBytes = encoder.encode(csvContent);

        // Method 3: Combine BOM + content for guaranteed UTF-8 recognition
        const combinedArray = new Uint8Array(BOM.length + contentBytes.length);
        combinedArray.set(BOM, 0);
        combinedArray.set(contentBytes, BOM.length);

        // Create blob with explicit UTF-8 encoding
        const blob = new Blob([combinedArray], {
            type: 'text/csv;charset=utf-8;'
        });

        // Download file
        const filename = `${reportType}_thai_fixed_${new Date().toISOString().slice(0, 10)}.csv`;

        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        // Show specific instructions for this method
        showThaiCSVInstructions(filename, reportType);

    } catch (error) {
        console.error('Error exporting Thai CSV:', error);
        showNotification('เกิดข้อผิดพลาดในการส่งออกไฟล์', 'error');
    }
}

// Show specific instructions for Thai CSV
function showThaiCSVInstructions(filename, reportType) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'thaiCSVModal';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-language"></i> ไฟล์ CSV แก้ไขปัญหาภาษาไทย
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-download"></i> <strong>ไฟล์ ${filename} ถูกดาวน์โหลดแล้ว</strong>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> ไฟล์นี้ได้รับการปรับแต่งพิเศษ:</h6>
                        <ul class="mb-0">
                            <li>ใช้ <strong>UTF-8 BOM</strong> สำหรับ Excel</li>
                            <li>ใช้ <strong>เครื่องหมาย ; (semicolon)</strong> แทน comma</li>
                            <li>เพิ่ม <strong>TextEncoder</strong> สำหรับ encoding ที่ถูกต้อง</li>
                        </ul>
                    </div>

                    <h6 class="text-success"><i class="fas fa-check-circle"></i> วิธีเปิดใน Excel:</h6>
                    <ol>
                        <li><strong>ดับเบิลคลิก</strong>ที่ไฟล์ ${filename}</li>
                        <li>หาก Excel เปิดขึ้นมาแล้วภาษาไทยยังเพี้ยน:</li>
                        <ul>
                            <li>ปิด Excel</li>
                            <li>เปิด Excel ใหม่ → <strong>File → Open</strong></li>
                            <li>เลือกไฟล์ → ในกล่อง <strong>Encoding</strong> เลือก <strong>UTF-8</strong></li>
                            <li>ในส่วน <strong>Delimiter</strong> เลือก <strong>Semicolon (;)</strong></li>
                        </ul>
                    </ol>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> หากยังมีปัญหา:</h6>
                        <p class="mb-2">ใช้ <strong>"ส่งออก Excel (.xls)"</strong> แทน จะไม่มีปัญหาภาษาไทยแน่นอน</p>
                        <button class="btn btn-primary btn-sm" onclick="exportExcel('${reportType.replace('_thai_fixed', '')}'); bootstrap.Modal.getInstance(document.getElementById('thaiCSVModal')).hide();">
                            <i class="fas fa-file-excel"></i> ส่งออก Excel แทน
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i> เข้าใจแล้ว
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(modal);
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : type === 'success' ? 'success' : 'info'} notification-popup`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;

    const icon = type === 'success' ? '✅' : type === 'warning' ? '⚠️' : type === 'error' ? '❌' : 'ℹ️';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="me-2" style="font-size: 1.2rem;">${icon}</span>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Export to Excel format (actually HTML that Excel can read)
function exportExcel(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
        return;
    }

    try {
        const reportTitles = {
            'daily_sales': 'รายงานยอดขายรายวัน',
            'monthly_sales': 'รายงานยอดขายรายเดือน',
            'top_products': 'รายงานสินค้าขายดีที่สุด',
            'customer_analysis': 'รายงานวิเคราะห์ลูกค้า',
            'staff_performance': 'รายงานผลงานพนักงาน',
            'payment_analysis': 'รายงานวิธีการชำระเงิน',
            'hourly_analysis': 'รายงานวิเคราะห์ตามชั่วโมง',
            'product_inventory': 'รายงานสต็อกและสถานะสินค้า',
            'order_patterns': 'รายงานรูปแบบการสั่งซื้อ',
            'staff_ranking': 'รายงานอันดับพนักงาน',
            'product_comparison': 'รายงานเปรียบเทียบสินค้าตามหมวดหมู่',
            'order_size_analysis': 'รายงานวิเคราะห์ขนาดออเดอร์',
            'product_performance': 'รายงานประสิทธิภาพสินค้า',
            'product_trends': 'รายงานเทรนด์สินค้า',
            'slow_moving_products': 'รายงานสินค้าขายช้า',
            'peak_hours': 'รายงานช่วงเวลาเร่าซื้อ',
            'staff_products': 'รายงานสินค้าที่พนักงานขาย',
            'staff_orders': 'รายงานออเดอร์ที่พนักงานรับผิดชอบ',
            'staff_efficiency': 'รายงานประสิทธิภาพพนักงาน',
            'staff_comparison': 'รายงานเปรียบเทียบพนักงาน',
            'advanced_queries': 'รายงาน SQL ขั้นสูง'
        };

        const reportTitle = reportTitles[reportType] || 'รายงาน';
        const currentDate = new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Create HTML content for Excel
        let htmlContent = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <meta name="ProgId" content="Excel.Sheet">
                <meta name="Generator" content="Coffee Shop Analytics">
            </head>
            <body>
                <div style="text-align: center; margin: 20px 0;">
                    <h2>${reportTitle}</h2>
                    <p>วันที่ส่งออก: ${currentDate}</p>
                    <p>ช่วงเวลาข้อมูล: ${getDateRangeText()}</p>
                    <p>ระบบ: Coffee Shop Analytics</p>
                </div>
                ${table.outerHTML}
            </body>
            </html>
        `;

        // Create blob and download
        const blob = new Blob([htmlContent], {
            type: 'application/vnd.ms-excel;charset=utf-8;'
        });

        const filename = `${reportTitle}_${new Date().toISOString().slice(0, 10)}.xls`;

        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showNotification(`ส่งออกไฟล์ Excel ${filename} เรียบร้อยแล้ว`, 'success');

    } catch (error) {
        console.error('Error exporting Excel:', error);
        showNotification('เกิดข้อผิดพลาดในการส่งออกไฟล์ Excel', 'error');
    }
}

// Print report function
function printReport(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        showNotification('ไม่มีข้อมูลให้พิมพ์', 'warning');
        return;
    }

    try {
        const reportTitles = {
            'daily_sales': 'รายงานยอดขายรายวัน',
            'monthly_sales': 'รายงานยอดขายรายเดือน',
            'top_products': 'รายงานสินค้าขายดีที่สุด',
            'customer_analysis': 'รายงานวิเคราะห์ลูกค้า',
            'staff_performance': 'รายงานผลงานพนักงาน',
            'payment_analysis': 'รายงานวิธีการชำระเงิน',
            'hourly_analysis': 'รายงานวิเคราะห์ตามชั่วโมง',
            'product_inventory': 'รายงานสต็อกและสถานะสินค้า',
            'order_patterns': 'รายงานรูปแบบการสั่งซื้อ',
            'staff_ranking': 'รายงานอันดับพนักงาน',
            'product_comparison': 'รายงานเปรียบเทียบสินค้าตามหมวดหมู่',
            'order_size_analysis': 'รายงานวิเคราะห์ขนาดออเดอร์',
            'product_performance': 'รายงานประสิทธิภาพสินค้า',
            'product_trends': 'รายงานเทรนด์สินค้า',
            'slow_moving_products': 'รายงานสินค้าขายช้า',
            'peak_hours': 'รายงานช่วงเวลาเร่าซื้อ',
            'staff_products': 'รายงานสินค้าที่พนักงานขาย',
            'staff_orders': 'รายงานออเดอร์ที่พนักงานรับผิดชอบ',
            'staff_efficiency': 'รายงานประสิทธิภาพพนักงาน',
            'staff_comparison': 'รายงานเปรียบเทียบพนักงาน',
            'advanced_queries': 'รายงาน SQL ขั้นสูง'
        };

        const reportTitle = reportTitles[reportType] || 'รายงาน';
        const currentDate = new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Create new window for printing
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>${reportTitle}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { font-size: 12pt; }
                        table { width: 100% !important; }
                        th, td { padding: 6px !important; font-size: 11pt; }
                        .table-striped tbody tr:nth-of-type(odd) {
                            background-color: #f8f9fa !important;
                        }
                    }
                    body {
                        font-family: 'Sarabun', 'Arial', sans-serif;
                        margin: 20px;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #dee2e6;
                        padding-bottom: 20px;
                    }
                    .header h1 {
                        color: #495057;
                        margin-bottom: 10px;
                    }
                    .header p {
                        color: #6c757d;
                        margin: 5px 0;
                    }
                    table {
                        width: 100%;
                        margin-top: 20px;
                    }
                    th {
                        background-color: #e9ecef !important;
                        font-weight: bold;
                        text-align: center;
                    }
                    td {
                        text-align: center;
                    }
                    .footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 11pt;
                        color: #6c757d;
                        border-top: 1px solid #dee2e6;
                        padding-top: 15px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>${reportTitle}</h1>
                    <p><strong>วันที่ส่งออก:</strong> ${currentDate}</p>
                    <p><strong>ช่วงเวลาข้อมูล:</strong> ${getDateRangeText()}</p>
                    <p><strong>ระบบ:</strong> Coffee Shop Analytics</p>
                </div>

                ${table.outerHTML}

                <div class="footer">
                    <p>รายงานนี้ถูกสร้างโดยระบบ Coffee Shop Analytics</p>
                    <p>พิมพ์เมื่อ: ${new Date().toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    })}</p>
                </div>

                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        };
                    };
                </script>
            </body>
            </html>
        `);

        printWindow.document.close();
        showNotification('เปิดหน้าต่างพิมพ์แล้ว', 'success');

    } catch (error) {
        console.error('Error printing report:', error);
        showNotification('เกิดข้อผิดพลาดในการพิมพ์รายงาน', 'error');
    }
}

// Add syntax highlighting for better learning experience
function highlightSqlSyntax() {
    document.querySelectorAll('.sql-dynamic').forEach(container => {
        // This function can be extended to add more sophisticated highlighting
        // For now, the HTML templates already include syntax highlighting classes
    });
}

// Function to copy SQL to clipboard
function copySqlToClipboard(reportType) {
    const sqlContainer = document.getElementById(`${reportType.replace('_', '-')}-sql`);
    if (sqlContainer) {
        const sqlText = sqlContainer.textContent;
        navigator.clipboard.writeText(sqlText).then(() => {
            // Show success notification
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4caf50;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s;
            `;
            notification.textContent = 'คัดลอก SQL แล้ว!';
            document.body.appendChild(notification);

            setTimeout(() => notification.style.opacity = '1', 100);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 2000);
        });
    }
}

// Add copy SQL button functionality
function addCopySqlButtons() {
    Object.keys(sqlTemplates).forEach(reportType => {
        const sqlContainer = document.getElementById(`${reportType.replace('_', '-')}-sql`);
        if (sqlContainer && !sqlContainer.nextElementSibling?.classList.contains('sql-controls')) {
            const controls = document.createElement('div');
            controls.className = 'sql-controls mt-2';
            controls.innerHTML = `
                <button class="btn btn-outline-secondary btn-sm me-2" onclick="copySqlToClipboard('${reportType}')">
                    <i class="fas fa-copy"></i> คัดลอก SQL
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="showSqlExplanation('${reportType}')">
                    <i class="fas fa-question-circle"></i> อธิบาย SQL
                </button>
            `;
            sqlContainer.parentNode.insertBefore(controls, sqlContainer.nextSibling);
        }
    });
}