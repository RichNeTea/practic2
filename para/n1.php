<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Карточки пользователей</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 40px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .user-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .user-age {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .user-email {
            margin: 10px 0;
        }
        .user-email a {
            color: #0066cc;
            text-decoration: none;
        }
        .user-email a:hover {
            text-decoration: underline;
        }
        .user-city {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Пользователи</h1>
    <div class="users-grid">
        <?php
        $users = [
            ['name' => 'Алексей Иванов', 'age' => 28, 'email' => 'alexey@example.com', 'city' => 'Москва'],
            ['name' => 'Мария Петрова', 'age' => 24, 'email' => 'maria@example.com', 'city' => 'Санкт-Петербург'],
            ['name' => 'Дмитрий Сидоров', 'age' => 32, 'email' => 'dmitry@example.com', 'city' => 'Новосибирск'],
            ['name' => 'Елена Смирнова', 'age' => 29, 'email' => 'elena@example.com', 'city' => 'Екатеринбург'],
            ['name' => 'Михаил Козлов', 'age' => 35, 'email' => 'mikhail@example.com', 'city' => 'Казань']
        ];
        
        foreach ($users as $user) {
            echo '<div class="card">';
            echo '<div class="user-name">' . $user['name'] . '</div>';
            echo '<div class="user-age">' . $user['age'] . ' лет</div>';
            echo '<div class="user-email">Email: <a href="mailto:' . $user['email'] . '">' . $user['email'] . '</a></div>';
            echo '<div class="user-city">Город: ' . $user['city'] . '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>
</body>
</html>