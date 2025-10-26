-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 11:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coffeeshop_analytics`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateTransactionData` ()   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE random_date DATE;
    DECLARE random_time TIME;
    DECLARE random_staff INT;
    DECLARE random_payment VARCHAR(10);
    DECLARE order_num VARCHAR(20);
    DECLARE order_total DECIMAL(10,2);
    DECLARE order_id_var INT;
    DECLARE items_count INT;
    DECLARE j INT;
    DECLARE random_menu INT;
    DECLARE random_qty INT;
    DECLARE menu_price DECIMAL(8,2);
    DECLARE random_customer_id INT DEFAULT NULL;
    DECLARE customer_type_var VARCHAR(10) DEFAULT 'guest';
    DECLARE points_earned_var INT DEFAULT 0;

    -- สร้าง 150 ออเดอร์เพื่อให้มีข้อมูลเพียงพอสำหรับการวิเคราะห์
    WHILE i <= 150 DO
        -- สุ่มวันที่ย้อนหลัง 90 วัน
        SET random_date = DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 90) DAY);

        -- สุ่มเวลาในช่วงเปิดร้าน (06:00-22:00)
        SET random_time = TIME(CONCAT(
            LPAD(6 + FLOOR(RAND() * 16), 2, '0'), ':',
            LPAD(FLOOR(RAND() * 60), 2, '0'), ':',
            LPAD(FLOOR(RAND() * 60), 2, '0')
        ));

        -- สุ่มพนักงาน (1-3, เพราะมี 3 คนที่ position cashier/barista)
        SET random_staff = 1 + FLOOR(RAND() * 3);

        -- สุ่มประเภทการชำระเงิน (70% เงินสด, 20% QR, 10% Online)
        SET random_payment = CASE
            WHEN RAND() < 0.7 THEN 'cash'
            WHEN RAND() < 0.9 THEN 'qr'
            ELSE 'online'
        END;

        -- สร้างหมายเลขออเดอร์
        SET order_num = CONCAT('ORD', LPAD(i, 6, '0'));

        -- เริ่มต้นยอดรวม
        SET order_total = 0;

        -- สุ่มลูกค้า (70% สมาชิก, 30% ลูกค้าทั่วไป)
        SET random_customer_id = NULL;
        SET customer_type_var = 'guest';
        SET points_earned_var = 0;

        -- สุ่มว่าเป็นสมาชิกหรือไม่
        IF RAND() < 0.7 THEN
            -- เป็นสมาชิก - สุ่มเลือกจากลูกค้าที่มี
            SET random_customer_id = 1 + FLOOR(RAND() * 10);  -- สุ่มลูกค้า ID 1-10
            SET customer_type_var = 'member';
        END IF;

        -- Insert ออเดอร์ (ยังไม่มียอดรวม)
        INSERT INTO orders (order_number, staff_id, customer_id, customer_type, total_amount, payment_type, order_date, order_time)
        VALUES (order_num, random_staff, random_customer_id, customer_type_var, 0, random_payment, random_date, random_time);

        SET order_id_var = LAST_INSERT_ID();

        -- สุ่มจำนวนรายการสินค้าในออเดอร์ (1-5 รายการ)
        SET items_count = 1 + FLOOR(RAND() * 5);
        SET j = 1;

        -- สร้างรายการสินค้าในออเดอร์
        WHILE j <= items_count DO
            -- สุ่มเมนู (1-11 ตามที่เรามี)
            SET random_menu = 1 + FLOOR(RAND() * 11);

            -- สุ่มจำนวน (1-3)
            SET random_qty = 1 + FLOOR(RAND() * 3);

            -- ดึงราคาจากตารางเมนู
            SELECT price INTO menu_price FROM menus WHERE id = random_menu;

            -- Insert รายการสินค้า
            INSERT INTO order_items (order_id, menu_id, quantity, unit_price, subtotal)
            VALUES (order_id_var, random_menu, random_qty, menu_price, menu_price * random_qty);

            -- เพิ่มยอดรวม
            SET order_total = order_total + (menu_price * random_qty);

            SET j = j + 1;
        END WHILE;

        -- คำนวณแต้มที่ได้รับ (1 แต้มต่อ 10 บาท)
        SET points_earned_var = FLOOR(order_total / 10);

        -- อัปเดตยอดรวมและแต้มในออเดอร์
        UPDATE orders SET total_amount = order_total, points_earned = points_earned_var WHERE id = order_id_var;

        -- อัปเดตข้อมูลลูกค้าสมาชิก (ถ้าเป็นสมาชิก)
        IF random_customer_id IS NOT NULL THEN
            UPDATE customers
            SET
                points = points + points_earned_var,
                total_spent = total_spent + order_total,
                visit_count = visit_count + 1,
                last_visit = random_date
            WHERE id = random_customer_id;
        END IF;

        SET i = i + 1;
    END WHILE;

    -- เพิ่มข้อมูลในวันนี้ (5-10 ออเดอร์)
    SET i = 1;
    WHILE i <= 10 DO
        SET random_time = TIME(CONCAT(
            LPAD(6 + FLOOR(RAND() * 16), 2, '0'), ':',
            LPAD(FLOOR(RAND() * 60), 2, '0'), ':',
            LPAD(FLOOR(RAND() * 60), 2, '0')
        ));

        SET random_staff = 1 + FLOOR(RAND() * 3);
        SET random_payment = CASE
            WHEN RAND() < 0.7 THEN 'cash'
            WHEN RAND() < 0.9 THEN 'qr'
            ELSE 'online'
        END;

        SET order_num = CONCAT('TODAY', LPAD(i, 3, '0'));
        SET order_total = 0;

        INSERT INTO orders (order_number, staff_id, total_amount, payment_type, order_date, order_time)
        VALUES (order_num, random_staff, 0, random_payment, CURDATE(), random_time);

        SET order_id_var = LAST_INSERT_ID();
        SET items_count = 1 + FLOOR(RAND() * 4);
        SET j = 1;

        WHILE j <= items_count DO
            SET random_menu = 1 + FLOOR(RAND() * 11);
            SET random_qty = 1 + FLOOR(RAND() * 3);

            SELECT price INTO menu_price FROM menus WHERE id = random_menu;

            INSERT INTO order_items (order_id, menu_id, quantity, unit_price, subtotal)
            VALUES (order_id_var, random_menu, random_qty, menu_price, menu_price * random_qty);

            SET order_total = order_total + (menu_price * random_qty);
            SET j = j + 1;
        END WHILE;

        UPDATE orders SET total_amount = order_total WHERE id = order_id_var;
        SET i = i + 1;
    END WHILE;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'เครื่องดื่มร้อน', 'กาแฟ ชา และเครื่องดื่มร้อนอื่นๆ', '2025-09-20 07:34:22'),
