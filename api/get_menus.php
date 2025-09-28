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
    $categoryId = isset($_GET['category']) ? $_GET['category'] : null;

    if ($categoryId) {
        $query = "SELECT m.id, m.name, m.price, c.name as category_name
                  FROM menus m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.category_id = :category_id AND m.is_active = 1
                  ORDER BY m.name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $categoryId);
    } else {
        $query = "SELECT m.id, m.name, m.price, c.name as category_name
                  FROM menus m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.is_active = 1
                  ORDER BY c.name, m.name";
        $stmt = $db->prepare($query);
    }

    $stmt->execute();

    $menus = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $menus[] = $row;
    }

    // Clear output buffer and send response
    ob_clean();
    echo json_encode($menus);

} catch(Exception $exception) {
    // Clear any output buffer
    ob_clean();

    // Send JSON error response
    http_response_code(500);
    echo json_encode(array(
        "error" => $exception->getMessage(),
        "success" => false
    ));
}

// End output buffering
ob_end_flush();
?>