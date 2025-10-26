# สรุปคำสั่ง SQL สำหรับรายงาน Coffee Shop Analytics

ไฟล์นี้รวบรวมคำสั่ง SQL ที่ใช้ใน API `api/reports.php` เพื่อสร้างรายงานแต่ละรูปแบบในระบบ Coffee Shop Analytics ช่วยให้คุณสามารถศึกษาแนวคิดการสรุปข้อมูล การใช้ฟังก์ชัน Aggregate, Window Function, CTE และการจัดกลุ่มข้อมูลได้อย่างเป็นระบบ

> หมายเหตุ  
> - โค้ดหลายส่วนใช้ตัวแปรภายใน PHP เช่น `$dateFilter` เพื่อปรับช่วงเวลาอัตโนมัติ โดยดีฟอลต์จะครอบคลุม 7 วันที่ผ่านมา  
> - หากรันคำสั่งในเครื่องมือฐานข้อมูล ให้แทนที่คอมเมนต์ `-- $dateFilter` ด้วยเงื่อนไขวันที่ที่ต้องการ หรือเอาออกได้เมื่อไม่ต้องการกรอง  
> - รายงานบางประเภทไม่ต้องใช้ตัวกรองวันที่ เช่น การวิเคราะห์ลูกค้า

---

## วิธีใช้ไฟล์นี้เพื่อศึกษา

1. อ่านคำอธิบายสั้นก่อนดูคำสั่ง เพื่อเข้าใจโจทย์และผลลัพธ์ที่คาดหวัง  
2. สังเกตหัวข้อ “แนวคิดหลัก” เพื่อโฟกัสฟังก์ชัน/โครงสร้าง SQL สำคัญในแต่ละตัวอย่าง  
3. ทดลองปรับแก้คำสั่ง (เพิ่มเงื่อนไข, เปลี่ยนการจัดกลุ่ม, ตัดคอลัมน์) แล้วรันใน phpMyAdmin หรือ MySQL CLI เพื่อเห็นผลลัพธ์ที่ต่างกัน  
4. หลังรันเสร็จ ลองเขียนคำอธิบายผลลัพธ์ด้วยตัวเอง เพื่อเตรียมการสอบหรือการนำเสนอ

---

## รายงานยอดขายรายวัน (`daily_sales`)

คำสั่งนี้สรุปยอดขายตามแต่ละวัน เพื่อเช็กจำนวนออเดอร์ ยอดขายรวม ค่าเฉลี่ย รวมถึงค่าสูงสุดต่ำสุด ช่วยดูแนวโน้มรายวันและหาวันที่พีคหรือเงียบผิดปกติ

- แนวคิดหลัก: `GROUP BY` วันที่, ฟังก์ชันรวม (`COUNT`, `SUM`, `AVG`, `MIN`, `MAX`)
- แบบฝึก: ทดลองเปลี่ยนช่วงวันที่ หรือเพิ่มเงื่อนไข `HAVING SUM(total_amount) > ...`

```sql
SELECT
    DATE(order_date) AS 'วันที่',
    COUNT(*) AS 'จำนวนออเดอร์',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย',
    MIN(total_amount) AS 'ยอดขายต่ำสุด',
    MAX(total_amount) AS 'ยอดขายสูงสุด'
FROM orders
WHERE 1=1
    -- $dateFilter
GROUP BY DATE(order_date)
ORDER BY order_date DESC;
```

## รายงานยอดขายรายเดือน (`monthly_sales`)

ใช้ดูภาพรวมเชิงเดือน นับจำนวนออเดอร์และยอดขายในแต่ละเดือน เพื่อเปรียบเทียบฤดูกาลหรือวิเคราะห์การเติบโตต่อเนื่อง

- แนวคิดหลัก: การใช้ฟังก์ชันวันที่ (`YEAR`, `MONTH`, `MONTHNAME`) และการจัดกลุ่มหลายระดับ
- แบบฝึก: เพิ่ม `ORDER BY MONTHNAME(order_date)` หรือรวมฟิลด์ `payment_type` เพื่อลงรายละเอียด

```sql
SELECT
    YEAR(order_date) AS 'ปี',
    MONTH(order_date) AS 'เดือน',
    MONTHNAME(order_date) AS 'ชื่อเดือน',
    COUNT(*) AS 'จำนวนออเดอร์',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย'
FROM orders
WHERE 1=1
    -- $dateFilter
GROUP BY YEAR(order_date), MONTH(order_date)
ORDER BY YEAR(order_date) DESC, MONTH(order_date) DESC;
```

## รายงานสินค้าขายดี (`top_products`)

ดึงรายการสินค้าที่ขายดีที่สุด 10 อันดับ โดยดูจำนวนชิ้น ยอดขายรวม และราคาเฉลี่ย เพื่อโฟกัสสินค้าที่ได้รับความนิยม

