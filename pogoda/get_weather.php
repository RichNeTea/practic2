<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM weather_data WHERE id = 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$data['hourly_forecast'] = json_decode($data['hourly_forecast'], true);
$data['week_forecast'] = json_decode($data['week_forecast'], true);
$data['month_forecast'] = json_decode($data['month_forecast'], true);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>