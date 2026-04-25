<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Калькулятор</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .calculator {
            background: white;
            border-radius: 8px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #ddd;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #555;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #eee;
        }
        .result-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .error {
            background: #fee;
            color: #c00;
            border-color: #fcc;
        }
        .error .result-value {
            color: #c00;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="calculator">
    <h1>Калькулятор</h1>
    
    <form method="POST">
        <div class="form-group">
            <label>Первое число</label>
            <input type="number" name="num1" step="any" 
                   value="<?php echo isset($_POST['num1']) ? htmlspecialchars($_POST['num1']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Второе число</label>
            <input type="number" name="num2" step="any" 
                   value="<?php echo isset($_POST['num2']) ? htmlspecialchars($_POST['num2']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Операция</label>
            <select name="operation">
                <option value="+">Сложение (+)</option>
                <option value="-">Вычитание (-)</option>
                <option value="*">Умножение (*)</option>
                <option value="/">Деление (/)</option>
                <option value="%">Остаток от деления (%)</option>
                <option value="^">Возведение в степень (^)</option>
            </select>
        </div>
        
        <button type="submit">Вычислить</button>
    </form>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $num1 = $_POST['num1'];
        $num2 = $_POST['num2'];
        $operation = $_POST['operation'];
        $result = null;
        $error = null;
        
        if ($num1 === '' || $num2 === '') {
            $error = 'Заполните все поля';
        } elseif (!is_numeric($num1) || !is_numeric($num2)) {
            $error = 'Введите числа';
        } else {
            $num1 = floatval($num1);
            $num2 = floatval($num2);
            
            switch ($operation) {
                case '+':
                    $result = $num1 + $num2;
                    break;
                case '-':
                    $result = $num1 - $num2;
                    break;
                case '*':
                    $result = $num1 * $num2;
                    break;
                case '/':
                    if ($num2 == 0) {
                        $error = 'Деление на ноль';
                    } else {
                        $result = $num1 / $num2;
                    }
                    break;
                case '%':
                    if ($num2 == 0) {
                        $error = 'Деление на ноль';
                    } else {
                        $result = $num1 % $num2;
                    }
                    break;
                case '^':
                    $result = pow($num1, $num2);
                    break;
                default:
                    $error = 'Неизвестная операция';
            }
        }
        
        if ($error) {
            echo '<div class="result error">';
            echo '<div class="result-value">' . $error . '</div>';
            echo '</div>';
        } elseif (isset($result)) {
            echo '<div class="result">';
            echo '<div class="result-value">' . $result . '</div>';
            echo '</div>';
        }
    }
    ?>
</div>
</body>
</html>