- แนวคิดหลัก: การเชื่อมตาราง (`JOIN`) หลายตารางพร้อมกัน และใช้ `LIMIT`
- แบบฝึก: ลองเปลี่ยนลำดับ `ORDER BY SUM(oi.subtotal)` หรือเพิ่ม `HAVING COUNT(DISTINCT o.id) >= 3`

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    SUM(oi.quantity) AS 'จำนวนที่ขาย',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    SUM(oi.subtotal) AS 'ยอดขายรวม',
    ROUND(AVG(oi.unit_price), 2) AS 'ราคาเฉลี่ย'
FROM order_items oi
JOIN menus m ON oi.menu_id = m.id
JOIN categories c ON m.category_id = c.id
JOIN orders o ON oi.order_id = o.id
WHERE 1=1
    -- $dateFilter
GROUP BY m.id, m.name, c.name
ORDER BY SUM(oi.quantity) DESC
LIMIT 10;
```

## วิเคราะห์ลูกค้า (`customer_analysis`)

แสดงข้อมูลลูกค้าและระดับสมาชิก พร้อมยอดซื้อสะสม แต้ม ค่าเฉลี่ยการกลับมา เพื่อระบุลูกค้ากลุ่ม VIP และลูกค้าที่อาจต้องกระตุ้น

- แนวคิดหลัก: `LEFT JOIN` กับซับคิวรี และ `CASE WHEN` เพื่อสร้างระดับสมาชิก
- แบบฝึก: เพิ่มคอลัมน์ `DATEDIFF(CURDATE(), last_order)` เพื่อดูจำนวนวันที่ไม่ได้มาใช้บริการ

```sql
SELECT
    c.name AS 'ชื่อลูกค้า',
    c.phone AS 'เบอร์โทร',
    c.points AS 'แต้มสะสม',
    c.total_spent AS 'ยอดซื้อสะสม',
    c.visit_count AS 'จำนวนครั้งที่มา',
    COALESCE(recent_orders.last_order, 'ไม่เคยสั่ง') AS 'ออเดอร์ล่าสุด',
    CASE
        WHEN c.total_spent >= 5000 THEN 'VIP'
        WHEN c.total_spent >= 2000 THEN 'Gold'
        WHEN c.total_spent >= 1000 THEN 'Silver'
        ELSE 'Bronze'
    END AS 'ระดับสมาชิก'
FROM customers c
LEFT JOIN (
    SELECT
        customer_id,
        MAX(order_date) AS last_order
    FROM orders
    WHERE customer_id IS NOT NULL
    GROUP BY customer_id
) recent_orders ON c.id = recent_orders.customer_id
WHERE c.is_active = 1
ORDER BY c.total_spent DESC;
```

## ผลงานพนักงาน (`staff_performance`)

วัดผลงานพนักงานแต่ละคนจากจำนวนออเดอร์ ยอดขายรวม ค่าเฉลี่ยต่อออเดอร์ รวมถึงช่วงเวลาทำการขาย เพื่อประเมินประสิทธิภาพรายบุคคล

- แนวคิดหลัก: `INNER JOIN`, `GROUP BY` พนักงาน, ฟังก์ชันสถิติพื้นฐาน
- แบบฝึก: เพิ่ม `ORDER BY COUNT(o.id) DESC` หรือเพิ่ม `HAVING COUNT(o.id) >= 5`

```sql
SELECT
    s.name AS 'ชื่อพนักงาน',
    s.position AS 'ตำแหน่ง',
    COUNT(o.id) AS 'จำนวนออเดอร์',
    SUM(o.total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(o.total_amount), 2) AS 'ยอดขายเฉลี่ย',
    ROUND(SUM(o.total_amount) / COUNT(o.id), 2) AS 'ยอดขายต่อออเดอร์',
    MIN(o.order_date) AS 'วันแรกที่ขาย',
    MAX(o.order_date) AS 'วันล่าสุดที่ขาย'
FROM staff s
INNER JOIN orders o ON s.id = o.staff_id
WHERE 1=1
    -- $dateFilter
GROUP BY s.id, s.name, s.position
ORDER BY SUM(o.total_amount) DESC;
```

## วิเคราะห์วิธีชำระเงิน (`payment_analysis`)

แบ่งยอดขายตามวิธีชำระเงิน เพื่อตรวจสอบว่าวิธีไหนได้รับความนิยม รู้สัดส่วนเงินสด เทียบกับ QR หรือช่องทางออนไลน์

- แนวคิดหลัก: `CASE` เพื่อเปลี่ยนรหัสเป็นข้อความ และการจัดกลุ่มตามชนิดการชำระเงิน
- แบบฝึก: ลองเพิ่ม `GROUP BY payment_type, DATE(order_date)` เพื่อดูสัดส่วนรายวัน

```sql
SELECT
    CASE
        WHEN payment_type = 'cash' THEN 'เงินสด'
        WHEN payment_type = 'qr' THEN 'QR Code'
        WHEN payment_type = 'online' THEN 'Online Payment'
        ELSE payment_type
    END AS 'วิธีการชำระเงิน',
    COUNT(*) AS 'จำนวนออเดอร์',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย'
