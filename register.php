<?php

    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

    require_once 'api/UserController.php';

    $login = "";
    $password = "";
    $users = json_decode(file_get_contents('data/users.json'), true);
    $log = 'data/auth.log';

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $input = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];
        $time = date('Y-m-d H:i:s');
        if (!isset($users[$login])){
            createUser($input);
            $_SESSION['user_id'] = $users[$login]['id'];
            $_SESSION['user_login'] = $login;
            $line = $time . " || user=" . $login . " | action=SUCCESS_REGISTRATION\n";
            file_put_contents($log, $line, FILE_APPEND);
            header('Location: account.php');
        } else {
            $line = $time . " || user=" . $login . " | action=USER_ALREADY_EXISTS\n";
            file_put_contents($log, $line, FILE_APPEND);
        }
        
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Регистрация</title>
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
                        <label for="username">Имя:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div>
                        <label for="email">Почта:</label>
                        <input type="text" id="email" name="email" required>
                    </div>
                    <div>
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <button type="submit">Зарегистрироваться</button>
                    </div>
                </form>
            </div>
        
            <div class = "copyright">
                <hr>
                Все права защищены © 2026
            </div>
        </div>
    </body>
</html>