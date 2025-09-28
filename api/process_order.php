<?php
// Prevent any output before JSON response
ob_start();

// Error handling - don't display errors to browser
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Clear any previous output
ob_clean();

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['items']) || !isset($data['total_amount']) || !isset($data['payment_method'])) {
        throw new Exception("Missing required fields");
    }

    // Start transaction
    $db->beginTransaction();

    // Generate order number
    $orderNumber = 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));

    // Check if order number exists, regenerate if needed
    $checkQuery = "SELECT COUNT(*) FROM orders WHERE order_number = :order_number";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':order_number', $orderNumber);
    $checkStmt->execute();

    while ($checkStmt->fetchColumn() > 0) {
        $orderNumber = 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));
        $checkStmt->execute();
    }

    // Insert order
    $insertOrderQuery = "INSERT INTO orders (order_number, staff_id, customer_id, customer_type, total_amount, points_earned, payment_type, order_date, order_time)
                         VALUES (:order_number, :staff_id, :customer_id, :customer_type, :total_amount, :points_earned, :payment_type, :order_date, :order_time)";

    $customerType = isset($data['customer_id']) && $data['customer_id'] ? 'member' : 'guest';
    $pointsEarned = floor($data['total_amount'] / 10); // 1 point per 10 baht
    $staffId = 1; // Default staff ID, should be from session in real app

    $orderStmt = $db->prepare($insertOrderQuery);
    $orderStmt->bindParam(':order_number', $orderNumber);
    $orderStmt->bindParam(':staff_id', $staffId);
    $orderStmt->bindParam(':customer_id', $data['customer_id']);
    $orderStmt->bindParam(':customer_type', $customerType);
    $orderStmt->bindParam(':total_amount', $data['total_amount']);
    $orderStmt->bindParam(':points_earned', $pointsEarned);
    $orderStmt->bindParam(':payment_type', $data['payment_method']);
    $orderStmt->bindParam(':order_date', date('Y-m-d'));
    $orderStmt->bindParam(':order_time', date('H:i:s'));

    if (!$orderStmt->execute()) {
        throw new Exception("Failed to insert order");
    }

    $orderId = $db->lastInsertId();

    // Insert order items
    $insertItemQuery = "INSERT INTO order_items (order_id, menu_id, quantity, unit_price, subtotal)
                        VALUES (:order_id, :menu_id, :quantity, :unit_price, :subtotal)";
    $itemStmt = $db->prepare($insertItemQuery);

    foreach ($data['items'] as $item) {
        $subtotal = $item['price'] * $item['quantity'];

        $itemStmt->bindParam(':order_id', $orderId);
        $itemStmt->bindParam(':menu_id', $item['id']);
        $itemStmt->bindParam(':quantity', $item['quantity']);
        $itemStmt->bindParam(':unit_price', $item['price']);
        $itemStmt->bindParam(':subtotal', $subtotal);

        if (!$itemStmt->execute()) {
            throw new Exception("Failed to insert order item");
        }
    }

    // Update customer points and stats (if customer is a member)
    if (isset($data['customer_id']) && $data['customer_id']) {
        $updateCustomerQuery = "UPDATE customers
                               SET points = points + :points_earned,
                                   total_spent = total_spent + :total_amount,
                                   visit_count = visit_count + 1,
                                   last_visit = :visit_date
                               WHERE id = :customer_id";

        $customerStmt = $db->prepare($updateCustomerQuery);
        $customerStmt->bindParam(':points_earned', $pointsEarned);
        $customerStmt->bindParam(':total_amount', $data['total_amount']);
        $customerStmt->bindParam(':visit_date', date('Y-m-d'));
        $customerStmt->bindParam(':customer_id', $data['customer_id']);

        if (!$customerStmt->execute()) {
            throw new Exception("Failed to update customer data");
        }
    }

    // Commit transaction
    $db->commit();

    echo json_encode(array(
        "success" => true,
        "message" => "Order processed successfully",
        "order_id" => $orderNumber,
        "order_number" => $orderNumber,
        "points_earned" => $pointsEarned
    ));

} catch(Exception $exception) {
    // Rollback transaction on error
    if ($db && $db->inTransaction()) {
        $db->rollback();
    }

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

// Clear output buffer and send response
ob_end_flush();
?>