FROM orders
WHERE 1=1
    -- $dateFilter
GROUP BY payment_type
ORDER BY COUNT(*) DESC;
```

## วิเคราะห์ตามช่วงชั่วโมง (`hourly_analysis`)

สรุปยอดขายตามชั่วโมงและช่วงเวลา (เช้า เที่ยง บ่าย เย็น) ช่วยระบุช่วงเวลาที่ลูกค้าเยอะเพื่อวางแผนพนักงานและโปรโมชั่น

- แนวคิดหลัก: ฟังก์ชันเวลา (`HOUR`, `CASE`) และการจัดกลุ่มตามช่วง
- แบบฝึก: ลองใช้ `DATE_FORMAT(order_time, '%H:00')` เพื่อสร้างคอลัมน์รูปแบบเวลาใหม่

```sql
SELECT
    HOUR(order_time) AS 'ชั่วโมง',
    CASE
        WHEN HOUR(order_time) BETWEEN 6 AND 10 THEN 'ช่วงเช้า'
        WHEN HOUR(order_time) BETWEEN 11 AND 14 THEN 'ช่วงเที่ยง'
        WHEN HOUR(order_time) BETWEEN 15 AND 18 THEN 'ช่วงบ่าย'
        WHEN HOUR(order_time) BETWEEN 19 AND 22 THEN 'ช่วงเย็น'
        ELSE 'ช่วงพิเศษ'
    END AS 'ช่วงเวลา',
    COUNT(*) AS 'จำนวนออเดอร์',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย'
FROM orders
WHERE 1=1
    -- $dateFilter
GROUP BY HOUR(order_time)
ORDER BY HOUR(order_time);
```

## รายงานสต็อกสินค้า (`product_inventory`)

ผูกข้อมูลยอดขายล่าสุดกับสินค้าปัจจุบันเพื่อประเมินสถานะว่า “ขายดี” หรือ “สินค้าค้าง” และนับจำนวนออเดอร์ใน 7 วันหลังสุด

- แนวคิดหลัก: `LEFT JOIN` เพื่อคงสินค้าทั้งหมด, ฟังก์ชัน `COALESCE`, การใช้ `CASE` ซ้อน
- แบบฝึก: แก้ช่วงเวลาเป็น 14 วัน หรือเพิ่ม `ORDER BY สถานะสินค้า`

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    m.price AS 'ราคา',
    COALESCE(SUM(oi.quantity), 0) AS 'จำนวนที่ขาย',
    COALESCE(SUM(oi.subtotal), 0) AS 'ยอดขายรวม',
    COUNT(
        DISTINCT CASE
            WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            THEN o.id
        END
    ) AS 'ออเดอร์_7วัน',
    CASE
        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ไม่มีการขาย'
        WHEN COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) = 0 THEN 'สินค้าค้าง'
        WHEN COALESCE(SUM(oi.quantity), 0) >= 100 THEN 'ขายดีมาก'
        WHEN COALESCE(SUM(oi.quantity), 0) >= 50 THEN 'ขายดีปานกลาง'
        ELSE 'ขายน้อย'
    END AS 'สถานะสินค้า'
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
    -- $dateFilter
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
ORDER BY 'จำนวนที่ขาย' DESC;
```

## รูปแบบการสั่งซื้อ (`order_patterns`)

จัดกลุ่มออเดอร์ตามช่วงยอดขาย (ต่ำกว่า 100, 100-199 ฯลฯ) เพื่อดูพฤติกรรมการใช้จ่ายของลูกค้าและสัดส่วนแต่ละช่วงราคา

- แนวคิดหลัก: การจัดกลุ่มด้วย `CASE` และใช้อัตราส่วน (`COUNT(*) * 100 / ...`)
- แบบฝึก: เพิ่มช่วงราคาตามต้องการ หรือสร้างมุมมอง (VIEW) เพื่อใช้ซ้ำ

```sql
SELECT
    CASE
        WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท'
        WHEN total_amount < 200 THEN '100-199 บาท'
        WHEN total_amount < 500 THEN '200-499 บาท'
        WHEN total_amount < 1000 THEN '500-999 บาท'
        ELSE '1000+ บาท'
    END AS 'ช่วงยอดขาย',
    COUNT(*) AS 'จำนวนออเดอร์',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(
        (COUNT(*) * 100.0 /
            (SELECT COUNT(*) FROM orders WHERE 1=1 -- $dateFilter)),
        2
    ) AS 'เปอร์เซ็นต์',
    AVG(items_count.item_count) AS 'จำนวนสินค้าเฉลี่ย'
FROM orders o
JOIN (
    SELECT order_id, COUNT(*) AS item_count
    FROM order_items
    GROUP BY order_id
) items_count ON o.id = items_count.order_id
WHERE 1=1
    -- $dateFilter
GROUP BY CASE
    WHEN total_amount < 100 THEN 'น้อยกว่า 100 บาท'
    WHEN total_amount < 200 THEN '100-199 บาท'
    WHEN total_amount < 500 THEN '200-499 บาท'
    WHEN total_amount < 1000 THEN '500-999 บาท'
    ELSE '1000+ บาท'
END
ORDER BY MIN(total_amount);
```

