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
    </style>
</head>
<body>
<div class="container">
    <h1>Подключение к AmoCRM</h1>
    <p>Нажмите на кнопку ниже для авторизации:</p>

    {!! $oauthButton !!}
</div>

<script>
    function handleOauthError(error) {
        console.error('OAuth error:', error);
        alert('Ошибка авторизации: ' + error);
    }

    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'amocrm_oauth') {
            if (event.data.success) {
                alert('Авторизация успешна!');
                window.location.href = '{{ route('dashboard') }}';
            } else {
                alert('Ошибка: ' + event.data.error);
            }
        }
    });
</script>
</body>
</html>
