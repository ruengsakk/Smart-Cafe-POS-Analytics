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

    $query = "SELECT id, name, description FROM categories ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $categories = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }

    // Clear output buffer and send response
    ob_clean();
    echo json_encode($categories);

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