## จัดอันดับพนักงาน (`staff_ranking`)

นำยอดขายรวมมาเรียงอันดับพนักงาน พร้อมจำนวนออเดอร์ ช่วยมองเห็น Top performer และใช้ประกาศรางวัลหรือกระตุ้นทีม

- แนวคิดหลัก: Window Function `RANK()` และซับคิวรี
- แบบฝึก: เปลี่ยนเป็น `DENSE_RANK()` หรือเพิ่มคอลัมน์ `ROW_NUMBER()`

```sql
SELECT
    RANK() OVER (ORDER BY total_sales DESC) AS 'อันดับ',
    staff_name AS 'ชื่อพนักงาน',
    position AS 'ตำแหน่ง',
    total_orders AS 'จำนวนออเดอร์',
    total_sales AS 'ยอดขายรวม'
FROM (
    SELECT
        s.name AS staff_name,
        s.position,
        COUNT(o.id) AS total_orders,
        COALESCE(SUM(o.total_amount), 0) AS total_sales
    FROM staff s
    LEFT JOIN orders o ON s.id = o.staff_id
        -- $dateFilter
    WHERE s.is_active = 1
    GROUP BY s.id, s.name, s.position
) staff_stats
ORDER BY total_sales DESC;
```

## เปรียบเทียบยอดขายตามหมวดหมู่ (`product_comparison`)

รวมยอดขายและจำนวนสินค้าต่อหมวด ช่วยเทียบหมวดไหนทำเงินหรือมีจำนวนสินค้าเยอะ เพื่อจัดการเมนูและสต็อกได้ดีขึ้น

- แนวคิดหลัก: `LEFT JOIN` + `GROUP BY` หมวดหมู่ เพื่อไม่ให้หมวดว่างหายไป
- แบบฝึก: เติม `AVG(oi.unit_price)` หรือ `COUNT(DISTINCT o.id)`

```sql
SELECT
    c.name AS 'หมวดหมู่',
    COUNT(DISTINCT m.id) AS 'จำนวนสินค้า',
    COALESCE(SUM(oi.quantity), 0) AS 'จำนวนที่ขายรวม',
    COALESCE(SUM(oi.subtotal), 0) AS 'ยอดขายรวม'
FROM categories c
LEFT JOIN menus m ON c.id = m.category_id AND m.is_active = 1
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
    -- $dateFilter
GROUP BY c.id, c.name
ORDER BY 'ยอดขายรวม' DESC;
```

## วิเคราะห์ขนาดออเดอร์ (`order_size_analysis`)

แบ่งออเดอร์ตามจำนวนสินค้าที่ซื้อในแต่ละครั้ง เพื่อดูว่าลูกค้าส่วนใหญ่ซื้อกี่ชิ้น รวมถึงสัดส่วนยอดขายที่แต่ละขนาดสร้างได้

- แนวคิดหลัก: ซับคิวรีเพื่อคำนวณจำนวนสินค้า และการเรียงลำดับด้วย `CASE`
- แบบฝึก: เพิ่มคอลัมน์ `MAX(total_items)` หรือปรับช่วงขนาดออเดอร์เอง

```sql
SELECT
    order_size_category AS 'ประเภทขนาดออเดอร์',
    COUNT(*) AS 'จำนวนออเดอร์',
    ROUND(AVG(total_items), 1) AS 'จำนวนสินค้าเฉลี่ย',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(
        (COUNT(*) * 100.0 /
            (SELECT COUNT(*) FROM orders WHERE 1=1 -- $dateFilter)),
        2
    ) AS 'เปอร์เซ็นต์ออเดอร์',
    ROUND(
        (SUM(total_amount) * 100.0 /
            (SELECT SUM(total_amount) FROM orders WHERE 1=1 -- $dateFilter)),
        2
    ) AS 'เปอร์เซ็นต์ยอดขาย',
    MIN(total_amount) AS 'ยอดขายต่ำสุด',
    MAX(total_amount) AS 'ยอดขายสูงสุด'
FROM (
    SELECT
        o.id,
        o.total_amount,
        SUM(oi.quantity) AS total_items,
        CASE
            WHEN SUM(oi.quantity) = 1 THEN 'ออเดอร์เดี่ยว (1 ชิ้น)'
            WHEN SUM(oi.quantity) <= 3 THEN 'ออเดอร์เล็ก (2-3 ชิ้น)'
            WHEN SUM(oi.quantity) <= 5 THEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)'
            WHEN SUM(oi.quantity) <= 10 THEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)'
            ELSE 'ออเดอร์รายใหญ่ (10+ ชิ้น)'
        END AS order_size_category
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE 1=1
        -- $dateFilter
    GROUP BY o.id, o.total_amount
) order_analysis
GROUP BY order_size_category
ORDER BY CASE order_size_category
    WHEN 'ออเดอร์เดี่ยว (1 ชิ้น)' THEN 1
    WHEN 'ออเดอร์เล็ก (2-3 ชิ้น)' THEN 2
    WHEN 'ออเดอร์ปานกลาง (4-5 ชิ้น)' THEN 3
    WHEN 'ออเดอร์ใหญ่ (6-10 ชิ้น)' THEN 4
    ELSE 5
END;
```

