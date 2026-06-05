<?php

    session_start();

    require_once 'api/User.php';

    $canWrite = false;

    if (isset($_SESSION['user_id'])) {
        $user = findUserId($_SESSION['user_id']);
        if ($user){
            $regDate = strtotime($user['registrationdate']);
            $currentDate = time();
            $timeDiff = $currentDate - $regDate;
            $hoursPassed = $timeDiff / 3600;
            if ($hoursPassed >= 24) {
                $canWrite = true;
            }
        }
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Главная</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($canWrite): ?>
                    <button onclick="location.href='write.html'" class="writebutton">
                        Начать творить
                    </button>
                <?php else: ?>
                    <button onclick="alert('Новые пользователи могут начать творить только через сутки после регистрации.')" class="writebutton">
                        Начать творить
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <button onclick="alert('Пожалуйста, авторизуйтесь, чтобы начать творить.')" class="writebutton">
                    Начать творить
                </button>
        <?php endif; ?>
        <div class="page">
            
            <div class="headline">
                <a href="index.php">Главная</a> <a href="authors.php">Авторы</a> <a href="poetry.php">Произведения</a> <a href="login.php">Аккаунт</a>
                <hr>
            </div>

            <div class = "center">
                <h1>Стихотвория</h1>
            </div>

            <div class = "copyright">
                <hr>
                Все права защищены © 2026
            </div>
            
        </div>
        
        <script src="script.js"></script>
    </body>
</html>