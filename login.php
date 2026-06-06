<?php

    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

    require_once 'api/UserController.php';

    $email = "";
    $password = "";
    $u = json_decode(file_get_contents('data/users.json'), true);
    if (!$u){
        createFile();
        $u = json_decode(file_get_contents('data/users.json'), true);
    }
    $users = $u['users'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        foreach ($users as $user){
            if ($user['email'] == $email){
                $userexist = "true";
                if (password_verify($password, $user['password_hash'])){
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_username'] = $user['username'];
                    writeLog("Пользователь успешно авторизовался", $user['username'], $user['id']);
                    header('Location: account.php');
                    exit;
                } else {
                    $error = "Неверный пароль";
                    exit;
                }
            }
        }
        if (!isset($userexist)){
            writelog("Пользователя не существует", $email);
        }
        if (isset($error)){
            writelog($error, $email);
        }
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Авторизация</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/loginstyle.css">
    </head>
    <body>
        <div class="page">
        
            <div class="headline">
                <a href="index.php">Главная</a> <a href="read.php">Читать</a> <a href="login.php">Аккаунт</a>
                <hr>
            </div>
            
            <div class="login">
                <form method="POST">
                    <div>
                        <label for="email">Почта:</label>
                        <input type="text" id="email" name="email" required>
                    </div>
                    <div>
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <button type="submit">Войти</button>
                        <a href="register.php">Зарегистрироваться</a>
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