## ตัวอย่าง Advanced Query (`advanced_queries`)

ยกตัวอย่างการใช้ซับคิวรีคำนวณค่าเฉลี่ยยอดซื้อของลูกค้าทั้งระบบ แล้วดึงเฉพาะลูกค้าที่มียอดสูงกว่าค่าเฉลี่ยเพื่อทำแคมเปญพิเศษ

- แนวคิดหลัก: ซับคิวรีในส่วน `SELECT` และ `WHERE` เพื่ออ้างอิงค่าเฉลี่ยทั้งระบบ
- แบบฝึก: เปลี่ยนเงื่อนไขให้ดึงลูกค้าที่ต่ำกว่าค่าเฉลี่ย หรือสร้าง `VIEW` ชื่อ `vip_customers`

```sql
SELECT
    c.name AS 'ชื่อลูกค้า',
    c.total_spent AS 'ยอดซื้อสะสม',
    ROUND(
        (SELECT AVG(total_spent) FROM customers WHERE is_active = 1),
        2
    ) AS 'ค่าเฉลี่ย',
    ROUND(
        c.total_spent -
        (SELECT AVG(total_spent) FROM customers WHERE is_active = 1),
        2
    ) AS 'ส่วนต่าง'
FROM customers c
WHERE c.is_active = 1
    AND c.total_spent >
        (SELECT AVG(total_spent) FROM customers WHERE is_active = 1)
ORDER BY c.total_spent DESC
LIMIT 10;
```

## ประสิทธิภาพสินค้า (`product_performance`)

ดูยอดขายรวมของแต่ละสินค้า พร้อมคำนวณราคาเฉลี่ยที่ขายได้และอัตราการขายต่อวัน เพื่อจัดอันดับสินค้าที่ควรโปรโมตหรือเติมสต็อก

- แนวคิดหลัก: การคำนวณจากยอดรวม/จำนวนชิ้นด้วย `NULLIF` เพื่อกันหารศูนย์
- แบบฝึก: เพิ่ม `HAVING ยอดขายรวม > 0` หรือ `ORDER BY อัตราการขายต่อวัน DESC`

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    m.price AS 'ราคา',
    COALESCE(SUM(oi.quantity), 0) AS 'จำนวนที่ขาย',
    COALESCE(SUM(oi.subtotal), 0) AS 'ยอดขายรวม',
    ROUND(
        COALESCE(SUM(oi.subtotal), 0) /
        NULLIF(SUM(oi.quantity), 0),
        2
    ) AS 'ราคาเฉลี่ยที่ขายได้',
    ROUND(
        COALESCE(SUM(oi.quantity), 0) /
        NULLIF(DATEDIFF(CURDATE(), DATE_SUB(CURDATE(), INTERVAL 30 DAY)), 0),
        2
    ) AS 'อัตราการขายต่อวัน'
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
    -- $dateFilter
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
ORDER BY ยอดขายรวม DESC;
```

## เทรนด์สินค้า (`product_trends`)

เปรียบเทียบยอดขายช่วง 7 วันล่าสุดกับ 7 วันก่อนหน้า แล้วระบุว่าเทรนด์กำลังเพิ่มขึ้น ลดลง หรือคงเดิม เหมาะกับการตามกระแส

- แนวคิดหลัก: `CASE` ซ้อนกันและการใช้ `SUM` เงื่อนไขตามช่วงเวลา
- แบบฝึก: เปลี่ยนช่วงเวลาเป็น 14 วัน และเพิ่มคอลัมน์แสดงอัตราการเติบโตเป็นเปอร์เซ็นต์

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    SUM(
        CASE
            WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            THEN oi.quantity ELSE 0
        END
    ) AS 'ขาย_7วันล่าสุด',
    SUM(
        CASE
            WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            THEN oi.quantity ELSE 0
        END
    ) AS 'ขาย_7วันก่อน',
    CASE
        WHEN SUM(
            CASE
                WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                THEN oi.quantity ELSE 0
            END
        ) = 0 THEN 'สินค้าใหม่/ไม่มีข้อมูล'
        WHEN SUM(
            CASE
                WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                THEN oi.quantity ELSE 0
            END
        ) > SUM(
            CASE
                WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                THEN oi.quantity ELSE 0
            END
        ) THEN '📈 เพิ่มขึ้น'
        WHEN SUM(
            CASE
                WHEN o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                THEN oi.quantity ELSE 0
            END
        ) < SUM(
            CASE
                WHEN o.order_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                THEN oi.quantity ELSE 0
            END
        ) THEN '📉 ลดลง'
        ELSE '➡️ คงเดิม'
    END AS 'เทรนด์'
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name
HAVING (ขาย_7วันล่าสุด + ขาย_7วันก่อน) > 0
ORDER BY ขาย_7วันล่าสุด DESC;
```

