# สรุปคำสั่ง SQL สำหรับรายงาน Coffee Shop Analytics

ไฟล์นี้รวบรวมคำสั่ง SQL ที่ใช้ใน API `api/reports.php` เพื่อสร้างรายงานแต่ละรูปแบบในระบบ Coffee Shop Analytics ช่วยให้คุณสามารถศึกษาแนวคิดการสรุปข้อมูล การใช้ฟังก์ชัน Aggregate, Window Function, CTE และการจัดกลุ่มข้อมูลได้อย่างเป็นระบบ

> หมายเหตุ
> - โค้ดหลายส่วนใช้ตัวแปรภายใน PHP เช่น `$dateFilter` เพื่อปรับช่วงเวลาอัตโนมัติ โดยดีฟอลต์จะครอบคลุม 7 วันที่ผ่านมา
> - หากรันคำสั่งในเครื่องมือฐานข้อมูล ให้แทนที่คอมเมนต์ `-- $dateFilter` ด้วยเงื่อนไขวันที่ที่ต้องการ หรือเอาออกได้เมื่อไม่ต้องการกรอง
> - รายงานบางประเภทไม่ต้องใช้ตัวกรองวันที่ เช่น การวิเคราะห์ลูกค้า

---

## 📚 คู่มือการศึกษา SQL พื้นฐาน

### 🎯 คำสั่ง SQL พื้นฐานที่ต้องรู้

#### 1. **SELECT** - เลือกข้อมูลที่ต้องการแสดง
```sql
SELECT column1, column2 FROM table_name;
```
- ใช้ `*` เพื่อเลือกทุกคอลัมน์: `SELECT * FROM orders;`
- ใช้ `AS` เพื่อตั้งชื่อคอลัมน์ใหม่: `SELECT name AS 'ชื่อสินค้า'`

#### 2. **WHERE** - กรองข้อมูลตามเงื่อนไข
```sql
SELECT * FROM orders WHERE total_amount > 100;
```
- เปรียบเทียบ: `=`, `>`, `<`, `>=`, `<=`, `!=`
- รวมเงื่อนไข: `AND`, `OR`, `NOT`
- ค้นหาข้อความ: `LIKE '%กาแฟ%'`

#### 3. **GROUP BY** - จัดกลุ่มข้อมูล
```sql
SELECT category, COUNT(*) FROM products GROUP BY category;
```
- **จุดประสงค์:** รวมแถวที่มีค่าเหมือนกันให้อยู่ในกลุ่มเดียว
- **ต้องใช้กับ Aggregate Functions:** COUNT, SUM, AVG, MIN, MAX
- **ตัวอย่าง:** นับจำนวนสินค้าในแต่ละหมวดหมู่

#### 4. **ORDER BY** - เรียงลำดับผลลัพธ์
```sql
SELECT * FROM products ORDER BY price DESC;
```
- `ASC` = น้อยไปมาก (ค่าเริ่มต้น)
- `DESC` = มากไปน้อย
- สามารถเรียงหลายคอลัมน์: `ORDER BY category ASC, price DESC`

#### 5. **JOIN** - เชื่อมตารางข้อมูล
```sql
SELECT * FROM orders o JOIN customers c ON o.customer_id = c.id;
```
- **INNER JOIN:** แสดงเฉพาะข้อมูลที่ตรงกันทั้ง 2 ตาราง
- **LEFT JOIN:** แสดงข้อมูลทั้งหมดจากตารางซ้าย + ข้อมูลที่ตรงจากตารางขวา
- **RIGHT JOIN:** ตรงข้ามกับ LEFT JOIN

#### 6. **Aggregate Functions** - ฟังก์ชันคำนวณ
- `COUNT(*)` - นับจำนวนแถว
- `SUM(column)` - รวมค่าทั้งหมด
- `AVG(column)` - คำนวณค่าเฉลี่ย
- `MIN(column)` - หาค่าต่ำสุด
- `MAX(column)` - หาค่าสูงสุด

#### 7. **HAVING** - กรองข้อมูลหลัง GROUP BY
```sql
SELECT category, COUNT(*) as cnt
FROM products
GROUP BY category
HAVING cnt > 5;
```
- **ความแตกต่างจาก WHERE:** WHERE กรองก่อนจัดกลุ่ม, HAVING กรองหลังจัดกลุ่ม

#### 8. **ฟังก์ชันวันที่และเวลา**
- `DATE(datetime)` - แปลงเป็นวันที่อย่างเดียว
- `YEAR(date)` - ดึงปี
- `MONTH(date)` - ดึงเดือน (เป็นตัวเลข 1-12)
- `MONTHNAME(date)` - ดึงชื่อเดือน (January, February...)
- `HOUR(time)` - ดึงชั่วโมง
- `CURDATE()` - วันที่ปัจจุบัน
- `DATE_SUB(date, INTERVAL n DAY)` - ลบวันที่

#### 9. **CASE WHEN** - สร้างเงื่อนไขแบบ if-else
```sql
SELECT name,
  CASE
    WHEN price < 50 THEN 'ถูก'
    WHEN price < 100 THEN 'ปานกลาง'
    ELSE 'แพง'
  END as 'ระดับราคา'
FROM products;
```

#### 10. **ฟังก์ชันอื่นๆ ที่ใช้บ่อย**
- `ROUND(number, decimal)` - ปัดเศษทศนิยม
- `COALESCE(value1, value2)` - ใช้ค่าแรกที่ไม่ใช่ NULL
- `DISTINCT` - กรองค่าซ้ำออก: `SELECT DISTINCT category FROM products`
- `LIMIT n` - จำกัดจำนวนแถวที่แสดง

---

## วิธีใช้ไฟล์นี้เพื่อศึกษา

