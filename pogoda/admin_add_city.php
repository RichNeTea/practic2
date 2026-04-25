<?php
require_once 'config.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_city'])) {
    $new_city = trim($_POST['new_city']);
    
    if (empty($new_city)) {
        header("Location: admin.php?error=" . urlencode('Введите название города!'));
        exit();
    }
    
    // Проверяем, существует ли уже такой город
    $check = $pdo->prepare("SELECT id FROM weather_data WHERE city = ?");
    $check->execute([$new_city]);
    
    if ($check->fetch()) {
        header("Location: admin.php?error=" . urlencode("Город '{$new_city}' уже существует!"));
        exit();
    }
    
    // Добавляем город
    $stmt = $pdo->prepare("INSERT INTO weather_data (city) VALUES (?)");
    if ($stmt->execute([$new_city])) {
        header("Location: admin.php?city_added=" . urlencode($new_city));
    } else {
        header("Location: admin.php?error=" . urlencode('Ошибка при добавлении города!'));
    }
    exit();
}

header("Location: admin.php");
exit();
?>