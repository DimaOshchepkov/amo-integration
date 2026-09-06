<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AmoCRM подключена</title>
</head>
<body>
<div style="text-align: center; padding: 40px;">
    <h1>AmoCRM уже подключена!</h1>
    <p>Токен действителен до: <strong>{{ $expires }}</strong></p>
    <a href="{{ route('home') }}">На главную</a>
</div>
</body>
</html>