1. อ่านคำอธิบายสั้นก่อนดูคำสั่ง เพื่อเข้าใจโจทย์และผลลัพธ์ที่คาดหวัง
2. สังเกตหัวข้อ "แนวคิดหลัก" เพื่อโฟกัสฟังก์ชัน/โครงสร้าง SQL สำคัญในแต่ละตัวอย่าง
3. อ่านส่วน "💡 คำอธิบายโค้ด" เพื่อเข้าใจการทำงานแบบละเอียด
4. ทดลองปรับแก้คำสั่ง (เพิ่มเงื่อนไข, เปลี่ยนการจัดกลุ่ม, ตัดคอลัมน์) แล้วรันใน phpMyAdmin หรือ MySQL CLI เพื่อเห็นผลลัพธ์ที่ต่างกัน
5. หลังรันเสร็จ ลองเขียนคำอธิบายผลลัพธ์ด้วยตัวเอง เพื่อเตรียมการสอบหรือการนำเสนอ

---

## รายงานยอดขายรายวัน (`daily_sales`)

คำสั่งนี้สรุปยอดขายตามแต่ละวัน เพื่อเช็กจำนวนออเดอร์ ยอดขายรวม ค่าเฉลี่ย รวมถึงค่าสูงสุดต่ำสุด ช่วยดูแนวโน้มรายวันและหาวันที่พีคหรือเงียบผิดปกติ

- **แนวคิดหลัก:** `GROUP BY` วันที่, ฟังก์ชันรวม (`COUNT`, `SUM`, `AVG`, `MIN`, `MAX`)
- **แบบฝึก:** ทดลองเปลี่ยนช่วงวันที่ หรือเพิ่มเงื่อนไข `HAVING SUM(total_amount) > ...`

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

### 💡 คำอธิบายโค้ด

1. **`DATE(order_date)`** - แปลง datetime เป็นวันที่อย่างเดียว (ตัด เวลา ออก)
2. **`COUNT(*)`** - นับจำนวนออเดอร์ทั้งหมดในแต่ละวัน
3. **`SUM(total_amount)`** - รวมยอดขายทั้งหมดในแต่ละวัน
4. **`ROUND(AVG(total_amount), 2)`** - คำนวณค่าเฉลี่ยของออเดอร์ และปัดเศษ 2 ตำแหน่ง
5. **`MIN/MAX(total_amount)`** - หาออเดอร์ที่มียอดต่ำสุดและสูงสุดในวันนั้น
6. **`GROUP BY DATE(order_date)`** - จัดกลุ่มข้อมูลตามวันที่
7. **`ORDER BY order_date DESC`** - เรียงจากวันใหม่ไปเก่า

### 🎓 ทริกที่ควรรู้

- **WHERE 1=1** - เทคนิคที่ใช้เพื่อให้เพิ่มเงื่อนไข AND ได้ง่าย (ในโค้ดจริงจะมี `AND $dateFilter` ต่อท้าย)
- **Aggregate Functions ต้องใช้กับ GROUP BY** - ถ้าใช้ COUNT, SUM, AVG ต้องมี GROUP BY เสมอ (ยกเว้นต้องการรวมทั้งหมด)
- **ROUND เพื่อความสวยงาม** - ราคาควรแสดง 2 ตำแหน่งทศนิยม

### 📊 ผลลัพธ์ตัวอย่าง
```
วันที่       | จำนวนออเดอร์ | ยอดขายรวม | ยอดขายเฉลี่ย | ยอดขายต่ำสุด | ยอดขายสูงสุด
2025-10-26  | 45           | 12,500     | 277.78       | 50           | 850
2025-10-25  | 38           | 9,800      | 257.89       | 45           | 720
```

## รายงานยอดขายรายเดือน (`monthly_sales`)

ใช้ดูภาพรวมเชิงเดือน นับจำนวนออเดอร์และยอดขายในแต่ละเดือน เพื่อเปรียบเทียบฤดูกาลหรือวิเคราะห์การเติบโตต่อเนื่อง

- **แนวคิดหลัก:** การใช้ฟังก์ชันวันที่ (`YEAR`, `MONTH`, `MONTHNAME`) และการจัดกลุ่มหลายระดับ
- **แบบฝึก:** เพิ่ม `ORDER BY MONTHNAME(order_date)` หรือรวมฟิลด์ `payment_type` เพื่อลงรายละเอียด

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

### 💡 คำอธิบายโค้ด

1. **`YEAR(order_date)`** - ดึงปีออกมา (เช่น 2025)
2. **`MONTH(order_date)`** - ดึงเดือนเป็นตัวเลข (1-12)
3. **`MONTHNAME(order_date)`** - ดึงชื่อเดือนภาษาอังกฤษ (January, February...)
4. **`GROUP BY YEAR(...), MONTH(...)`** - จัดกลุ่มตาม ปี+เดือน (ต้องระบุทั้ง 2 ตัว)
5. **`ORDER BY ... DESC, ... DESC`** - เรียงจากปีใหม่→เก่า, เดือน 12→1

### 🎓 ทริกที่ควรรู้

- **ทำไมต้อง GROUP BY ทั้งปีและเดือน?** - เพราะเดือนมกราคม 2024 กับ มกราคม 2025 ไม่ใช่กลุ่มเดียวกัน
- **MONTHNAME ภาษาไทย** - ถ้าต้องการภาษาไทย ใช้ `CASE WHEN MONTH(...) = 1 THEN 'มกราคม' ...`
- **การเปรียบเทียบเดือน** - เหมาะสำหรับดูว่าเดือนไหนขายดีกว่า หรือมีการเติบโต

### 📊 ผลลัพธ์ตัวอย่าง
```
ปี   | เดือน | ชื่อเดือน  | จำนวนออเดอร์ | ยอดขายรวม | ยอดขายเฉลี่ย
2025 | 10    | October    | 1,245        | 342,800    | 275.34
2025 | 9     | September  | 1,180        | 318,500    | 269.92
```

## รายงานสินค้าขายดี (`top_products`)

ดึงรายการสินค้าที่ขายดีที่สุด 10 อันดับ โดยดูจำนวนชิ้น ยอดขายรวม และราคาเฉลี่ย เพื่อโฟกัสสินค้าที่ได้รับความนิยม

- **แนวคิดหลัก:** การเชื่อมตาราง (`JOIN`) หลายตารางพร้อมกัน และใช้ `LIMIT`
- **แบบฝึก:** ลองเปลี่ยนลำดับ `ORDER BY SUM(oi.subtotal)` หรือเพิ่ม `HAVING COUNT(DISTINCT o.id) >= 3`

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

