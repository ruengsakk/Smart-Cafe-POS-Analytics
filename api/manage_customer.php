<?php
// Prevent any output before JSON response
ob_start();

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($db);
            break;

        case 'POST':
            handlePost($db);
            break;

        case 'PUT':
            handlePut($db);
            break;

        case 'DELETE':
            handleDelete($db);
            break;

        default:
            throw new Exception("Method not allowed");
    }

} catch(Exception $exception) {
    ob_clean();
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => $exception->getMessage()
    ));
}

ob_end_flush();

// GET - ดึงข้อมูลลูกค้า
function handleGet($db) {
    if (isset($_GET['id'])) {
        // Get single customer
        $query = "SELECT * FROM customers WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            // Calculate tier
            $customer['tier'] = calculateTier($customer['total_spent']);

            ob_clean();
            echo json_encode(array(
                "success" => true,
                "data" => $customer
            ));
        } else {
            throw new Exception("Customer not found");
        }
    } else {
        // Get all customers with statistics
        $query = "SELECT *,
                  CASE
                      WHEN total_spent >= 5000 THEN 'VIP'
                      WHEN total_spent >= 2000 THEN 'Gold'
                      WHEN total_spent >= 1000 THEN 'Silver'
                      ELSE 'Bronze'
                  END as tier
                  FROM customers
                  ORDER BY total_spent DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_clean();
        echo json_encode(array(
            "success" => true,
            "data" => $customers
        ));
    }
}

// POST - เพิ่มลูกค้าใหม่
function handlePost($db) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['name']) || !isset($data['phone'])) {
        throw new Exception("Missing required fields");
    }

    // Check if phone already exists
    $checkQuery = "SELECT COUNT(*) FROM customers WHERE phone = :phone";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':phone', $data['phone']);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("Phone number already exists");
    }

    $query = "INSERT INTO customers (name, phone, points, is_active)
              VALUES (:name, :phone, :points, :is_active)";

    $points = isset($data['points']) ? $data['points'] : 0;
    $is_active = isset($data['is_active']) ? $data['is_active'] : 1;

    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':points', $points);
    $stmt->bindParam(':is_active', $is_active);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Customer created successfully",
            "id" => $db->lastInsertId()
        ));
    } else {
        throw new Exception("Failed to create customer");
    }
}

// PUT - แก้ไขลูกค้า
function handlePut($db) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['phone'])) {
        throw new Exception("Missing required fields");
    }

    // Check if phone already exists for other customers
    $checkQuery = "SELECT COUNT(*) FROM customers WHERE phone = :phone AND id != :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':phone', $data['phone']);
    $checkStmt->bindParam(':id', $data['id']);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("Phone number already exists");
    }

    $query = "UPDATE customers
              SET name = :name,
                  phone = :phone,
                  points = :points,
                  is_active = :is_active
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':points', $data['points']);
    $stmt->bindParam(':is_active', $data['is_active']);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Customer updated successfully"
        ));
    } else {
        throw new Exception("Failed to update customer");
    }
}

// DELETE - ลบลูกค้า (soft delete)
function handleDelete($db) {
    if (!isset($_GET['id'])) {
        throw new Exception("Customer ID is required");
    }

    // Soft delete - set is_active = 0
    $query = "UPDATE customers SET is_active = 0 WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Customer deleted successfully"
        ));
    } else {
        throw new Exception("Failed to delete customer");
    }
}

// Helper function to calculate customer tier
function calculateTier($totalSpent) {
    if ($totalSpent >= 5000) return 'VIP';
    if ($totalSpent >= 2000) return 'Gold';
    if ($totalSpent >= 1000) return 'Silver';
    return 'Bronze';
}
?>