## สินค้าขายช้า (`slow_moving_products`)

ค้นหาสินค้าที่ขายน้อยหรือไม่เคยขาย พร้อมสถานะและคำแนะนำ เช่น จัดโปรโมชั่น หรือลดราคา เพื่อระบายสต็อก

- แนวคิดหลัก: `HAVING` เพื่อกรองผลลัพธ์หลังจากรวม และ `CASE` สำหรับคำแนะนำ
- แบบฝึก: ปรับจำนวนที่ขาย `<= 10` หรือเพิ่มวันที่ `MAX(o.order_date)`

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    m.price AS 'ราคา',
    COALESCE(SUM(oi.quantity), 0) AS 'จำนวนที่ขาย',
    COALESCE(SUM(oi.subtotal), 0) AS 'ยอดขาย',
    COALESCE(MAX(o.order_date), 'ไม่เคยขาย') AS 'ขายครั้งล่าสุด',
    CASE
        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN '🔴 ไม่เคยขาย'
        WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN '🟠 ขายน้อยมาก'
        WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN '🟡 ขายช้า'
        ELSE '🟢 ปกติ'
    END AS 'สถานะ',
    CASE
        WHEN COALESCE(SUM(oi.quantity), 0) = 0 THEN 'ลดราคา, โปรโมชั่น, หรือพิจารณายกเลิก'
        WHEN COALESCE(SUM(oi.quantity), 0) <= 2 THEN 'สร้างโปรโมชั่น หรือ Bundle กับสินค้าอื่น'
        WHEN COALESCE(SUM(oi.quantity), 0) <= 5 THEN 'ปรับ Marketing หรือตำแหน่งสินค้า'
        ELSE 'ไม่ต้องดำเนินการ'
    END AS 'คำแนะนำ'
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id
    -- $dateFilter
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
HAVING จำนวนที่ขาย <= 5
ORDER BY จำนวนที่ขาย ASC, m.price DESC;
```

## ช่วงเวลาเร่าซื้อ (`peak_hours`)

วิเคราะห์ยอดขายตามวันและชั่วโมง พร้อมเทียบค่าเฉลี่ยเพื่อจัดกลุ่มเป็นช่วงปกติ/เร่ามาก ช่วยบริหารพนักงานและโปรโมชั่นเวลาพีค

- แนวคิดหลัก: การซ้อนซับคิวรีเพื่อหาเกณฑ์เฉลี่ย และ `DAYOFWEEK`
- แบบฝึก: ปรับเงื่อนไขเทียบเป็น 120% หรือจัดกลุ่มวันทำงาน vs เสาร์-อาทิตย์

```sql
SELECT
    HOUR(order_time) AS 'ชั่วโมง',
    CASE DAYOFWEEK(order_date)
        WHEN 1 THEN 'อาทิตย์'
        WHEN 2 THEN 'จันทร์'
        WHEN 3 THEN 'อังคาร'
        WHEN 4 THEN 'พุธ'
        WHEN 5 THEN 'พฤหัสบดี'
        WHEN 6 THEN 'ศุกร์'
        WHEN 7 THEN 'เสาร์'
    END AS 'วัน',
    COUNT(*) AS 'จำนวนออเดอร์',
    SUM(total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(total_amount), 2) AS 'ยอดขายเฉลี่ย',
    CASE
        WHEN COUNT(*) >= (
            SELECT AVG(hourly_count)
            FROM (
                SELECT HOUR(order_time) AS hr, COUNT(*) AS hourly_count
                FROM orders
                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY HOUR(order_time)
            ) AS avg_calc
        ) * 1.5 THEN '🔥 เร่ามาก'
        WHEN COUNT(*) >= (
            SELECT AVG(hourly_count)
            FROM (
                SELECT HOUR(order_time) AS hr, COUNT(*) AS hourly_count
                FROM orders
                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY HOUR(order_time)
            ) AS avg_calc
        ) THEN '📈 เร่า'
        ELSE '📊 ปกติ'
    END AS 'สถานะ'
FROM orders
WHERE 1=1
    -- $dateFilter