### 💡 คำอธิบายโค้ด

1. **`FROM order_items oi`** - เริ่มจากตาราง order_items (เพราะเก็บข้อมูลว่าขายอะไรบ้าง)
2. **`JOIN menus m ON oi.menu_id = m.id`** - เชื่อมกับตาราง menus เพื่อเอาชื่อสินค้า
3. **`JOIN categories c ON m.category_id = c.id`** - เชื่อมกับตาราง categories เพื่อเอาหมวดหมู่
4. **`JOIN orders o ON oi.order_id = o.id`** - เชื่อมกับตาราง orders เพื่อกรองวันที่
5. **`COUNT(DISTINCT o.id)`** - นับจำนวนออเดอร์ที่ไม่ซ้ำกัน (DISTINCT = ไม่นับซ้ำ)
6. **`GROUP BY m.id, m.name, c.name`** - จัดกลุ่มตามสินค้า (ต้องใส่ทุกคอลัมน์ที่ไม่ได้ใช้ aggregate)
7. **`ORDER BY SUM(oi.quantity) DESC`** - เรียงตามจำนวนที่ขายจากมาก→น้อย
8. **`LIMIT 10`** - เอาแค่ 10 อันดับแรก

### 🎓 ทริกที่ควรรู้

- **ทำไมใช้ JOIN?** - เพราะข้อมูลกระจายอยู่หลายตาราง (ชื่อสินค้าอยู่ที่ menus, ราคาอยู่ที่ order_items)
- **DISTINCT คืออะไร?** - กรองค่าซ้ำออก (ถ้าออเดอร์เดียวกันซื้อกาแฟ 3 แก้ว นับเป็น 1 ออเดอร์)
- **สามารถเปลี่ยนเกณฑ์ได้** - ถ้าต้องการดูตามยอดเงิน ใช้ `ORDER BY SUM(oi.subtotal) DESC`

### 📊 ผลลัพธ์ตัวอย่าง
```
ชื่อสินค้า        | หมวดหมู่  | จำนวนที่ขาย | จำนวนออเดอร์ | ยอดขายรวม | ราคาเฉลี่ย
ลาเต้             | เครื่องดื่ม | 450         | 380          | 22,500    | 50.00
คาปูชิโน่          | เครื่องดื่ม | 385         | 320          | 19,250    | 50.00
เอสเพรสโซ่        | เครื่องดื่ม | 280         | 265          | 11,200    | 40.00
```

## วิเคราะห์ลูกค้า (`customer_analysis`)

แสดงข้อมูลลูกค้าและระดับสมาชิก พร้อมยอดซื้อสะสม แต้ม ค่าเฉลี่ยการกลับมา เพื่อระบุลูกค้ากลุ่ม VIP และลูกค้าที่อาจต้องกระตุ้น

- **แนวคิดหลัก:** `LEFT JOIN` กับซับคิวรี และ `CASE WHEN` เพื่อสร้างระดับสมาชิก
- **แบบฝึก:** เพิ่มคอลัมน์ `DATEDIFF(CURDATE(), last_order)` เพื่อดูจำนวนวันที่ไม่ได้มาใช้บริการ

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

### 💡 คำอธิบายโค้ด

1. **Subquery (ซับคิวรี)** - วงเล็บใน `LEFT JOIN ( ... )` คือการ query ซ้อน query
   - หา `MAX(order_date)` = วันที่สั่งซื้อล่าสุดของแต่ละลูกค้า
   - `GROUP BY customer_id` = แยกตามลูกค้า
2. **`LEFT JOIN`** - เอาลูกค้าทั้งหมด แม้ไม่เคยสั่งซื้อก็แสดง (ถ้าใช้ JOIN ธรรมดา ลูกค้าที่ไม่เคยซื้อจะหายไป)
3. **`COALESCE(value1, value2)`** - ถ้า value1 เป็น NULL ให้ใช้ value2 แทน
   - เช่น ถ้าไม่เคยสั่ง (NULL) ให้แสดง 'ไม่เคยสั่ง'
4. **`CASE WHEN ... END`** - คล้าย if-else ในการเขียนโปรแกรม
   - ตรวจสอบเงื่อนไขจากบนลงล่าง หยุดที่เงื่อนไขแรกที่เป็นจริง

### 🎓 ทริกที่ควรรู้

- **LEFT JOIN vs INNER JOIN**
  - `LEFT JOIN` = เอาข้อมูลทั้งหมดจากตารางซ้าย (customers) แม้ไม่มีใน orders
  - `INNER JOIN` = เอาเฉพาะข้อมูลที่ตรงกันทั้ง 2 ตาราง
- **CASE WHEN ลำดับสำคัญ** - ใส่เงื่อนไขที่เข้มงวดกว่าไว้บน (>= 5000 ก่อน >= 2000)
- **Subquery ช่วยจัดกลุ่มข้อมูลก่อน** - หาวันล่าสุดของแต่ละคนก่อน แล้วค่อย JOIN

