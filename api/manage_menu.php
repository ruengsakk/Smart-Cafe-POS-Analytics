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

// GET - ดึงข้อมูลเมนู
function handleGet($db) {
    if (isset($_GET['id'])) {
        // Get single menu
        $query = "SELECT m.*, c.name as category_name
                  FROM menus m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();

        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu) {
            ob_clean();
            echo json_encode(array(
                "success" => true,
                "data" => $menu
            ));
        } else {
            throw new Exception("Menu not found");
        }
    } else {
        // Get all menus
        $query = "SELECT m.*, c.name as category_name
                  FROM menus m
                  LEFT JOIN categories c ON m.category_id = c.id
                  ORDER BY m.id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_clean();
        echo json_encode(array(
            "success" => true,
            "data" => $menus
        ));
    }
}

// POST - เพิ่มเมนูใหม่
function handlePost($db) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['name']) || !isset($data['category_id']) || !isset($data['price'])) {
        throw new Exception("Missing required fields");
    }

    $query = "INSERT INTO menus (name, category_id, price, is_active)
              VALUES (:name, :category_id, :price, :is_active)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':is_active', $data['is_active']);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Menu created successfully",
            "id" => $db->lastInsertId()
        ));
    } else {
        throw new Exception("Failed to create menu");
    }
}

// PUT - แก้ไขเมนู
function handlePut($db) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['category_id']) || !isset($data['price'])) {
        throw new Exception("Missing required fields");
    }

    $query = "UPDATE menus
              SET name = :name,
                  category_id = :category_id,
                  price = :price,
                  is_active = :is_active
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':is_active', $data['is_active']);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Menu updated successfully"
        ));
    } else {
        throw new Exception("Failed to update menu");
    }
}

// DELETE - ลบเมนู (soft delete)
function handleDelete($db) {
    if (!isset($_GET['id'])) {
        throw new Exception("Menu ID is required");
    }

    // Soft delete - set is_active = 0
    $query = "UPDATE menus SET is_active = 0 WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(array(
            "success" => true,
            "message" => "Menu deleted successfully"
        ));
    } else {
        throw new Exception("Failed to delete menu");
    }
}
?>