GROUP BY HOUR(order_time), DAYOFWEEK(order_date)
ORDER BY วัน, ชั่วโมง;
```

## สินค้าที่พนักงานขาย (`staff_products`)

ดูว่าสินค้าไหนถูกขายโดยพนักงานแต่ละคนมากที่สุด พร้อมจัดอันดับและระดับการขาย เพื่อตามหาจุดแข็งของแต่ละคน หรือจัดการเทรนนิ่ง

- แนวคิดหลัก: Window Function `RANK()` พร้อมการแบ่งกลุ่มด้วย `PARTITION BY`
- แบบฝึก: เปลี่ยนเป็น `DENSE_RANK()` หรือเพิ่ม `SUM(oi.subtotal)` ใน `ORDER BY`

```sql
SELECT
    s.name AS 'ชื่อพนักงาน',
    s.position AS 'ตำแหน่ง',
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    SUM(oi.quantity) AS 'จำนวนที่ขาย',
    SUM(oi.subtotal) AS 'ยอดขายรวม',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    ROUND(AVG(oi.unit_price), 2) AS 'ราคาเฉลี่ย',
    RANK() OVER (PARTITION BY s.id ORDER BY SUM(oi.quantity) DESC) AS 'อันดับสินค้าขายดี',
    CASE
        WHEN SUM(oi.quantity) >= 50 THEN '⭐ สินค้าเด่น'
        WHEN SUM(oi.quantity) >= 20 THEN '👍 สินค้าขายดี'
        WHEN SUM(oi.quantity) >= 10 THEN '🔵 สินค้าปกติ'
        ELSE '🔴 สินค้าขายน้อย'
    END AS 'ระดับการขาย'
FROM staff s
JOIN orders o ON s.id = o.staff_id
JOIN order_items oi ON o.id = oi.order_id
JOIN menus m ON oi.menu_id = m.id
JOIN categories c ON m.category_id = c.id
WHERE s.is_active = 1
    -- $dateFilter
GROUP BY s.id, s.name, s.position, m.id, m.name, c.name
ORDER BY s.name, จำนวนที่ขาย DESC;
```

## ออเดอร์ที่พนักงานรับผิดชอบ (`staff_orders`)

สรุปจำนวนออเดอร์ ยอดขาย สินค้าเฉลี่ยต่อออเดอร์ รวมถึงจัดประเภทออเดอร์ที่พนักงานรับผิดชอบ เพื่อประเมินรูปแบบการขายของแต่ละคน

- แนวคิดหลัก: ซับคิวรีนับจำนวนรายการต่อออเดอร์ และ `CASE` เพื่อจัดระดับออเดอร์
- แบบฝึก: เพิ่มคอลัมน์ `SUM(order_items_count)` หรือ `ORDER BY ออเดอร์เฉลี่ย DESC`

```sql
SELECT
    s.name AS 'ชื่อพนักงาน',
    s.position AS 'ตำแหน่ง',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    SUM(o.total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(o.total_amount), 2) AS 'ออเดอร์เฉลี่ย',
    MIN(o.total_amount) AS 'ออเดอร์ต่ำสุด',
    MAX(o.total_amount) AS 'ออเดอร์สูงสุด',
    ROUND(AVG(order_items_count), 1) AS 'รายการเฉลี่ยต่อออเดอร์',
    COUNT(CASE WHEN o.total_amount >= 500 THEN 1 END) AS 'ออเดอร์ใหญ่_500_บาทขึ้นไป',
    COUNT(CASE WHEN o.total_amount < 100 THEN 1 END) AS 'ออเดอร์เล็ก_ต่ำกว่า100บาท',
    CASE
        WHEN AVG(o.total_amount) >= 300 THEN '🏆 ขายออเดอร์ใหญ่'
        WHEN AVG(o.total_amount) >= 200 THEN '⭐ ขายออเดอร์ปานกลาง'
        WHEN AVG(o.total_amount) >= 100 THEN '👍 ขายออเดอร์เล็ก'
        ELSE '📊 ออเดอร์ขนาดเล็กมาก'
    END AS 'ประเภทการขาย'
FROM staff s
JOIN orders o ON s.id = o.staff_id
JOIN (
    SELECT order_id, COUNT(*) AS order_items_count
    FROM order_items
    GROUP BY order_id
) oi_count ON o.id = oi_count.order_id
WHERE s.is_active = 1
    -- $dateFilter
