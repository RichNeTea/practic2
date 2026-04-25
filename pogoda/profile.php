<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user_login.php');
    exit();
}

// Обработка смены города
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_city'])) {
    $new_city = $_POST['city'];
    $stmt = $pdo->prepare("UPDATE users SET city = ? WHERE id = ?");
    $stmt->execute([$new_city, $_SESSION['user_id']]);
    $_SESSION['user_city'] = $new_city;
    $success = 'Город успешно обновлён!';
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Список городов из БД
$cities = $pdo->query("SELECT city FROM weather_data ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | Рич Погода</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: #eef2f8;
            min-height: 100vh;
            padding: 40px;
        }
        .profile-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #1a73e8, #0d5bbf);
            padding: 40px;
            text-align: center;
            color: white;
        }
        .profile-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }
        .profile-avatar {
            background: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .profile-avatar span {
            font-size: 48px;
        }
        .profile-body {
            padding: 32px 40px;
        }
        .info-card {
            background: #f8faff;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 140px;
            font-weight: 500;
            color: #1a73e8;
        }
        .info-value {
            flex: 1;
            color: #202124;
            font-weight: 500;
        }
        .city-selector-card {
            background: #f8faff;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .city-selector-card h3 {
            color: #1a73e8;
            font-size: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .city-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .city-form select {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #dadce0;
            border-radius: 30px;
            font-size: 14px;
            background: white;
        }
        .city-form button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
        }
        .city-form button:hover {
            background: #0d5bbf;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 24px;
        }
        .btn {
            display: inline-block;
            background: #1a73e8;
            color: white;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn:hover {
            background: #0d5bbf;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #1a73e8;
            color: #1a73e8;
        }
        .btn-outline:hover {
            background: #e8f0fe;
        }
        .weather-link {
            display: inline-block;
            margin-top: 16px;
            color: #1a73e8;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar"><span>👤</span></div>
        <h1>Личный кабинет</h1>
        <p>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </div>
    
    <div class="profile-body">
        <?php if (isset($success)): ?>
            <div class="success-msg">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="info-card">
            <div class="info-row">
                <div class="info-label">Имя пользователя:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Ваш город:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['city']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Дата регистрации:</div>
                <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></div>
            </div>
        </div>
        
        <div class="city-selector-card">
            <h3>📍 Изменить город</h3>
            <form method="POST" class="city-form">
                <select name="city">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($user['city'] == $city) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_city">Сохранить</button>
            </form>
        </div>
        
        <div class="buttons">
            <a href="index.html" class="btn">🏠 На главную</a>
            <a href="user_logout.php" class="btn btn-outline">🚪 Выйти</a>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.html?city=<?php echo urlencode($user['city']); ?>" class="weather-link">
                🌍 Посмотреть погоду в <?php echo htmlspecialchars($user['city']); ?>
            </a>
        </div>
    </div>
</div>
</body>
</html>