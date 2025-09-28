<?php
// Test database connection and basic functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Coffee Shop Analytics - Database Connection Test</h2>";

// Test 1: Include database file
echo "<h3>1. Testing Database Include</h3>";
try {
    require_once 'config/database.php';
    echo "✅ Database class loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading database class: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Create database connection
echo "<h3>2. Testing Database Connection</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "✅ Database connection successful<br>";
        echo "📊 Connection info: " . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "<br>";
    } else {
        echo "❌ Database connection failed - returned null<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check if database exists and has tables
echo "<h3>3. Testing Database Structure</h3>";
try {
    // Check if database exists
    $result = $db->query("SELECT DATABASE() as db_name");
    $dbName = $result->fetch(PDO::FETCH_ASSOC);
    echo "📋 Current database: " . $dbName['db_name'] . "<br>";

    // Check if required tables exist
    $tables = ['categories', 'menus', 'customers', 'staff', 'orders', 'order_items'];
    $existingTables = [];

    foreach ($tables as $table) {
        $checkTable = $db->query("SHOW TABLES LIKE '$table'");
        if ($checkTable->rowCount() > 0) {
            $existingTables[] = $table;
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }

    if (count($existingTables) === count($tables)) {
        echo "🎉 All required tables exist!<br>";
    } else {
        echo "⚠️ Some tables are missing. You may need to run the SQL setup script.<br>";
    }

} catch (Exception $e) {
    echo "❌ Error checking database structure: " . $e->getMessage() . "<br>";
}

// Test 4: Test API endpoints
echo "<h3>4. Testing API Endpoints</h3>";

function testAPI($url, $name) {
    $fullUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $url;
    echo "🔗 Testing: $name ($url)<br>";

    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($fullUrl, false, $context);

    if ($response === false) {
        echo "❌ Failed to reach endpoint<br>";
        return false;
    }

    $json = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Valid JSON response<br>";
        return true;
    } else {
        echo "❌ Invalid JSON response: " . json_last_error_msg() . "<br>";
        echo "📄 Raw response: " . htmlspecialchars(substr($response, 0, 200)) . "...<br>";
        return false;
    }
}

$apiTests = [
    'api/get_categories.php' => 'Categories API',
    'api/get_menus.php' => 'Menus API',
    'api/get_customers.php' => 'Customers API',
    'api/reports.php?type=daily_sales' => 'Reports API'
];

$passedTests = 0;
foreach ($apiTests as $url => $name) {
    if (testAPI($url, $name)) {
        $passedTests++;
    }
    echo "<br>";
}

// Test 5: Summary
echo "<h3>5. Test Summary</h3>";
echo "✅ API Tests Passed: $passedTests/" . count($apiTests) . "<br>";

if ($passedTests === count($apiTests)) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "🎉 <strong>All tests passed!</strong> Your Coffee Shop Analytics system should work correctly.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "⚠️ <strong>Some tests failed.</strong> Please check:";
    echo "<ul>";
    echo "<li>XAMPP/WAMP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Database 'coffeeshop_analytics' exists</li>";
    echo "<li>Tables are created (check coffeeshop_analytics.sql)</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<br><br><a href='index.php' class='btn btn-primary'>🏠 Back to POS System</a>";
echo " <a href='reports.php' class='btn btn-info'>📊 View Reports</a>";
?>