### 📊 ผลลัพธ์ตัวอย่าง
```
ชื่อลูกค้า  | เบอร์โทร     | แต้มสะสม | ยอดซื้อสะสม | จำนวนครั้งที่มา | ออเดอร์ล่าสุด | ระดับสมาชิก
สมชาย       | 081-234-5678 | 520      | 8,500        | 45              | 2025-10-25    | VIP
สมหญิง      | 082-345-6789 | 180      | 3,200        | 22              | 2025-10-20    | Gold
ประยุทธ์     | 083-456-7890 | 95       | 1,800        | 12              | 2025-10-15    | Silver
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

- **แนวคิดหลัก:** Window Function `RANK()` พร้อมการแบ่งกลุ่มด้วย `PARTITION BY`
- **แบบฝึก:** เปลี่ยนเป็น `DENSE_RANK()` หรือเพิ่ม `SUM(oi.subtotal)` ใน `ORDER BY`

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

### 💡 คำอธิบายโค้ด - Window Functions

1. **`RANK() OVER (...)`** - Window Function สำหรับจัดอันดับ
   - ไม่ต้องใช้ GROUP BY เพิ่ม แต่ทำงานบนผลลัพธ์ที่ GROUP BY แล้ว
   - แสดงอันดับของแต่ละแถว

2. **`PARTITION BY s.id`** - แบ่งกลุ่มข้อมูลตามพนักงาน
   - อันดับจะรีเซ็ตทุกครั้งที่เปลี่ยนพนักงาน
   - พนักงาน A มีอันดับ 1,2,3 พนักงาน B ก็มีอันดับ 1,2,3 ของตัวเอง

3. **`ORDER BY SUM(oi.quantity) DESC`** - เรียงลำดับภายในกลุ่ม
   - สินค้าที่ขายได้มากที่สุดของพนักงานแต่ละคนจะได้อันดับ 1

### 🎓 ทริกที่ควรรู้ - Window Functions

- **RANK() vs DENSE_RANK() vs ROW_NUMBER()**
  ```sql
  -- ถ้าคะแนนเป็น 100, 90, 90, 80
  RANK()        = 1, 2, 2, 4  (มีอันดับซ้ำได้, ข้ามอันดับถัดไป)
  DENSE_RANK()  = 1, 2, 2, 3  (มีอันดับซ้ำได้, ไม่ข้ามอันดับ)
  ROW_NUMBER()  = 1, 2, 3, 4  (ไม่มีอันดับซ้ำเลย)
  ```

- **PARTITION BY คล้าย GROUP BY แต่ไม่รวมข้อมูล** - เพียงแบ่งกลุ่มเพื่อคำนวณ
- **Window Functions อื่นๆ ที่ใช้ได้**
  - `SUM() OVER()` - ผลรวมสะสม
  - `AVG() OVER()` - ค่าเฉลี่ยในกลุ่ม
  - `LAG()` / `LEAD()` - ดูแถวก่อนหน้า/ถัดไป

### 📊 ผลลัพธ์ตัวอย่าง
```
ชื่อพนักงาน | ตำแหน่ง | ชื่อสินค้า | หมวดหมู่    | จำนวนที่ขาย | อันดับสินค้าขายดี | ระดับการขาย
สมชาย        | Barista | ลาเต้      | เครื่องดื่ม   | 120         | 1                 | ⭐ สินค้าเด่น
สมชาย        | Barista | คาปูชิโน่   | เครื่องดื่ม   | 85          | 2                 | ⭐ สินค้าเด่น
สมชาย        | Barista | เอสเพรสโซ่ | เครื่องดื่ม   | 45          | 3                 | 👍 สินค้าขายดี
สมหญิง       | Cashier | ชาเขียว    | เครื่องดื่ม   | 95          | 1                 | ⭐ สินค้าเด่น
สมหญิง       | Cashier | คุกกี้     | ขนม          | 60          | 2                 | ⭐ สินค้าเด่น
```
สังเกตว่าอันดับรีเซ็ตเมื่อเปลี่ยนพนักงาน (สมชาย → สมหญิง)

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

- **แนวคิดหลัก:** Common Table Expression (CTE), การนำค่าเฉลี่ยทีมมาคำนวณเปอร์เซ็นต์เปรียบเทียบ
- **แบบฝึก:** เพิ่มคอลัมน์ `ROUND(sp.avg_order_value - ta.avg_team_order_value, 2)` หรือปรับเกณฑ์เปอร์เซ็นต์ให้เข้มงวดขึ้น (เช่น 150%, 120%, 80%)

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

### 💡 คำอธิบายโค้ด - CTE (Common Table Expression)

**CTE คืออะไร?** - เหมือนการสร้างตารางชั่วคราวเพื่อใช้ใน Query เดียว ทำให้โค้ดอ่านง่ายขึ้น

**โครงสร้าง:**
```sql
WITH ชื่อ_CTE AS (
    -- Query ที่ 1
),
ชื่อ_CTE_ที่2 AS (
    -- Query ที่ 2 (สามารถใช้ CTE แรกได้)
)
SELECT ... FROM ชื่อ_CTE JOIN ชื่อ_CTE_ที่2
```

**ในตัวอย่างนี้:**

1. **CTE ที่ 1: `staff_performance`**
   - คำนวณผลงานของพนักงานแต่ละคน (ออเดอร์, ยอดขาย)
   - เก็บผลลัพธ์ไว้ในชื่อ `staff_performance`

2. **CTE ที่ 2: `team_averages`**
   - ใช้ข้อมูลจาก CTE แรก
   - คำนวณค่าเฉลี่ยของทีมทั้งหมด

3. **SELECT หลัก**
   - ดึงข้อมูลจาก CTE ทั้ง 2 มาเปรียบเทียบ
   - `CROSS JOIN` = เอาทุกแถวจาก sp มาจับคู่กับ ta (ในกรณีนี้ ta มีแถวเดียว)

### 🎓 ทริกที่ควรรู้ - CTE

- **ข้อดีของ CTE**
  - อ่านง่ายกว่า Subquery ซ้อนหลายชั้น
  - สามารถใช้ CTE ซ้ำได้หลายครั้งในคำสั่งเดียว
  - สามารถอ้างอิง CTE อื่นได้

- **CTE vs Subquery vs VIEW**
  ```sql
  -- CTE (ชั่วคราวใน Query เดียว)
  WITH temp AS (SELECT ...) SELECT * FROM temp;

  -- Subquery (ซ้อนใน Query)
  SELECT * FROM (SELECT ...) AS temp;

  -- VIEW (ถาวร ใช้ได้หลาย Query)
  CREATE VIEW temp AS SELECT ...;
  ```

- **เมื่อไหร่ควรใช้ CTE?**
  - Query ซับซ้อน ต้องการแบ่งเป็นขั้นตอน
  - ต้องการใช้ผลลัพธ์เดียวกันหลายครั้ง
  - ทำให้โค้ดอ่านง่ายขึ้น

### 📊 ผลลัพธ์ตัวอย่าง
```
ชื่อพนักงาน | ตำแหน่ง | จำนวนออเดอร์ | ค่าเฉลี่ยทีม | เปอร์เซ็นต์เปรียบเทียบ | ยอดขายรวม | ผลงานเปรียบเทียบ
สมชาย        | Barista | 150         | 100          | +50.0%                  | 45,000     | 🏆 เหนือค่าเฉลี่ยมาก
สมหญิง       | Cashier | 110         | 100          | +10.0%                  | 32,000     | ⭐ เหนือค่าเฉลี่ย
ประยุทธ์      | Barista | 90          | 100          | -10.0%                  | 28,000     | 📊 ใกล้ค่าเฉลี่ย
สมศรี        | Cashier | 50          | 100          | -50.0%                  | 18,000     | 📈 ต่ำกว่าค่าเฉลี่ย
```

### 🔥 ตัวอย่าง CTE ขั้นสูง - Recursive CTE
```sql
-- สร้างลำดับเลข 1-10 (ไม่เกี่ยวกับ Coffee Shop แต่เป็นตัวอย่างที่ดี)
WITH RECURSIVE numbers AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 10
)
SELECT * FROM numbers;
```

---

## 📝 สรุปเทคนิค SQL ทั้งหมดในไฟล์นี้

### 🔰 ระดับพื้นฐาน
- ✅ `SELECT`, `FROM`, `WHERE` - เลือกและกรองข้อมูล
- ✅ `GROUP BY`, `ORDER BY` - จัดกลุ่มและเรียงลำดับ
- ✅ `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` - ฟังก์ชันรวมพื้นฐาน
- ✅ `LIMIT` - จำกัดจำนวนแถว
- ✅ `AS` - ตั้งชื่อคอลัมน์ใหม่

### 🔶 ระดับกลาง
- ✅ `JOIN` (INNER, LEFT, RIGHT) - เชื่อมตาราง
- ✅ `CASE WHEN` - เงื่อนไขแบบ if-else
- ✅ `HAVING` - กรองข้อมูลหลัง GROUP BY
- ✅ `DISTINCT` - กรองค่าซ้ำ
- ✅ `COALESCE` - จัดการค่า NULL
- ✅ ฟังก์ชันวันที่: `DATE`, `YEAR`, `MONTH`, `HOUR`, `CURDATE`, `DATE_SUB`
- ✅ `ROUND`, `NULLIF` - ฟังก์ชันคำนวณ
- ✅ Subquery - Query ซ้อน Query

### 🔴 ระดับสูง
- ✅ **Window Functions:** `RANK()`, `DENSE_RANK()`, `ROW_NUMBER()`
- ✅ **PARTITION BY** - แบ่งกลุ่มสำหรับ Window Functions
- ✅ **CTE (Common Table Expression)** - WITH ... AS (...)
- ✅ **CROSS JOIN** - Cartesian product
- ✅ **Aggregate Functions ซ้อน CASE WHEN**
- ✅ **Multiple Subqueries** - ใช้ subquery หลายจุด

---

## วันที่ขายดีที่สุด (`best_day`)

```sql
SELECT
    DATE(order_date) AS 'วันที่',
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
    RANK() OVER (ORDER BY SUM(total_amount) DESC) AS 'อันดับ'
