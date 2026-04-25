<?php
require_once 'config.php';
checkAuth();

$cities = $pdo->query("SELECT id, city FROM weather_data ORDER BY city")->fetchAll();

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT id, city FROM weather_data WHERE city LIKE ? ORDER BY city");
    $stmt->execute(["%$search%"]);
    $cities = $stmt->fetchAll();
}

$selected_city_id = $_GET['city_id'] ?? $_POST['city_id'] ?? ($cities[0]['id'] ?? 0);
if ($selected_city_id) {
    $stmt = $pdo->prepare("SELECT * FROM weather_data WHERE id = ?");
    $stmt->execute([$selected_city_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_weather']) && $selected_city_id) {
    $update_sql = "UPDATE weather_data SET 
        current_temp = ?,
        current_condition = ?,
        feels_like = ?,
        sunrise = ?,
        sunset = ?,
        humidity = ?,
        wind_speed = ?,
        wind_direction = ?,
        pressure = ?,
        uv_index = ?,
        visibility = ?,
        dew_point = ?,
        precipitation = ?,
        wind_gusts = ?,
        snow_cover = ?,
        air_quality = ?,
        rain_chance = ?
        WHERE id = ?";
    
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([
        $_POST['current_temp'],
        $_POST['current_condition'],
        $_POST['feels_like'],
        $_POST['sunrise'],
        $_POST['sunset'],
        $_POST['humidity'],
        $_POST['wind_speed'],
        $_POST['wind_direction'],
        $_POST['pressure'],
        $_POST['uv_index'],
        $_POST['visibility'],
        $_POST['dew_point'],
        $_POST['precipitation'],
        $_POST['wind_gusts'],
        $_POST['snow_cover'],
        $_POST['air_quality'],
        $_POST['rain_chance'],
        $selected_city_id
    ]);
    
    $hourly = [];
    for ($i = 0; $i < 5; $i++) {
        if (isset($_POST["hourly_time_$i"])) {
            $hourly[] = [
                'time' => $_POST["hourly_time_$i"],
                'temp' => $_POST["hourly_temp_$i"],
                'icon' => $_POST["hourly_icon_$i"],
                'precip' => $_POST["hourly_precip_$i"]
            ];
        }
    }
    $stmt = $pdo->prepare("UPDATE weather_data SET hourly_forecast = ? WHERE id = ?");
    $stmt->execute([json_encode($hourly, JSON_UNESCAPED_UNICODE), $selected_city_id]);
    
    $week = [];
    for ($i = 0; $i < 7; $i++) {
        if (isset($_POST["week_day_$i"])) {
            $week[] = [
                'day' => $_POST["week_day_$i"],
                'icon' => $_POST["week_icon_$i"],
                'temp_max' => $_POST["week_temp_max_$i"],
                'temp_min' => $_POST["week_temp_min_$i"],
                'description' => $_POST["week_desc_$i"]
            ];
        }
    }
    $stmt = $pdo->prepare("UPDATE weather_data SET week_forecast = ? WHERE id = ?");
    $stmt->execute([json_encode($week, JSON_UNESCAPED_UNICODE), $selected_city_id]);
    
    $month = [];
    for ($i = 0; $i < 4; $i++) {
        if (isset($_POST["month_week_$i"])) {
            $month[] = [
                'day' => $_POST["month_week_$i"],
                'icon' => $_POST["month_icon_$i"],
                'temp_max' => $_POST["month_temp_max_$i"],
                'temp_min' => $_POST["month_temp_min_$i"]
            ];
        }
    }
    $stmt = $pdo->prepare("UPDATE weather_data SET month_forecast = ? WHERE id = ?");
    $stmt->execute([json_encode($month, JSON_UNESCAPED_UNICODE), $selected_city_id]);
    
    header("Location: admin.php?city_id=$selected_city_id&success=1" . ($search ? "&search=" . urlencode($search) : ""));
    exit();
}

$hourly_data = [];
$week_data = [];
$month_data = [];

if ($selected_city_id && isset($data)) {
    $hourly_data = json_decode($data['hourly_forecast'] ?? '', true);
    $week_data = json_decode($data['week_forecast'] ?? '', true);
    $month_data = json_decode($data['month_forecast'] ?? '', true);
}

if (!is_array($hourly_data)) {
    $hourly_data = [
        ['time' => '15:00', 'temp' => '+3°', 'icon' => '☁️', 'precip' => '5%'],
        ['time' => '16:00', 'temp' => '+3°', 'icon' => '☁️', 'precip' => '5%'],
        ['time' => '17:00', 'temp' => '+2°', 'icon' => '🌥️', 'precip' => '10%'],
        ['time' => '18:00', 'temp' => '+1°', 'icon' => '🌥️', 'precip' => '10%'],
        ['time' => '19:00', 'temp' => '0°', 'icon' => '🌙', 'precip' => '5%']
    ];
}

if (!is_array($week_data)) {
    $week_data = [
        ['day' => 'Понедельник', 'icon' => '☁️', 'temp_max' => '+4°', 'temp_min' => '-2°', 'description' => 'облачно'],
        ['day' => 'Вторник', 'icon' => '🌧️', 'temp_max' => '+6°', 'temp_min' => '+1°', 'description' => 'дождь'],
        ['day' => 'Среда', 'icon' => '☁️', 'temp_max' => '+7°', 'temp_min' => '+2°', 'description' => 'пасмурно'],
        ['day' => 'Четверг', 'icon' => '☀️', 'temp_max' => '+10°', 'temp_min' => '+3°', 'description' => 'солнечно'],
        ['day' => 'Пятница', 'icon' => '🌦️', 'temp_max' => '+9°', 'temp_min' => '+4°', 'description' => 'небольшой дождь'],
        ['day' => 'Суббота', 'icon' => '☀️', 'temp_max' => '+12°', 'temp_min' => '+5°', 'description' => 'солнечно'],
        ['day' => 'Воскресенье', 'icon' => '⛅', 'temp_max' => '+11°', 'temp_min' => '+4°', 'description' => 'переменная облачность']
    ];
}

if (!is_array($month_data)) {
    $month_data = [
        ['day' => '1-я неделя', 'icon' => '☁️', 'temp_max' => '+2°', 'temp_min' => '-3°'],
        ['day' => '2-я неделя', 'icon' => '☀️', 'temp_max' => '+5°', 'temp_min' => '-1°'],
        ['day' => '3-я неделя', 'icon' => '🌧️', 'temp_max' => '+4°', 'temp_min' => '+1°'],
        ['day' => '4-я неделя', 'icon' => '☀️', 'temp_max' => '+7°', 'temp_min' => '+2°']
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | Рич Погода</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: #eef2f8;
            padding: 24px;
        }
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .admin-header {
            background: #1a73e8;
            color: white;
            padding: 20px 24px;
            border-radius: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .admin-header h1 { font-size: 24px; }
        .header-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-section {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .search-section input {
            background: white;
            border: none;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            width: 200px;
            outline: none;
        }
        .search-section input:focus {
            box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
        }
        .search-section button {
            background: white;
            border: none;
            padding: 8px 18px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #1a73e8;
            transition: all 0.2s;
        }
        .search-section button:hover {
            background: #e8f0fe;
            transform: scale(1.02);
        }
        .search-section a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            margin-left: 5px;
            opacity: 0.8;
        }
        .search-section a:hover {
            opacity: 1;
        }
        .city-switcher {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .city-switcher select {
            background: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }
        .home-btn, .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            color: white;
            text-decoration: none;
        }
        .home-btn:hover, .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .add-city-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .add-city-btn:hover { background: #218838; }
        .add-city-form {
            background: #f8faff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            display: none;
        }
        .add-city-form h3 {
            color: #1a73e8;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .add-city-form input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #dadce0;
            border-radius: 30px;
            font-size: 14px;
        }
        .form-section {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .form-section h2 {
            color: #1a73e8;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e8f0fe;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 13px;
            color: #5f6368;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            padding: 10px 12px;
            border: 1px solid #dadce0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
        }
        .hour-row, .week-row, .month-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            padding: 16px;
            background: #f8faff;
            border-radius: 16px;
            margin-bottom: 12px;
        }
        .hour-row .form-group, .week-row .form-group, .month-row .form-group {
            flex: 1;
            min-width: 100px;
        }
        button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 24px;
        }
        button:hover { background: #0d5bbf; }
        .flex-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>☀️ Панель управления Рич Погода</h1>
        <div class="header-buttons">
            <form method="GET" class="search-section">
                <input type="text" name="search" placeholder="🔍 Поиск города..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Найти</button>
                <?php if ($search): ?>
                    <a href="admin.php">✖</a>
                <?php endif; ?>
            </form>
            
            <?php if (!empty($cities)): ?>
            <div class="city-switcher">
                <span>🌆</span>
                <form method="GET" style="display: inline;">
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>
                    <select name="city_id" onchange="this.form.submit()">
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo ($city['id'] == $selected_city_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php endif; ?>
            
            <button type="button" class="add-city-btn" onclick="showAddCityForm()">➕ Добавить город</button>
            <a href="index.html" class="home-btn">🏠 На главную</a>
            <a href="admin_logout.php" class="logout-btn">Выйти</a>
        </div>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success">✅ Данные для города "<?php echo htmlspecialchars($data['city'] ?? ''); ?>" успешно сохранены!</div>
    <?php endif; ?>
    <?php if (isset($_GET['city_added'])): ?>
        <div class="success">✅ Город "<?php echo htmlspecialchars($_GET['city_added']); ?>" успешно добавлен!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    
    <div id="addCityForm" class="add-city-form">
        <h3>🏙️ Добавление нового города</h3>
        <form method="POST" action="admin_add_city.php" class="flex-row" id="addCityFormSubmit">
            <input type="text" name="new_city" id="newCityName" placeholder="Название города" required>
            <button type="submit" name="add_city" style="margin-top: 0; background: #28a745;">➕ Добавить</button>
        </form>
        <p style="font-size: 12px; color: #5f6368; margin-top: 10px;">* Город будет добавлен с данными по умолчанию.</p>
    </div>
    
    <?php if ($selected_city_id && isset($data)): ?>
    <form method="POST">
        <input type="hidden" name="save_weather" value="1">
        <input type="hidden" name="city_id" value="<?php echo $selected_city_id; ?>">
        <?php if ($search): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <?php endif; ?>
        
        <div class="form-section">
            <h2>🌡️ Текущая погода — <?php echo htmlspecialchars($data['city']); ?></h2>
            <div class="form-grid">
                <div class="form-group"><label>Температура (°C)</label><input name="current_temp" value="<?php echo htmlspecialchars($data['current_temp']); ?>"></div>
                <div class="form-group">
                    <label>Состояние погоды</label>
                    <select name="current_condition">
                        <option value="Солнечно" <?php echo ($data['current_condition'] == 'Солнечно') ? 'selected' : ''; ?>>☀️ Солнечно</option>
                        <option value="Малооблачно" <?php echo ($data['current_condition'] == 'Малооблачно') ? 'selected' : ''; ?>>🌤️ Малооблачно</option>
                        <option value="Облачно с прояснениями" <?php echo ($data['current_condition'] == 'Облачно с прояснениями') ? 'selected' : ''; ?>>⛅ Облачно с прояснениями</option>
                        <option value="Пасмурно" <?php echo ($data['current_condition'] == 'Пасмурно') ? 'selected' : ''; ?>>☁️ Пасмурно</option>
                        <option value="Небольшой дождь" <?php echo ($data['current_condition'] == 'Небольшой дождь') ? 'selected' : ''; ?>>🌦️ Небольшой дождь</option>
                        <option value="Дождь" <?php echo ($data['current_condition'] == 'Дождь') ? 'selected' : ''; ?>>🌧️ Дождь</option>
                        <option value="Гроза" <?php echo ($data['current_condition'] == 'Гроза') ? 'selected' : ''; ?>>⛈️ Гроза</option>
                        <option value="Снег" <?php echo ($data['current_condition'] == 'Снег') ? 'selected' : ''; ?>>❄️ Снег</option>
                        <option value="Туман" <?php echo ($data['current_condition'] == 'Туман') ? 'selected' : ''; ?>>🌫️ Туман</option>
                    </select>
                </div>
                <div class="form-group"><label>Ощущается как (°C)</label><input name="feels_like" value="<?php echo htmlspecialchars($data['feels_like']); ?>"></div>
                <div class="form-group"><label>Восход</label><input name="sunrise" value="<?php echo htmlspecialchars($data['sunrise']); ?>"></div>
                <div class="form-group"><label>Закат</label><input name="sunset" value="<?php echo htmlspecialchars($data['sunset']); ?>"></div>
            </div>
        </div>
        
        <div class="form-section">
            <h2>📊 Детали погоды</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Влажность</label>
                    <select name="humidity">
                        <option value="30%" <?php echo ($data['humidity'] == '30%') ? 'selected' : ''; ?>>30%</option>
                        <option value="40%" <?php echo ($data['humidity'] == '40%') ? 'selected' : ''; ?>>40%</option>
                        <option value="50%" <?php echo ($data['humidity'] == '50%') ? 'selected' : ''; ?>>50%</option>
                        <option value="60%" <?php echo ($data['humidity'] == '60%') ? 'selected' : ''; ?>>60%</option>
                        <option value="70%" <?php echo ($data['humidity'] == '70%') ? 'selected' : ''; ?>>70%</option>
                        <option value="78%" <?php echo ($data['humidity'] == '78%') ? 'selected' : ''; ?>>78%</option>
                        <option value="85%" <?php echo ($data['humidity'] == '85%') ? 'selected' : ''; ?>>85%</option>
                        <option value="90%" <?php echo ($data['humidity'] == '90%') ? 'selected' : ''; ?>>90%</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ветер (скорость)</label>
                    <select name="wind_speed">
                        <option value="0 м/с" <?php echo ($data['wind_speed'] == '0 м/с') ? 'selected' : ''; ?>>0 м/с</option>
                        <option value="1 м/с" <?php echo ($data['wind_speed'] == '1 м/с') ? 'selected' : ''; ?>>1 м/с</option>
                        <option value="2 м/с" <?php echo ($data['wind_speed'] == '2 м/с') ? 'selected' : ''; ?>>2 м/с</option>
                        <option value="3 м/с" <?php echo ($data['wind_speed'] == '3 м/с') ? 'selected' : ''; ?>>3 м/с</option>
                        <option value="4 м/с" <?php echo ($data['wind_speed'] == '4 м/с') ? 'selected' : ''; ?>>4 м/с</option>
                        <option value="4.2 м/с" <?php echo ($data['wind_speed'] == '4.2 м/с') ? 'selected' : ''; ?>>4.2 м/с</option>
                        <option value="5 м/с" <?php echo ($data['wind_speed'] == '5 м/с') ? 'selected' : ''; ?>>5 м/с</option>
                        <option value="7 м/с" <?php echo ($data['wind_speed'] == '7 м/с') ? 'selected' : ''; ?>>7 м/с</option>
                        <option value="10 м/с" <?php echo ($data['wind_speed'] == '10 м/с') ? 'selected' : ''; ?>>10 м/с</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Направление ветра</label>
                    <select name="wind_direction">
                        <option value="северный" <?php echo ($data['wind_direction'] == 'северный') ? 'selected' : ''; ?>>⬆️ Северный</option>
                        <option value="северо-восточный" <?php echo ($data['wind_direction'] == 'северо-восточный') ? 'selected' : ''; ?>>↗️ Северо-восточный</option>
                        <option value="восточный" <?php echo ($data['wind_direction'] == 'восточный') ? 'selected' : ''; ?>>➡️ Восточный</option>
                        <option value="юго-восточный" <?php echo ($data['wind_direction'] == 'юго-восточный') ? 'selected' : ''; ?>>↘️ Юго-восточный</option>
                        <option value="южный" <?php echo ($data['wind_direction'] == 'южный') ? 'selected' : ''; ?>>⬇️ Южный</option>
                        <option value="юго-западный" <?php echo ($data['wind_direction'] == 'юго-западный') ? 'selected' : ''; ?>>↙️ Юго-западный</option>
                        <option value="западный" <?php echo ($data['wind_direction'] == 'западный') ? 'selected' : ''; ?>>⬅️ Западный</option>
                        <option value="северо-западный" <?php echo ($data['wind_direction'] == 'северо-западный') ? 'selected' : ''; ?>>↖️ Северо-западный</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Давление</label>
                    <select name="pressure">
                        <option value="730 мм" <?php echo ($data['pressure'] == '730 мм') ? 'selected' : ''; ?>>730 мм</option>
                        <option value="735 мм" <?php echo ($data['pressure'] == '735 мм') ? 'selected' : ''; ?>>735 мм</option>
                        <option value="740 мм" <?php echo ($data['pressure'] == '740 мм') ? 'selected' : ''; ?>>740 мм</option>
                        <option value="745 мм" <?php echo ($data['pressure'] == '745 мм') ? 'selected' : ''; ?>>745 мм</option>
                        <option value="750 мм" <?php echo ($data['pressure'] == '750 мм') ? 'selected' : ''; ?>>750 мм</option>
                        <option value="752 мм" <?php echo ($data['pressure'] == '752 мм') ? 'selected' : ''; ?>>752 мм</option>
                        <option value="755 мм" <?php echo ($data['pressure'] == '755 мм') ? 'selected' : ''; ?>>755 мм</option>
                        <option value="760 мм" <?php echo ($data['pressure'] == '760 мм') ? 'selected' : ''; ?>>760 мм</option>
                        <option value="765 мм" <?php echo ($data['pressure'] == '765 мм') ? 'selected' : ''; ?>>765 мм</option>
                        <option value="770 мм" <?php echo ($data['pressure'] == '770 мм') ? 'selected' : ''; ?>>770 мм</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>УФ-индекс</label>
                    <select name="uv_index">
                        <option value="0" <?php echo ($data['uv_index'] == '0') ? 'selected' : ''; ?>>0</option>
                        <option value="1" <?php echo ($data['uv_index'] == '1') ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($data['uv_index'] == '2') ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($data['uv_index'] == '3') ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo ($data['uv_index'] == '4') ? 'selected' : ''; ?>>4</option>
                        <option value="5" <?php echo ($data['uv_index'] == '5') ? 'selected' : ''; ?>>5</option>
                        <option value="6" <?php echo ($data['uv_index'] == '6') ? 'selected' : ''; ?>>6</option>
                        <option value="7" <?php echo ($data['uv_index'] == '7') ? 'selected' : ''; ?>>7</option>
                        <option value="8" <?php echo ($data['uv_index'] == '8') ? 'selected' : ''; ?>>8</option>
                        <option value="9" <?php echo ($data['uv_index'] == '9') ? 'selected' : ''; ?>>9</option>
                        <option value="10" <?php echo ($data['uv_index'] == '10') ? 'selected' : ''; ?>>10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Видимость</label>
                    <select name="visibility">
                        <option value="1 км" <?php echo ($data['visibility'] == '1 км') ? 'selected' : ''; ?>>1 км</option>
                        <option value="2 км" <?php echo ($data['visibility'] == '2 км') ? 'selected' : ''; ?>>2 км</option>
                        <option value="5 км" <?php echo ($data['visibility'] == '5 км') ? 'selected' : ''; ?>>5 км</option>
                        <option value="10 км" <?php echo ($data['visibility'] == '10 км') ? 'selected' : ''; ?>>10 км</option>
                        <option value="12 км" <?php echo ($data['visibility'] == '12 км') ? 'selected' : ''; ?>>12 км</option>
                        <option value="15 км" <?php echo ($data['visibility'] == '15 км') ? 'selected' : ''; ?>>15 км</option>
                        <option value="20 км" <?php echo ($data['visibility'] == '20 км') ? 'selected' : ''; ?>>20 км</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Точка росы</label>
                    <select name="dew_point">
                        <option value="-10°" <?php echo ($data['dew_point'] == '-10°') ? 'selected' : ''; ?>>-10°</option>
                        <option value="-5°" <?php echo ($data['dew_point'] == '-5°') ? 'selected' : ''; ?>>-5°</option>
                        <option value="-1°" <?php echo ($data['dew_point'] == '-1°') ? 'selected' : ''; ?>>-1°</option>
                        <option value="0°" <?php echo ($data['dew_point'] == '0°') ? 'selected' : ''; ?>>0°</option>
                        <option value="5°" <?php echo ($data['dew_point'] == '5°') ? 'selected' : ''; ?>>5°</option>
                        <option value="10°" <?php echo ($data['dew_point'] == '10°') ? 'selected' : ''; ?>>10°</option>
                        <option value="15°" <?php echo ($data['dew_point'] == '15°') ? 'selected' : ''; ?>>15°</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Осадки</label>
                    <select name="precipitation">
                        <option value="0 мм" <?php echo ($data['precipitation'] == '0 мм') ? 'selected' : ''; ?>>0 мм</option>
                        <option value="0.1 мм" <?php echo ($data['precipitation'] == '0.1 мм') ? 'selected' : ''; ?>>0.1 мм</option>
                        <option value="0.2 мм" <?php echo ($data['precipitation'] == '0.2 мм') ? 'selected' : ''; ?>>0.2 мм</option>
                        <option value="0.5 мм" <?php echo ($data['precipitation'] == '0.5 мм') ? 'selected' : ''; ?>>0.5 мм</option>
                        <option value="1 мм" <?php echo ($data['precipitation'] == '1 мм') ? 'selected' : ''; ?>>1 мм</option>
                        <option value="2 мм" <?php echo ($data['precipitation'] == '2 мм') ? 'selected' : ''; ?>>2 мм</option>
                        <option value="5 мм" <?php echo ($data['precipitation'] == '5 мм') ? 'selected' : ''; ?>>5 мм</option>
                        <option value="10 мм" <?php echo ($data['precipitation'] == '10 мм') ? 'selected' : ''; ?>>10 мм</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Порывы ветра</label>
                    <select name="wind_gusts">
                        <option value="до 3 м/с" <?php echo ($data['wind_gusts'] == 'до 3 м/с') ? 'selected' : ''; ?>>до 3 м/с</option>
                        <option value="до 5 м/с" <?php echo ($data['wind_gusts'] == 'до 5 м/с') ? 'selected' : ''; ?>>до 5 м/с</option>
                        <option value="до 8 м/с" <?php echo ($data['wind_gusts'] == 'до 8 м/с') ? 'selected' : ''; ?>>до 8 м/с</option>
                        <option value="до 10 м/с" <?php echo ($data['wind_gusts'] == 'до 10 м/с') ? 'selected' : ''; ?>>до 10 м/с</option>
                        <option value="до 12 м/с" <?php echo ($data['wind_gusts'] == 'до 12 м/с') ? 'selected' : ''; ?>>до 12 м/с</option>
                        <option value="до 15 м/с" <?php echo ($data['wind_gusts'] == 'до 15 м/с') ? 'selected' : ''; ?>>до 15 м/с</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Снежный покров</label>
                    <select name="snow_cover">
                        <option value="0 см" <?php echo ($data['snow_cover'] == '0 см') ? 'selected' : ''; ?>>0 см</option>
                        <option value="1-2 см" <?php echo ($data['snow_cover'] == '1-2 см') ? 'selected' : ''; ?>>1-2 см</option>
                        <option value="3-5 см" <?php echo ($data['snow_cover'] == '3-5 см') ? 'selected' : ''; ?>>3-5 см</option>
                        <option value="5-10 см" <?php echo ($data['snow_cover'] == '5-10 см') ? 'selected' : ''; ?>>5-10 см</option>
                        <option value="10-20 см" <?php echo ($data['snow_cover'] == '10-20 см') ? 'selected' : ''; ?>>10-20 см</option>
                        <option value="20-30 см" <?php echo ($data['snow_cover'] == '20-30 см') ? 'selected' : ''; ?>>20-30 см</option>
                        <option value="30+ см" <?php echo ($data['snow_cover'] == '30+ см') ? 'selected' : ''; ?>>30+ см</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Качество воздуха</label>
                    <select name="air_quality">
                        <option value="AQI 0-25" <?php echo ($data['air_quality'] == 'AQI 0-25') ? 'selected' : ''; ?>>AQI 0-25 (отличное)</option>
                        <option value="AQI 26-50" <?php echo ($data['air_quality'] == 'AQI 26-50') ? 'selected' : ''; ?>>AQI 26-50 (хорошее)</option>
                        <option value="AQI 34" <?php echo ($data['air_quality'] == 'AQI 34') ? 'selected' : ''; ?>>AQI 34 (хорошее)</option>
                        <option value="AQI 51-75" <?php echo ($data['air_quality'] == 'AQI 51-75') ? 'selected' : ''; ?>>AQI 51-75 (среднее)</option>
                        <option value="AQI 75-100" <?php echo ($data['air_quality'] == 'AQI 75-100') ? 'selected' : ''; ?>>AQI 75-100 (плохое)</option>
                        <option value="AQI 100+" <?php echo ($data['air_quality'] == 'AQI 100+') ? 'selected' : ''; ?>>AQI 100+ (опасное)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Вероятность дождя</label>
                    <select name="rain_chance">
                        <option value="0%" <?php echo ($data['rain_chance'] == '0%') ? 'selected' : ''; ?>>0%</option>
                        <option value="5%" <?php echo ($data['rain_chance'] == '5%') ? 'selected' : ''; ?>>5%</option>
                        <option value="8%" <?php echo ($data['rain_chance'] == '8%') ? 'selected' : ''; ?>>8%</option>
                        <option value="10%" <?php echo ($data['rain_chance'] == '10%') ? 'selected' : ''; ?>>10%</option>
                        <option value="20%" <?php echo ($data['rain_chance'] == '20%') ? 'selected' : ''; ?>>20%</option>
                        <option value="30%" <?php echo ($data['rain_chance'] == '30%') ? 'selected' : ''; ?>>30%</option>
                        <option value="40%" <?php echo ($data['rain_chance'] == '40%') ? 'selected' : ''; ?>>40%</option>
                        <option value="50%" <?php echo ($data['rain_chance'] == '50%') ? 'selected' : ''; ?>>50%</option>
                        <option value="60%" <?php echo ($data['rain_chance'] == '60%') ? 'selected' : ''; ?>>60%</option>
                        <option value="70%" <?php echo ($data['rain_chance'] == '70%') ? 'selected' : ''; ?>>70%</option>
                        <option value="80%" <?php echo ($data['rain_chance'] == '80%') ? 'selected' : ''; ?>>80%</option>
                        <option value="90%" <?php echo ($data['rain_chance'] == '90%') ? 'selected' : ''; ?>>90%</option>
                        <option value="100%" <?php echo ($data['rain_chance'] == '100%') ? 'selected' : ''; ?>>100%</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2>⏰ Почасовой прогноз (5 часов)</h2>
            <?php for ($i = 0; $i < 5; $i++): 
                $hour = $hourly_data[$i] ?? ['time' => '12:00', 'temp' => '0°', 'icon' => '☁️', 'precip' => '5%'];
            ?>
            <div class="hour-row">
                <div class="form-group"><label>Время</label><input name="hourly_time_<?php echo $i; ?>" value="<?php echo htmlspecialchars($hour['time']); ?>"></div>
                <div class="form-group"><label>Температура</label><input name="hourly_temp_<?php echo $i; ?>" value="<?php echo htmlspecialchars($hour['temp']); ?>"></div>
                <div class="form-group">
                    <label>Иконка</label>
                    <select name="hourly_icon_<?php echo $i; ?>">
                        <option value="☀️" <?php echo ($hour['icon'] == '☀️') ? 'selected' : ''; ?>>☀️ Солнце</option>
                        <option value="🌤️" <?php echo ($hour['icon'] == '🌤️') ? 'selected' : ''; ?>>🌤️ Малооблачно</option>
                        <option value="⛅" <?php echo ($hour['icon'] == '⛅') ? 'selected' : ''; ?>>⛅ Переменная облачность</option>
                        <option value="☁️" <?php echo ($hour['icon'] == '☁️') ? 'selected' : ''; ?>>☁️ Облачно</option>
                        <option value="🌥️" <?php echo ($hour['icon'] == '🌥️') ? 'selected' : ''; ?>>🌥️ Пасмурно</option>
                        <option value="🌦️" <?php echo ($hour['icon'] == '🌦️') ? 'selected' : ''; ?>>🌦️ Небольшой дождь</option>
                        <option value="🌧️" <?php echo ($hour['icon'] == '🌧️') ? 'selected' : ''; ?>>🌧️ Дождь</option>
                        <option value="⛈️" <?php echo ($hour['icon'] == '⛈️') ? 'selected' : ''; ?>>⛈️ Гроза</option>
                        <option value="❄️" <?php echo ($hour['icon'] == '❄️') ? 'selected' : ''; ?>>❄️ Снег</option>
                        <option value="🌨️" <?php echo ($hour['icon'] == '🌨️') ? 'selected' : ''; ?>>🌨️ Снегопад</option>
                        <option value="🌙" <?php echo ($hour['icon'] == '🌙') ? 'selected' : ''; ?>>🌙 Ночь</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Осадки</label>
                    <select name="hourly_precip_<?php echo $i; ?>">
                        <option value="0%" <?php echo ($hour['precip'] == '0%') ? 'selected' : ''; ?>>0%</option>
                        <option value="5%" <?php echo ($hour['precip'] == '5%') ? 'selected' : ''; ?>>5%</option>
                        <option value="10%" <?php echo ($hour['precip'] == '10%') ? 'selected' : ''; ?>>10%</option>
                        <option value="15%" <?php echo ($hour['precip'] == '15%') ? 'selected' : ''; ?>>15%</option>
                        <option value="20%" <?php echo ($hour['precip'] == '20%') ? 'selected' : ''; ?>>20%</option>
                        <option value="25%" <?php echo ($hour['precip'] == '25%') ? 'selected' : ''; ?>>25%</option>
                        <option value="30%" <?php echo ($hour['precip'] == '30%') ? 'selected' : ''; ?>>30%</option>
                        <option value="40%" <?php echo ($hour['precip'] == '40%') ? 'selected' : ''; ?>>40%</option>
                        <option value="50%" <?php echo ($hour['precip'] == '50%') ? 'selected' : ''; ?>>50%</option>
                        <option value="60%" <?php echo ($hour['precip'] == '60%') ? 'selected' : ''; ?>>60%</option>
                        <option value="70%" <?php echo ($hour['precip'] == '70%') ? 'selected' : ''; ?>>70%</option>
                        <option value="80%" <?php echo ($hour['precip'] == '80%') ? 'selected' : ''; ?>>80%</option>
                        <option value="90%" <?php echo ($hour['precip'] == '90%') ? 'selected' : ''; ?>>90%</option>
                        <option value="100%" <?php echo ($hour['precip'] == '100%') ? 'selected' : ''; ?>>100%</option>
                    </select>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        
        <div class="form-section">
            <h2>📅 Прогноз на 7 дней</h2>
            <?php for ($i = 0; $i < 7; $i++): 
                $week = $week_data[$i] ?? ['day' => 'День', 'icon' => '☁️', 'temp_max' => '+2°', 'temp_min' => '-3°', 'description' => 'облачно'];
            ?>
            <div class="week-row">
                <div class="form-group"><label>День</label><input name="week_day_<?php echo $i; ?>" value="<?php echo htmlspecialchars($week['day']); ?>"></div>
                <div class="form-group">
                    <label>Иконка</label>
                    <select name="week_icon_<?php echo $i; ?>">
                        <option value="☀️" <?php echo ($week['icon'] == '☀️') ? 'selected' : ''; ?>>☀️</option>
                        <option value="🌤️" <?php echo ($week['icon'] == '🌤️') ? 'selected' : ''; ?>>🌤️</option>
                        <option value="⛅" <?php echo ($week['icon'] == '⛅') ? 'selected' : ''; ?>>⛅</option>
                        <option value="☁️" <?php echo ($week['icon'] == '☁️') ? 'selected' : ''; ?>>☁️</option>
                        <option value="🌥️" <?php echo ($week['icon'] == '🌥️') ? 'selected' : ''; ?>>🌥️</option>
                        <option value="🌦️" <?php echo ($week['icon'] == '🌦️') ? 'selected' : ''; ?>>🌦️</option>
                        <option value="🌧️" <?php echo ($week['icon'] == '🌧️') ? 'selected' : ''; ?>>🌧️</option>
                        <option value="⛈️" <?php echo ($week['icon'] == '⛈️') ? 'selected' : ''; ?>>⛈️</option>
                        <option value="❄️" <?php echo ($week['icon'] == '❄️') ? 'selected' : ''; ?>>❄️</option>
                        <option value="🌨️" <?php echo ($week['icon'] == '🌨️') ? 'selected' : ''; ?>>🌨️</option>
                    </select>
                </div>
                <div class="form-group"><label>Макс. темп.</label><input name="week_temp_max_<?php echo $i; ?>" value="<?php echo htmlspecialchars($week['temp_max']); ?>"></div>
                <div class="form-group"><label>Мин. темп.</label><input name="week_temp_min_<?php echo $i; ?>" value="<?php echo htmlspecialchars($week['temp_min']); ?>"></div>
                <div class="form-group"><label>Описание</label><input name="week_desc_<?php echo $i; ?>" value="<?php echo htmlspecialchars($week['description']); ?>"></div>
            </div>
            <?php endfor; ?>
        </div>
        
        <div class="form-section">
            <h2>📅 Прогноз на месяц (по неделям)</h2>
            <?php for ($i = 0; $i < 4; $i++): 
                $month = $month_data[$i] ?? ['day' => 'Неделя', 'icon' => '☁️', 'temp_max' => '+2°', 'temp_min' => '-3°'];
            ?>
            <div class="month-row">
                <div class="form-group"><label>Неделя</label><input name="month_week_<?php echo $i; ?>" value="<?php echo htmlspecialchars($month['day']); ?>"></div>
                <div class="form-group">
                    <label>Иконка</label>
                    <select name="month_icon_<?php echo $i; ?>">
                        <option value="☀️" <?php echo ($month['icon'] == '☀️') ? 'selected' : ''; ?>>☀️</option>
                        <option value="🌤️" <?php echo ($month['icon'] == '🌤️') ? 'selected' : ''; ?>>🌤️</option>
                        <option value="⛅" <?php echo ($month['icon'] == '⛅') ? 'selected' : ''; ?>>⛅</option>
                        <option value="☁️" <?php echo ($month['icon'] == '☁️') ? 'selected' : ''; ?>>☁️</option>
                        <option value="🌧️" <?php echo ($month['icon'] == '🌧️') ? 'selected' : ''; ?>>🌧️</option>
                        <option value="🌦️" <?php echo ($month['icon'] == '🌦️') ? 'selected' : ''; ?>>🌦️</option>
                    </select>
                </div>
                <div class="form-group"><label>Макс. темп.</label><input name="month_temp_max_<?php echo $i; ?>" value="<?php echo htmlspecialchars($month['temp_max']); ?>"></div>
                <div class="form-group"><label>Мин. темп.</label><input name="month_temp_min_<?php echo $i; ?>" value="<?php echo htmlspecialchars($month['temp_min']); ?>"></div>
            </div>
            <?php endfor; ?>
        </div>
        
        <button type="submit">💾 Сохранить изменения для <?php echo htmlspecialchars($data['city']); ?></button>
    </form>
    <?php else: ?>
        <div class="error">❌ Города не найдены. Добавьте первый город через кнопку "Добавить город".</div>
    <?php endif; ?>
</div>

<script>
    function showAddCityForm() {
        var form = document.getElementById('addCityForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
    
    document.getElementById('addCityFormSubmit').addEventListener('submit', function(e) {
        var newCity = document.getElementById('newCityName').value.trim();
        if (!newCity) {
            e.preventDefault();
            alert('Введите название города!');
            return;
        }
        
        var select = document.querySelector('.city-switcher select');
        if (select) {
            var options = Array.from(select.options);
            var exists = options.some(opt => opt.text.toLowerCase() === newCity.toLowerCase());
            if (exists) {
                e.preventDefault();
                alert('❌ Город "' + newCity + '" уже существует в базе!');
                return;
            }
        }
    });
</script>
</body>
</html>