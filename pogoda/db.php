<?php
$host = 'localhost';
$dbname = 'weather_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Дополнительно устанавливаем кодировку
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>