GROUP BY s.id, s.name, s.position
ORDER BY ยอดขายรวม DESC;
```

## ประสิทธิภาพพนักงาน (`staff_efficiency`)

วัดออเดอร์ต่อวัน ยอดขายต่อวัน/ชั่วโมง และจัดระดับประสิทธิภาพพนักงาน เพื่อรู้ว่าใครทำได้ดีและใครต้องได้รับการสนับสนุน

- แนวคิดหลัก: การใช้ `COUNT(DISTINCT ...)`, การป้องกันหารศูนย์ด้วย `NULLIF`, และ `CASE` เพื่อจัดระดับ
- แบบฝึก: เพิ่มคอลัมน์ `MAX(o.order_date)` เพื่อดูวันที่ทำงานล่าสุด หรือเปลี่ยนสมมติฐานจำนวนชั่วโมงต่อวัน (เช่น 10 ชั่วโมง)

```sql
SELECT
    s.name AS 'ชื่อพนักงาน',
    s.position AS 'ตำแหน่ง',
    COUNT(DISTINCT DATE(o.order_date)) AS 'จำนวนวันที่ทำงาน',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    SUM(o.total_amount) AS 'ยอดขายรวม',
    ROUND(
        COUNT(DISTINCT o.id) /
        NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0),
        2
    ) AS 'ออเดอร์ต่อวัน',
    ROUND(
        SUM(o.total_amount) /
        NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0),
        2
    ) AS 'ยอดขายต่อวัน',
    ROUND(
        COUNT(DISTINCT o.id) /
        (COUNT(DISTINCT DATE(o.order_date)) * 8),
        2
    ) AS 'ออเดอร์ต่อชั่วโมง',
    ROUND(
        SUM(o.total_amount) /
        (COUNT(DISTINCT DATE(o.order_date)) * 8),
        2
    ) AS 'ยอดขายต่อชั่วโมง',
    CASE
        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 20 THEN '🚀 ประสิทธิภาพสูงมาก'
        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 15 THEN '⭐ ประสิทธิภาพสูง'
        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 10 THEN '👍 ประสิทธิภาพปานกลาง'
        WHEN COUNT(DISTINCT o.id) / NULLIF(COUNT(DISTINCT DATE(o.order_date)), 0) >= 5 THEN '📊 ประสิทธิภาพต่ำ'
        ELSE '🔴 ต้องพัฒนา'
    END AS 'ระดับประสิทธิภาพ'
FROM staff s
JOIN orders o ON s.id = o.staff_id
WHERE s.is_active = 1
    -- $dateFilter
GROUP BY s.id, s.name, s.position
ORDER BY ออเดอร์ต่อวัน DESC;
```

## เปรียบเทียบผลงานพนักงานต่อค่าเฉลี่ยทีม (`staff_comparison`)

ใช้ CTE คำนวณค่าเฉลี่ยทั้งทีม แล้วเทียบผลงานแต่ละคนเป็นเปอร์เซ็นต์เหนือ/ต่ำกว่าทีม พร้อมป้ายสถานะ เพื่อใช้อ้างอิงในการโค้ชทีม

- แนวคิดหลัก: Common Table Expression (CTE), การนำค่าเฉลี่ยทีมมาคำนวณเปอร์เซ็นต์เปรียบเทียบ
- แบบฝึก: เพิ่มคอลัมน์ `ROUND(sp.avg_order_value - ta.avg_team_order_value, 2)` หรือปรับเกณฑ์เปอร์เซ็นต์ให้เข้มงวดขึ้น (เช่น 150%, 120%, 80%)

```sql
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
    WHERE s.is_active = 1
        -- $dateFilter
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
    sp.name AS 'ชื่อพนักงาน',
    sp.position AS 'ตำแหน่ง',
    sp.total_orders AS 'จำนวนออเดอร์',
    ROUND(ta.avg_team_orders, 0) AS 'ค่าเฉลี่ยทีม_ออเดอร์',
    ROUND(((sp.total_orders - ta.avg_team_orders) / ta.avg_team_orders) * 100, 1) AS 'เปอร์เซ็นต์เปรียบเทียบ_ออเดอร์',
    sp.total_sales AS 'ยอดขายรวม',
    ROUND(ta.avg_team_sales, 0) AS 'ค่าเฉลี่ยทีม_ยอดขาย',
    ROUND(((sp.total_sales - ta.avg_team_sales) / ta.avg_team_sales) * 100, 1) AS 'เปอร์เซ็นต์เปรียบเทียบ_ยอดขาย',
    sp.avg_order_value AS 'ค่าออเดอร์เฉลี่ย',
    ROUND(ta.avg_team_order_value, 2) AS 'ค่าเฉลี่ยทีม_ค่าออเดอร์',
    CASE
        WHEN sp.total_sales > ta.avg_team_sales * 1.2 THEN '🏆 เหนือค่าเฉลี่ยมาก'
        WHEN sp.total_sales > ta.avg_team_sales THEN '⭐ เหนือค่าเฉลี่ย'
        WHEN sp.total_sales > ta.avg_team_sales * 0.8 THEN '📊 ใกล้ค่าเฉลี่ย'
        ELSE '📈 ต่ำกว่าค่าเฉลี่ย'
    END AS 'ผลงานเปรียบเทียบ'
FROM staff_performance sp
CROSS JOIN team_averages ta
ORDER BY sp.total_sales DESC;
```

---

คุณสามารถนำคำสั่งเหล่านี้ไปทดลองรัน ปรับวันที่ หรือพัฒนาต่อยอดเพื่อสร้างรายงานรูปแบบอื่น ๆ ได้ตามต้องการ หากต้องการคำอธิบายเพิ่มเติมของรายงานใด สามารถกลับไปดูส่วน UI ใน `reports.php` และ JavaScript ที่เกี่ยวข้องใน `js/reports.js` / `js/simple_reports.js` เพื่อเห็นการเชื่อมโยงครบถ้วน
