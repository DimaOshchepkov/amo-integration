<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подключение AmoCRM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2594e0;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1a7bc0;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Подключение к AmoCRM</h1>
    <p>Выберите способ авторизации:</p>

    <a href="{{ route('amocrm.connect', ['button' => 1]) }}" class="btn">
        Авторизация через виджет (popup)
    </a>

    <a href="{{ route('amocrm.connect') }}" class="btn btn-secondary">
        Авторизация через редирект
    </a>
</div>
</body>
</html>
