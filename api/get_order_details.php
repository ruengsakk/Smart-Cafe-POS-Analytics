<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    $orderId = isset($_GET['id']) ? $_GET['id'] : '';

    if (!$orderId) {
        throw new Exception("Order ID is required");
    }

    // Get order details
    $orderQuery = "
        SELECT
            o.id,
            o.order_date,
            o.order_time,
            o.total_amount,
            o.payment_type,
            c.id as customer_id,
            c.name as customer_name,
            c.phone as customer_phone,
            c.points as customer_points,
            s.id as staff_id,
            s.name as staff_name,
            s.position as staff_position
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN staff s ON o.staff_id = s.id
        WHERE o.id = ?
    ";

    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Get order items
    $itemsQuery = "
        SELECT
            oi.id,
            oi.menu_id,
            oi.quantity,
            oi.unit_price,
            oi.subtotal,
            m.name as menu_name,
            c.name as category_name
        FROM order_items oi
        JOIN menus m ON oi.menu_id = m.id
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ";

    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $order['items'] = $items;

    echo json_encode([
        "success" => true,
        "data" => $order
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
