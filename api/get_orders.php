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

    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $paymentType = isset($_GET['payment_type']) ? $_GET['payment_type'] : '';

    $offset = ($page - 1) * $limit;

    // Build WHERE clause
    $whereConditions = ["1=1"];
    $params = [];

    if ($search) {
        $whereConditions[] = "(o.id LIKE ? OR c.name LIKE ? OR s.name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if ($startDate) {
        $whereConditions[] = "DATE(o.order_date) >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $whereConditions[] = "DATE(o.order_date) <= ?";
        $params[] = $endDate;
    }

    if ($paymentType) {
        $whereConditions[] = "o.payment_type = ?";
        $params[] = $paymentType;
    }

    $whereClause = implode(" AND ", $whereConditions);

    // Count total records
    $countQuery = "
        SELECT COUNT(*) as total
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN staff s ON o.staff_id = s.id
        WHERE $whereClause
    ";

    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get orders with details
    $query = "
        SELECT
            o.id,
            o.order_date,
            o.order_time,
            o.total_amount,
            o.payment_type,
            c.id as customer_id,
            c.name as customer_name,
            c.phone as customer_phone,
            s.id as staff_id,
            s.name as staff_name,
            s.position as staff_position,
            COUNT(oi.id) as items_count
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN staff s ON o.staff_id = s.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE $whereClause
        GROUP BY o.id, o.order_date, o.order_time, o.total_amount, o.payment_type,
                 c.id, c.name, c.phone, s.id, s.name, s.position
        ORDER BY o.order_date DESC, o.order_time DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $orders,
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total" => (int)$totalRecords,
            "total_pages" => ceil($totalRecords / $limit)
        ]
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