FROM orders
WHERE 1=1 -- $dateFilter
GROUP BY DATE(order_date), DAYOFWEEK(order_date)
ORDER BY 'ยอดขายรวม' DESC
LIMIT 10
```

### 💡 คำอธิบายโค้ด

1. **DATE(order_date)** - แปลง datetime เป็นวันที่อย่างเดียว
2. **DAYOFWEEK(order_date)** - หาว่าเป็นวันอะไรในสัปดาห์ (1=อาทิตย์, 2=จันทร์, ...)
3. **CASE WHEN + DAYOFWEEK** - แปลงตัวเลขเป็นชื่อวันภาษาไทย
4. **RANK() OVER (ORDER BY ...)** - จัดอันดับตามยอดขายรวม (Window Function)
   - ถ้ายอดขายเท่ากันจะได้อันดับเดียวกัน และข้ามอันดับถัดไป
5. **GROUP BY DATE, DAYOFWEEK** - จัดกลุ่มตามวันที่และวัน
6. **ORDER BY ยอดขายรวม DESC** - เรียงจากมากไปน้อย
7. **LIMIT 10** - แสดงแค่ Top 10 วัน

### 🎓 ทริกที่ควรรู้

- **RANK() vs DENSE_RANK() vs ROW_NUMBER()**
  - `RANK()` - อันดับเท่ากันได้, ข้ามอันดับ (1,2,2,4)
  - `DENSE_RANK()` - อันดับเท่ากันได้, ไม่ข้ามอันดับ (1,2,2,3)
  - `ROW_NUMBER()` - อันดับไม่ซ้ำ (1,2,3,4)
- **Window Function** ไม่ต้องใช้ GROUP BY แยก แต่ใช้ OVER clause
- **DAYOFWEEK()** เริ่มจาก 1 (อาทิตย์) ไม่ใช่ 0

### 📊 ผลลัพธ์ตัวอย่าง

| วันที่ | วัน | จำนวนออเดอร์ | ยอดขายรวม | ยอดขายเฉลี่ย | อันดับ |
|--------|-----|--------------|-----------|--------------|--------|
| 2025-10-25 | ศุกร์ | 45 | 12,500.00 | 277.78 | 1 |
| 2025-10-24 | พฤหัสบดี | 38 | 10,800.00 | 284.21 | 2 |
| 2025-10-26 | เสาร์ | 40 | 10,500.00 | 262.50 | 3 |

---

## สรุปยอดขายสินค้า (`product_summary`)

```sql
SELECT
    m.name AS 'ชื่อสินค้า',
    c.name AS 'หมวดหมู่',
    m.price AS 'ราคา',
    SUM(oi.quantity) AS 'ขายไปแล้ว',
    SUM(oi.subtotal) AS 'ยอดขายรวม',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    ROUND(AVG(oi.unit_price), 2) AS 'ราคาเฉลี่ย',
    MIN(o.order_date) AS 'ขายครั้งแรก',
    MAX(o.order_date) AS 'ขายครั้งล่าสุด',
    CASE
        WHEN SUM(oi.quantity) >= 100 THEN '🔥 ขายดีมาก'
        WHEN SUM(oi.quantity) >= 50 THEN '⭐ ขายดี'
        WHEN SUM(oi.quantity) >= 20 THEN '👍 ขายปานกลาง'
        WHEN SUM(oi.quantity) >= 10 THEN '📊 ขายน้อย'
        ELSE '🔴 ขายน้อยมาก'
    END AS 'สถานะ'
