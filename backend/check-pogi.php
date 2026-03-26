<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$connection = $db->getConnection();

$query = "SELECT id, username FROM users WHERE username = 'pogi' OR username = 'daowo' OR username LIKE '%pogi%'";
$stmt = $connection->prepare($query);
if ($stmt && $stmt->execute()) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($rows) . " users with 'pogi' in username:\n";
    var_dump($rows);
    
    if (count($rows) > 0) {
        echo "\nUsernames found: ";
        foreach ($rows as $row) {
            echo "'" . $row['username'] . "' ";
        }
        echo "\n";
    }
} else {
    echo "Query failed\n";
}
?>
