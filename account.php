<?php

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $username = $_SESSION['user_username'];
    $log = 'data/auth.log';

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $time = date('Y-m-d H:i:s');
        $line = $time . " || user=" . $username . " | action=LOGOUT\n";
        file_put_contents($log, $line, FILE_APPEND);
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
        <div class="page">
            
            <div class="headline">
                <a href="index.php">Главная</a> <a href="authors.php">Авторы</a> <a href="poetry.php">Произведения</a> <a href="login.php">Аккаунт</a>
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