FROM menus m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.menu_id
LEFT JOIN orders o ON oi.order_id = o.id -- $dateFilter
WHERE m.is_active = 1
GROUP BY m.id, m.name, c.name, m.price
ORDER BY 'ขายไปแล้ว' DESC
```

### 💡 คำอธิบายโค้ด

1. **LEFT JOIN** - ใช้เพื่อแสดงสินค้าทั้งหมดแม้ยังไม่เคยขาย (ขายไปแล้ว = NULL หรือ 0)
2. **SUM(oi.quantity)** - รวมจำนวนชิ้นที่ขายไป (เช่น กาแฟขาย 150 แก้ว)
3. **COUNT(DISTINCT o.id)** - นับจำนวนออเดอร์ที่ไม่ซ้ำ (ออเดอร์เดียวอาจมีหลายรายการ)
4. **MIN/MAX(order_date)** - หาวันที่ขายครั้งแรกและล่าสุด
5. **CASE WHEN + SUM** - จัดระดับสินค้าตามยอดขาย
   - >= 100 ชิ้น = ขายดีมาก 🔥
   - >= 50 ชิ้น = ขายดี ⭐
   - >= 20 ชิ้น = ขายปานกลาง 👍
   - >= 10 ชิ้น = ขายน้อย 📊
   - < 10 ชิ้น = ขายน้อยมาก 🔴
6. **m.is_active = 1** - แสดงเฉพาะสินค้าที่เปิดขายอยู่

### 🎓 ทริกที่ควรรู้

- **LEFT JOIN vs INNER JOIN**
  - `INNER JOIN` - แสดงเฉพาะสินค้าที่เคยขาย
  - `LEFT JOIN` - แสดงสินค้าทั้งหมด (แม้ยังไม่เคยขาย)
- **COUNT(*) vs COUNT(DISTINCT column)**
  - `COUNT(*)` - นับทุกแถว (อาจซ้ำ)
  - `COUNT(DISTINCT o.id)` - นับเฉพาะค่าที่ไม่ซ้ำ
- **CASE WHEN เรียงเงื่อนไข** - เงื่อนไขที่เข้มงวดควรอยู่บนสุด (>= 100 ก่อน >= 50)
- **SUM ใน CASE WHEN** - สามารถใช้ Aggregate Function ในเงื่อนไขได้

### 📊 ผลลัพธ์ตัวอย่าง

| ชื่อสินค้า | หมวดหมู่ | ราคา | ขายไปแล้ว | ยอดขายรวม | จำนวนออเดอร์ | สถานะ |
|-----------|---------|------|-----------|-----------|--------------|-------|
| Americano | กาแฟ | 50.00 | 150 | 7,500.00 | 120 | 🔥 ขายดีมาก |
| Latte | กาแฟ | 65.00 | 85 | 5,525.00 | 75 | ⭐ ขายดี |
| Croissant | ขนม | 45.00 | 32 | 1,440.00 | 28 | 👍 ขายปานกลาง |

---

## จำนวนเมนูที่ขายแต่ละเดือน (`monthly_menu_count`)

```sql
SELECT
    YEAR(o.order_date) AS 'ปี',
    MONTH(o.order_date) AS 'เดือน',
    MONTHNAME(o.order_date) AS 'ชื่อเดือน',
    COUNT(DISTINCT m.id) AS 'จำนวนเมนูที่ขาย',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    SUM(oi.quantity) AS 'จำนวนแก้วทั้งหมด',
    SUM(oi.subtotal) AS 'ยอดขายรวม'
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN menus m ON oi.menu_id = m.id
WHERE 1=1 -- $dateFilter, $monthFilter
GROUP BY YEAR(o.order_date), MONTH(o.order_date)
ORDER BY YEAR(o.order_date) DESC, MONTH(o.order_date) DESC
```

### 💡 คำอธิบายโค้ด

1. **COUNT(DISTINCT m.id)** - นับจำนวนเมนูที่ไม่ซ้ำกันที่ขายในเดือนนั้น
   - ถ้าเดือนหนึ่งขายเมนู 15 ชนิด จะได้ 15
2. **COUNT(DISTINCT o.id)** - นับจำนวนออเดอร์ที่ไม่ซ้ำ
3. **SUM(oi.quantity)** - รวมจำนวนแก้วทั้งหมดที่ขายในเดือนนั้น
4. **GROUP BY YEAR, MONTH** - จัดกลุ่มตามปีและเดือน
5. **MONTHNAME()** - แสดงชื่อเดือนภาษาอังกฤษ (January, February...)

### 🎓 ทริกที่ควรรู้

- **COUNT(DISTINCT) สำคัญมาก** - ถ้าไม่ใช้ DISTINCT จะนับซ้ำ
- **ตอบโจทย์ข้อสอบ:** "หาว่าในแต่ละเดือนขายกี่เมนู" = COUNT DISTINCT menu
- **การเชื่อม JOIN 3 ตาราง** - orders → order_items → menus
- ใช้ได้กับข้อสอบที่ถาม "แต่ละเดือนมีเมนูอะไรบ้าง"

### 📊 ผลลัพธ์ตัวอย่าง

| ปี | เดือน | ชื่อเดือน | จำนวนเมนูที่ขาย | จำนวนออเดอร์ | จำนวนแก้วทั้งหมด | ยอดขายรวม |
|----|-------|-----------|----------------|--------------|------------------|-----------|
| 2025 | 10 | October | 15 | 245 | 680 | 34,500.00 |
| 2025 | 9 | September | 14 | 230 | 650 | 32,800.00 |
| 2025 | 8 | August | 16 | 260 | 720 | 36,200.00 |

---

## พนักงานขายให้ลูกค้าคนไหนบ้าง (`staff_customers`)

```sql
SELECT
    s.name AS 'ชื่อพนักงาน',
    c.name AS 'ชื่อลูกค้า',
    c.type AS 'ประเภทลูกค้า',
    COUNT(DISTINCT o.id) AS 'จำนวนออเดอร์',
    SUM(o.total_amount) AS 'ยอดขายรวม',
    ROUND(AVG(o.total_amount), 2) AS 'ยอดขายเฉลี่ย',
    MIN(o.order_date) AS 'ซื้อครั้งแรก',
    MAX(o.order_date) AS 'ซื้อครั้งล่าสุด'
