<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT id, name FROM staff ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($staff);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
