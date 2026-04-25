<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: admin.php');
        exit();
    } else {
        $error = 'Неверное имя пользователя или пароль!';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для администратора | Рич Погода</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1a73e8, #0d5bbf);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 380px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .login-container h1 {
            color: #1a73e8;
            font-size: 28px;
            margin-bottom: 8px;
            text-align: center;
        }
        .login-container p {
            color: #5f6368;
            text-align: center;
            margin-bottom: 32px;
        }
        input {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 16px;
            border: 1px solid #dadce0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #1a73e8;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        button:hover {
            background: #0d5bbf;
        }
        .error {
            background: #fce8e6;
            color: #c5221f;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .user-link {
            text-align: center;
            margin-top: 20px;
        }
        .user-link a {
            color: #5f6368;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>☀️ Рич Погода</h1>
        <p>Вход для администратора</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти как администратор</button>
        </form>
        
        <div class="user-link">
            <a href="user_login.php">← Вход для пользователей</a>
        </div>
    </div>
</body>
</html>