FROM staff s
JOIN orders o ON s.id = o.staff_id
LEFT JOIN customers c ON o.customer_id = c.id
WHERE 1=1 -- $dateFilter, $staffFilter, $customerFilter, $monthFilter
GROUP BY s.id, s.name, c.id, c.name, c.type
ORDER BY s.name, 'ยอดขายรวม' DESC
```

### 💡 คำอธิบายโค้ด

1. **Staff → Orders → Customers** - เชื่อม 3 ตารางเพื่อดูความสัมพันธ์
2. **LEFT JOIN customers** - ใช้ LEFT JOIN เพราะบางออเดอร์อาจไม่มีลูกค้า (Walk-in)
3. **GROUP BY s.id, c.id** - จัดกลุ่มตามพนักงานและลูกค้า
4. **MIN/MAX order_date** - หาว่าซื้อครั้งแรกและล่าสุดเมื่อไหร่
5. **ORDER BY s.name** - เรียงตามชื่อพนักงานก่อน แล้วเรียงตามยอดขาย

### 🎓 ทริกที่ควรรู้

- **ตอบโจทย์ข้อสอบ:** "พนักงาน วิทยา ขายให้กับลูกค้าคนไหนบ้าง"
  - เพิ่ม WHERE s.name = 'วิทยา'
- **Staff-Customer Relationship** - ดูว่าพนักงานแต่ละคนดูแลลูกค้าใครบ้าง
- **CRM Analysis** - ใช้วิเคราะห์ความสัมพันธ์ระหว่างพนักงานกับลูกค้า
- **LEFT JOIN vs INNER JOIN** - ต้องเข้าใจว่าเมื่อไหร่ควรใช้อะไร

### 📊 ผลลัพธ์ตัวอย่าง

| ชื่อพนักงาน | ชื่อลูกค้า | ประเภทลูกค้า | จำนวนออเดอร์ | ยอดขายรวม | ยอดขายเฉลี่ย |
|------------|-----------|-------------|--------------|-----------|--------------|
| วิทยา | บริษัท A | Corporate | 25 | 12,500.00 | 500.00 |
| วิทยา | สมชาย | Member | 18 | 9,000.00 | 500.00 |
| วิทยา | สมหญิง | Regular | 12 | 6,000.00 | 500.00 |
| กานดา | บริษัท B | Corporate | 30 | 15,000.00 | 500.00 |

---

## 🔍 Filter Parameters - การกรองข้อมูลเฉพาะ

ระบบรองรับการกรองข้อมูลเพิ่มเติมตามพารามิเตอร์ต่อไปนี้:

### 1. **กรองตามชื่อเมนู** (`menu_name`)
```sql
-- ตัวอย่าง: หายอดขายของเมนู "ลาเต้"
WHERE m.name LIKE '%ลาเต้%'
```
**ใช้กับรายงาน:** product_summary, top_products

**ตัวอย่างการใช้งาน:**
```
api/reports.php?type=product_summary&menu_name=ลาเต้
```

### 2. **กรองตามชื่อพนักงาน** (`staff_name`)
```sql
-- ตัวอย่าง: หาจำนวนแก้วที่พนักงาน "กานดา" ขายได้
WHERE s.name LIKE '%กานดา%'
```
**ใช้กับรายงาน:** staff_performance, staff_customers, staff_products

**ตัวอย่างการใช้งาน:**
```
api/reports.php?type=staff_customers&staff_name=กานดา
```

### 3. **กรองตามชื่อลูกค้า** (`customer_name`)
```sql
-- ตัวอย่าง: หาว่าลูกค้า "สมชาย" ซื้อไปทั้งหมดกี่แก้ว
WHERE c.name LIKE '%สมชาย%'
```
**ใช้กับรายงาน:** customer_analysis, staff_customers

**ตัวอย่างการใช้งาน:**
```
api/reports.php?type=customer_analysis&customer_name=สมชาย
```

### 4. **กรองตามเดือน** (`month`)
```sql
-- ตัวอย่าง: หาจำนวนแก้วที่ขายในเดือน "ธ.ค." (12)
WHERE MONTH(order_date) = 12
```
**ใช้กับรายงาน:** ทุกรายงานที่มี order_date

**ตัวอย่างการใช้งาน:**
```
api/reports.php?type=product_summary&month=12
```

### 🎯 ตัวอย่างข้อสอบที่ใช้ Filter

**ข้อ 6:** หาว่าลูกค้าชื่อ "สมชาย" ซื้อไปทั้งหมดกี่แก้ว
```sql
SELECT c.name, SUM(oi.quantity) AS 'จำนวนแก้ว'
FROM customers c
JOIN orders o ON c.id = o.customer_id
JOIN order_items oi ON o.id = oi.order_id
WHERE c.name LIKE '%สมชาย%'
GROUP BY c.id, c.name;
```

**ข้อ 7:** หายอดขายรวมของเมนู "ลาเต้"
```sql
SELECT m.name, SUM(oi.subtotal) AS 'ยอดขายรวม'
FROM menus m
JOIN order_items oi ON m.id = oi.menu_id
WHERE m.name LIKE '%ลาเต้%'
GROUP BY m.id, m.name;
```

**ข้อ 8:** หาจำนวนแก้วที่พนักงาน "กานดา" ขายได้
```sql
SELECT s.name, SUM(oi.quantity) AS 'จำนวนแก้ว'
FROM staff s
JOIN orders o ON s.id = o.staff_id
JOIN order_items oi ON o.id = oi.order_id
WHERE s.name LIKE '%กานดา%'
GROUP BY s.id, s.name;
```

**ข้อ 13:** หาจำนวนแก้วที่ขายในเดือน "ธ.ค."
```sql
SELECT MONTH(o.order_date) AS 'เดือน', SUM(oi.quantity) AS 'จำนวนแก้ว'
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE MONTH(o.order_date) = 12
GROUP BY MONTH(o.order_date);
```

---

## 🎯 เคล็ดลับเตรียมสอบ SQL

### 📌 หัวข้อที่มักออกสอบ

1. **GROUP BY + Aggregate Functions** (ออกบ่อย ⭐⭐⭐⭐⭐)
   - นับจำนวน (COUNT), หาผลรวม (SUM), ค่าเฉลี่ย (AVG)
   - ต้องเข้าใจว่าคอลัมน์ไหนต้องอยู่ใน GROUP BY

2. **JOIN** (ออกบ่อย ⭐⭐⭐⭐⭐)
   - แตกต่างระหว่าง INNER JOIN vs LEFT JOIN
   - การเชื่อมหลายตาราง (3-4 ตาราง)

3. **CASE WHEN** (ออกบ่อย ⭐⭐⭐⭐)
   - สร้างคอลัมน์ใหม่ตามเงื่อนไข
   - ลำดับเงื่อนไขสำคัญ (เข้มงวดก่อน)

4. **Subquery** (ออกบ่อย ⭐⭐⭐)
   - ใน SELECT, FROM, WHERE
   - หาค่าเฉลี่ยแล้วเปรียบเทียบ

5. **Window Functions** (ออกปานกลาง ⭐⭐⭐)
   - RANK(), ROW_NUMBER(), PARTITION BY
   - ความแตกต่างของแต่ละฟังก์ชัน

6. **CTE** (ออกบ่อยในข้อสอบยาก ⭐⭐)
   - WITH ... AS (...)
   - ใช้หลาย CTE ต่อกัน

### ✏️ คำถามตัวอย่างที่น่าจะออกสอบ

**ข้อ 1:** เขียน SQL หายอดขายรวมของแต่ละหมวดหมู่สินค้า เรียงจากมากไปน้อย
<details>
<summary>คำตอบ</summary>

```sql
SELECT
    c.name AS category,
    SUM(oi.subtotal) AS total_sales
