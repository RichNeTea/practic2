<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmt = $pdo->query("SELECT city FROM weather_data ORDER BY city");
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($cities, JSON_UNESCAPED_UNICODE);
?>