<?php
require_once 'db.php';

echo "<h2>Проверка таблицы admins</h2>";

// Проверяем, существует ли таблица
$tables = $pdo->query("SHOW TABLES LIKE 'admins'");
if ($tables->rowCount() == 0) {
    echo "❌ Таблица 'admins' не существует!<br>";
    echo "Выполните этот SQL в phpMyAdmin:<br>";
    echo "<pre>
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (username, password) VALUES 
('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
</pre>";
    exit();
}

// Выводим всех админов
$stmt = $pdo->query("SELECT * FROM admins");
$admins = $stmt->fetchAll();

if (count($admins) == 0) {
    echo "❌ В таблице admins нет записей!<br>";
} else {
    echo "<h3>Список администраторов:</h3>";
    foreach ($admins as $admin) {
        echo "ID: {$admin['id']}, Username: {$admin['username']}<br>";
        
        // Проверяем пароль
        if (password_verify('admin123', $admin['password'])) {
            echo "✅ Пароль 'admin123' ПРАВИЛЬНЫЙ!<br><br>";
        } else {
            echo "❌ Пароль 'admin123' НЕПРАВИЛЬНЫЙ!<br>";
            // Создаём новый хеш
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "Обновите пароль этим SQL:<br>";
            echo "UPDATE admins SET password = '$new_hash' WHERE id = {$admin['id']};<br><br>";
        }
    }
}
?>