FROM categories c
JOIN menus m ON c.id = m.category_id
JOIN order_items oi ON m.id = oi.menu_id
GROUP BY c.id, c.name
ORDER BY total_sales DESC;
```
</details>

**ข้อ 2:** หาพนักงานที่มียอดขายมากกว่าค่าเฉลี่ยของทุกคน
<details>
<summary>คำตอบ</summary>

```sql
SELECT
    s.name,
    SUM(o.total_amount) AS total_sales
FROM staff s
JOIN orders o ON s.id = o.staff_id
GROUP BY s.id, s.name
HAVING SUM(o.total_amount) > (
    SELECT AVG(total_sales)
    FROM (
        SELECT SUM(o2.total_amount) AS total_sales
        FROM staff s2
        JOIN orders o2 ON s2.id = o2.staff_id
        GROUP BY s2.id
    ) AS avg_calc
);
```
</details>

**ข้อ 3:** จัดอันดับสินค้าขายดีในแต่ละหมวดหมู่
<details>
<summary>คำตอบ</summary>

```sql
SELECT
    c.name AS category,
    m.name AS product,
    SUM(oi.quantity) AS qty_sold,
    RANK() OVER (PARTITION BY c.id ORDER BY SUM(oi.quantity) DESC) AS rank_in_category
FROM categories c
JOIN menus m ON c.id = m.category_id
JOIN order_items oi ON m.id = oi.menu_id
GROUP BY c.id, c.name, m.id, m.name
ORDER BY c.name, rank_in_category;
```
</details>

### 💪 วิธีฝึกเพิ่มเติม

1. **ลองเขียนจากหัว** - อย่าดูโค้ด ให้ดูแค่โจทย์
2. **ลองแก้ไข** - เปลี่ยนเงื่อนไข เปลี่ยนการเรียงลำดับ
3. **อธิบายให้คนอื่นฟัง** - ถ้าอธิบายได้ แสดงว่าเข้าใจแล้ว
4. **ลองรันจริง** - ไปที่ http://localhost/coffeeshop_analytics/reports.php

### 🚨 ข้อผิดพลาดที่พบบ่อย

❌ ลืมใส่คอลัมน์ใน GROUP BY
```sql
-- ผิด
SELECT name, category, SUM(price)
FROM products
GROUP BY name;  -- ลืม category

-- ถูก
SELECT name, category, SUM(price)
FROM products
GROUP BY name, category;
```

❌ ใช้ WHERE แทน HAVING
```sql
-- ผิด - WHERE ไม่สามารถใช้กับ Aggregate Functions
SELECT category, COUNT(*) as cnt
FROM products
WHERE cnt > 5  -- ❌ ผิด
GROUP BY category;

-- ถูก
SELECT category, COUNT(*) as cnt
FROM products
GROUP BY category
HAVING cnt > 5;  -- ✅ ถูก
```

❌ ลืม DISTINCT ใน COUNT
```sql
-- อาจผิด - นับซ้ำถ้าออเดอร์เดียวกันมีหลายรายการ
SELECT COUNT(order_id) FROM order_items;

-- ถูก - นับออเดอร์ที่ไม่ซ้ำ
SELECT COUNT(DISTINCT order_id) FROM order_items;
```

---

## 🎓 แหล่งศึกษาเพิ่มเติม

- **ลองรันจริง:** http://localhost/coffeeshop_analytics/reports.php
- **เอกสาร MySQL:** https://dev.mysql.com/doc/
- **ฝึกหัด SQL Online:** https://www.w3schools.com/sql/
- **ตัวอย่างโค้ด:** `api/reports.php` (โค้ดจริงที่ใช้ในระบบ)

---

คุณสามารถนำคำสั่งเหล่านี้ไปทดลองรัน ปรับวันที่ หรือพัฒนาต่อยอดเพื่อสร้างรายงานรูปแบบอื่น ๆ ได้ตามต้องการ หากต้องการคำอธิบายเพิ่มเติมของรายงานใด สามารถกลับไปดูส่วน UI ใน `reports.php` และ JavaScript ที่เกี่ยวข้องใน `js/reports.js` / `js/simple_reports.js` เพื่อเห็นการเชื่อมโยงครบถ้วน

**ขอให้สอบผ่านด้วยคะแนนดีนะครับ! 🎉**