(2, 'เครื่องดื่มเย็น', 'เครื่องดื่มเย็น น้ำปั่น ชาเย็น', '2025-09-20 07:34:22'),
(3, 'อาหาร', 'อาหารว่าง ขนมปัง แซนวิช', '2025-09-20 07:34:22'),
(4, 'ของหวาน1', 'เค้ก คุกกี้ ของหวานต่างๆ', '2025-09-20 07:34:22'),
(5, 'เครื่องดืมชู', 'เครื่องดืมชู', '2025-09-20 13:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `points` int(11) DEFAULT 0,
  `total_spent` decimal(12,2) DEFAULT 0.00,
  `visit_count` int(11) DEFAULT 0,
  `member_since` date DEFAULT curdate(),
  `last_visit` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `points`, `total_spent`, `visit_count`, `member_since`, `last_visit`, `is_active`, `created_at`) VALUES
(1, 'สมชาย ใจดี', '081-234-5678', 155, 2805.00, 17, '2024-10-01', '2025-09-20', 1, '2025-09-20 07:34:22'),
(2, 'สมหญิง สวยงาม', '082-345-6789', 89, 1890.00, 12, '2024-10-15', '2024-12-10', 1, '2025-09-20 07:34:22'),
(3, 'วิชัย รวยเร็ว', '083-456-7890', 245, 5670.00, 28, '2024-09-20', '2024-12-14', 1, '2025-09-20 07:34:22'),
(4, 'มานะ ขยันทำงาน', '084-567-8901', 67, 1230.00, 8, '2024-11-01', '2024-12-12', 1, '2025-09-20 07:34:22'),
(5, 'สุกัญญา อร่อยดี', '085-678-9012', 156, 3240.00, 19, '2024-10-10', '2024-12-13', 1, '2025-09-20 07:34:22'),
(6, 'ประยุทธ์ กินเก่ง', '086-789-0123', 78, 1567.00, 10, '2024-11-15', '2024-12-11', 1, '2025-09-20 07:34:22'),
(7, 'อัญชลี ชอบกาแฟ', '087-890-1234', 203, 4890.00, 24, '2024-09-30', '2024-12-16', 1, '2025-09-20 07:34:22'),
(8, 'สมศักดิ์ ดื่มทุกวัน', '088-901-2345', 312, 7850.00, 35, '2024-09-01', '2024-12-16', 1, '2025-09-20 07:34:22'),
(9, 'นัจวา กาแฟรัก', '089-012-3456', 45, 890.00, 6, '2024-11-20', '2024-12-09', 1, '2025-09-20 07:34:22'),
(10, 'ธนวัฒน์ เบิร์นเงิน', '090-123-4567', 189, 4120.00, 22, '2024-10-05', '2024-12-15', 1, '2025-09-20 07:34:22'),
(11, 'ทดสอบ', '0123456789', 118, 1185.00, 4, '2025-09-20', '2025-10-26', 1, '2025-09-20 08:05:20'),
(12, 'เทส', '0888888888', 0, 0.00, 0, '2025-10-26', NULL, 1, '2025-10-26 07:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `category_id`, `price`, `is_active`, `created_at`) VALUES
(1, 'Americano', 1, 45.00, 1, '2025-09-20 07:34:22'),
(2, 'Cappuccino', 1, 55.00, 1, '2025-09-20 07:34:22'),
(3, 'Latte', 1, 60.00, 1, '2025-09-20 07:34:22'),
(4, 'Espresso', 1, 40.00, 1, '2025-09-20 07:34:22'),
(5, 'Iced Coffee', 2, 50.00, 1, '2025-09-20 07:34:22'),
(6, 'Green Tea Latte', 2, 65.00, 1, '2025-09-20 07:34:22'),
(7, 'Chocolate Frappe', 2, 70.00, 1, '2025-09-20 07:34:22'),
(8, 'Club Sandwich', 3, 120.00, 1, '2025-09-20 07:34:22'),
(9, 'Croissant', 3, 85.00, 1, '2025-09-20 07:34:22'),
(10, 'Cheesecake', 4, 95.00, 1, '2025-09-20 07:34:22'),
(11, 'Chocolate Brownie', 4, 75.00, 1, '2025-09-20 07:34:22'),
(12, 'น้ำส้ม', 2, 75.00, 1, '2025-09-20 13:52:17'),
(13, 'ชาเขียวปั่น', 2, 55.00, 1, '2025-09-20 13:53:27'),
(14, 'แมว', 3, 50.00, 1, '2025-10-05 11:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_type` enum('member','guest') DEFAULT 'guest',
  `total_amount` decimal(10,2) NOT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_used` int(11) DEFAULT 0,
  `payment_type` enum('cash','qr','online') NOT NULL,
  `order_date` date NOT NULL,
  `order_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `staff_id`, `customer_id`, `customer_type`, `total_amount`, `points_earned`, `points_used`, `payment_type`, `order_date`, `order_time`, `created_at`) VALUES
(1, 'SPECIAL001', 1, NULL, 'guest', 125.00, 0, 0, 'cash', '2024-12-25', '10:30:00', '2025-09-20 07:34:22'),
(2, 'SPECIAL002', 2, NULL, 'guest', 250.00, 0, 0, 'qr', '2024-12-31', '23:45:00', '2025-09-20 07:34:22'),
(3, 'SPECIAL003', 3, NULL, 'guest', 89.00, 0, 0, 'online', '2025-01-01', '08:15:00', '2025-09-20 07:34:22'),
(4, 'ORD202509200001', 3, NULL, 'guest', 0.00, 0, 0, 'cash', '2025-09-20', '14:55:54', '2025-09-20 07:55:54'),
(5, 'ORD202509200002', 3, NULL, 'guest', 0.00, 0, 0, 'cash', '2025-09-20', '14:55:56', '2025-09-20 07:55:56'),
(6, 'ORD202509200003', 3, NULL, 'guest', 0.00, 0, 0, 'cash', '2025-09-20', '14:55:59', '2025-09-20 07:55:59'),
(7, 'ORD202509200004', 3, NULL, 'guest', 475.00, 47, 0, 'qr', '2025-09-20', '14:57:53', '2025-09-20 07:57:53'),
(8, 'ORD202509200005', 3, NULL, 'guest', 40.00, 4, 0, 'cash', '2025-09-20', '14:58:11', '2025-09-20 07:58:11'),
(9, 'ORD202509200006', 3, NULL, 'guest', 40.00, 4, 0, 'cash', '2025-09-20', '14:58:14', '2025-09-20 07:58:14'),
(10, 'ORD202509200007', 3, NULL, 'guest', 40.00, 4, 0, 'cash', '2025-09-20', '14:58:17', '2025-09-20 07:58:17'),
(11, 'ORD202509200008', 3, NULL, 'guest', 40.00, 4, 0, 'qr', '2025-09-20', '14:58:22', '2025-09-20 07:58:22'),
(12, 'ORD202509200009', 3, NULL, 'guest', 115.00, 11, 0, 'cash', '2025-09-20', '14:58:40', '2025-09-20 07:58:40'),
(13, 'ORD202509200010', 4, NULL, 'guest', 85.00, 8, 0, 'cash', '2025-09-20', '15:02:02', '2025-09-20 08:02:02'),
(14, 'ORD202509200011', 4, NULL, 'guest', 85.00, 8, 0, 'cash', '2025-09-20', '15:02:04', '2025-09-20 08:02:04'),
(15, 'ORD202509200012', 4, NULL, 'guest', 240.00, 24, 0, 'cash', '2025-09-20', '15:04:09', '2025-09-20 08:04:09'),
(16, 'ORD202509200013', 4, NULL, 'guest', 305.00, 30, 0, 'cash', '2025-09-20', '15:04:47', '2025-09-20 08:04:47'),
(17, 'ORD202509200014', 1, 11, 'member', 60.00, 6, 0, 'cash', '2025-09-20', '15:06:27', '2025-09-20 08:06:27'),
(18, 'CLITEST1758357655', 1, NULL, 'guest', 100.00, 10, 0, 'cash', '2025-09-20', '15:40:55', '2025-09-20 08:40:55'),
(19, 'CLITEST1758357672', 1, 1, 'member', 160.00, 16, 0, 'cash', '2025-09-20', '15:41:12', '2025-09-20 08:41:12'),
(20, 'CLITEST1758357686', 1, 1, 'member', 195.00, 19, 0, 'qr', '2025-09-20', '15:41:26', '2025-09-20 08:41:26'),
(21, 'CLITEST1758357730', 1, NULL, 'guest', 0.00, 0, 0, 'cash', '2025-09-20', '15:42:10', '2025-09-20 08:42:10'),
(23, 'ORD202509200015', 2, NULL, 'guest', 120.00, 12, 0, 'online', '2025-09-20', '20:34:57', '2025-09-20 13:34:57'),
(24, 'ORD202509200016', 2, NULL, 'guest', 50.00, 5, 0, 'qr', '2025-09-20', '20:35:09', '2025-09-20 13:35:09'),
(25, 'ORD202509200017', 2, NULL, 'guest', 50.00, 5, 0, 'cash', '2025-09-20', '20:35:21', '2025-09-20 13:35:21'),
(26, 'ORD202509200018', 3, NULL, 'guest', 55.00, 5, 0, 'cash', '2025-09-20', '21:28:18', '2025-09-20 14:28:18'),
(27, 'ORD202509200019', 3, NULL, 'guest', 60.00, 6, 0, 'cash', '2025-09-20', '21:30:35', '2025-09-20 14:30:35'),
(28, 'ORD202509200020', 3, 11, 'member', 195.00, 19, 0, 'cash', '2025-09-20', '21:31:36', '2025-09-20 14:31:36'),
(29, 'TEST1758647841', 1, NULL, 'guest', 100.00, 10, 0, 'cash', '2025-09-24', '00:17:21', '2025-09-23 17:17:21'),
(30, 'ORD202509240001', 3, NULL, 'guest', 225.00, 22, 0, 'cash', '2025-09-24', '01:06:48', '2025-09-23 18:06:48'),
(31, 'ORD202509240002', 4, NULL, 'guest', 100.00, 10, 0, 'cash', '2025-09-24', '01:07:07', '2025-09-23 18:07:07'),
(32, 'ORD202509278061', 1, NULL, 'guest', 120.00, 12, 0, 'cash', '2025-09-27', '11:06:39', '2025-09-27 09:06:39'),
(33, 'ORD202509285262', 1, NULL, 'guest', 290.00, 29, 0, 'cash', '2025-09-28', '08:24:18', '2025-09-28 06:24:18'),
(34, 'ORD202509284657', 1, NULL, 'guest', 170.00, 17, 0, 'cash', '2025-09-28', '08:29:20', '2025-09-28 06:29:20'),
(35, 'ORD202509280182', 1, NULL, 'guest', 675.00, 67, 0, 'cash', '2025-09-28', '15:19:03', '2025-09-28 13:19:03'),
(36, 'ORD202510059052', 1, NULL, 'guest', 290.00, 29, 0, 'cash', '2025-10-05', '13:01:57', '2025-10-05 11:01:57'),
(37, 'ORD202510053198', 1, NULL, 'guest', 220.00, 22, 0, 'cash', '2025-10-05', '13:02:06', '2025-10-05 11:02:06'),
(38, 'ORD202510058197', 1, NULL, 'guest', 130.00, 13, 0, 'cash', '2025-10-05', '13:02:11', '2025-10-05 11:02:11'),
(39, 'ORD202510052050', 1, NULL, 'guest', 460.00, 46, 0, 'cash', '2025-10-05', '13:02:22', '2025-10-05 11:02:22'),
(40, 'ORD202510056789', 1, NULL, 'guest', 125.00, 12, 0, 'cash', '2025-10-05', '13:02:26', '2025-10-05 11:02:26'),
(41, 'ORD202510059195', 1, NULL, 'guest', 210.00, 21, 0, 'cash', '2025-10-05', '13:02:30', '2025-10-05 11:02:30'),
(42, 'ORD202510052778', 1, NULL, 'guest', 50.00, 5, 0, 'cash', '2025-10-05', '13:15:30', '2025-10-05 11:15:30'),
(43, 'ORD202510050330', 1, NULL, 'guest', 550.00, 55, 0, 'cash', '2025-10-05', '13:23:09', '2025-10-05 11:23:09'),
(44, 'ORD202510050781', 1, 11, 'member', 640.00, 64, 0, 'cash', '2025-10-05', '14:08:35', '2025-10-05 12:08:35'),
(45, 'ORD202510262598', 1, 11, 'member', 290.00, 29, 0, 'qr', '2025-10-26', '08:28:20', '2025-10-26 07:28:20'),
(46, 'ORD202510260519', 1, NULL, 'guest', 525.00, 52, 0, 'cash', '2025-10-26', '09:40:35', '2025-10-26 08:40:35'),
(47, 'ORD202510267323', 1, NULL, 'guest', 550.00, 55, 0, 'cash', '2025-10-26', '09:41:02', '2025-10-26 08:41:02'),
(48, 'ORD202510260736', 1, NULL, 'guest', 65.00, 6, 0, 'cash', '2025-10-26', '09:49:44', '2025-10-26 08:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(3, 15, 8, 2, 120.00, 240.00),
(4, 16, 8, 1, 120.00, 120.00),
(5, 16, 4, 1, 40.00, 40.00),
(6, 16, 9, 1, 85.00, 85.00),
(7, 16, 3, 1, 60.00, 60.00),
(8, 17, 3, 1, 60.00, 60.00),
(9, 18, 1, 2, 50.00, 100.00),
(10, 19, 1, 2, 50.00, 100.00),
(11, 19, 2, 1, 60.00, 60.00),
(12, 20, 3, 3, 65.00, 195.00),
(13, 23, 8, 1, 120.00, 120.00),
(14, 24, 5, 1, 50.00, 50.00),
(15, 25, 5, 1, 50.00, 50.00),
(16, 26, 2, 1, 55.00, 55.00),
(17, 27, 3, 1, 60.00, 60.00),
(18, 28, 11, 1, 75.00, 75.00),
(19, 28, 8, 1, 120.00, 120.00),
(20, 29, 1, 2, 50.00, 100.00),
(21, 30, 12, 3, 75.00, 225.00),
(22, 31, 5, 2, 50.00, 100.00),
(23, 32, 8, 1, 120.00, 120.00),
(24, 33, 10, 1, 95.00, 95.00),
(25, 33, 11, 1, 75.00, 75.00),
(26, 33, 8, 1, 120.00, 120.00),
(27, 34, 10, 1, 95.00, 95.00),
(28, 34, 11, 1, 75.00, 75.00),
(29, 35, 12, 9, 75.00, 675.00),
(30, 36, 10, 1, 95.00, 95.00),
(31, 36, 11, 1, 75.00, 75.00),
(32, 36, 8, 1, 120.00, 120.00),
(33, 37, 8, 1, 120.00, 120.00),
(34, 37, 2, 1, 55.00, 55.00),
(35, 37, 1, 1, 45.00, 45.00),
(36, 38, 3, 1, 60.00, 60.00),
(37, 38, 7, 1, 70.00, 70.00),
(38, 39, 8, 1, 120.00, 120.00),
(39, 39, 11, 1, 75.00, 75.00),
(40, 39, 10, 1, 95.00, 95.00),
(41, 39, 1, 1, 45.00, 45.00),
(42, 39, 2, 1, 55.00, 55.00),
(43, 39, 7, 1, 70.00, 70.00),
(44, 40, 5, 1, 50.00, 50.00),
(45, 40, 12, 1, 75.00, 75.00),
(46, 41, 4, 1, 40.00, 40.00),
(47, 41, 7, 1, 70.00, 70.00),
(48, 41, 2, 1, 55.00, 55.00),
(49, 41, 1, 1, 45.00, 45.00),
(50, 42, 14, 1, 50.00, 50.00),
(51, 43, 8, 1, 120.00, 120.00),
(52, 43, 11, 1, 75.00, 75.00),
(53, 43, 10, 1, 95.00, 95.00),
(54, 43, 5, 1, 50.00, 50.00),
(55, 43, 6, 1, 65.00, 65.00),
(56, 43, 12, 1, 75.00, 75.00),
(57, 43, 7, 1, 70.00, 70.00),
(58, 44, 11, 2, 75.00, 150.00),
(59, 44, 8, 1, 120.00, 120.00),
(60, 44, 1, 2, 45.00, 90.00),
(61, 44, 14, 2, 50.00, 100.00),
(62, 44, 10, 1, 95.00, 95.00),
(63, 44, 9, 1, 85.00, 85.00),
(64, 45, 10, 1, 95.00, 95.00),
(65, 45, 11, 1, 75.00, 75.00),
(66, 45, 8, 1, 120.00, 120.00),
(67, 46, 11, 7, 75.00, 525.00),
(68, 47, 5, 11, 50.00, 550.00),
(69, 48, 6, 1, 65.00, 65.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` enum('cashier','barista','admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `position`, `is_active`, `created_at`) VALUES
(1, 'สมชาย ใจดี', 'cashier', 1, '2025-09-20 07:34:22'),
(2, 'สมหญิง รักงาน', 'cashier', 1, '2025-09-20 07:34:22'),
(3, 'วิชัย ชงเก่ง', 'barista', 1, '2025-09-20 07:34:22'),
(4, 'มานะ จัดการ', 'admin', 1, '2025-09-20 07:34:22'),
(5, 'ทดสอบ', 'cashier', 0, '2025-09-20 13:55:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
