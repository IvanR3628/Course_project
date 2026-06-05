<?php

    session_start();

    require_once 'api/UserController.php';

    $canWrite = false;

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    } else {
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

    $username = $_SESSION['user_username'];
    $id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        writeLog('Пользователь вышел из системы', $username, $id);
        $_SESSION = array();
        session_destroy();
        header('Location: login.php');
        exit;
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Аккаунт</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($canWrite): ?>
                    <button onclick="location.href='write.php'" class="writebutton">
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
                <a href="index.php">Главная</a> <a href="poetry.php">Читать</a> <a href="login.php">Аккаунт</a>
                <hr>
            </div>

            <div class="login">
                <form method="POST">
                    <div>
                        <h1>Добро пожаловать, <?php echo $_SESSION['user_username']; ?>!</h1>
                    </div>
                    <div>
                        <button type="submit">Выйти из аккаунта</button>
                    </div>
                </form>
            </div>

            <div class = "copyright">
                <hr>
                Все права защищены © 2026
            </div>
            
        </div>
        
        <script src="script.js"></script>
    </body>
</html>