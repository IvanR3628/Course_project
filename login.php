<?php

    session_start();
    require_once 'api/Controller.php';

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

    $email = "";
    $password = "";
    $users = getUsers();

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        foreach ($users as $user){
            if ($user['email'] == $email){
                $userexist = "true";
                if (password_verify($password, $user['password_hash'])){
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_username'] = $user['username'];
                    writeLog(('code=200 | Пользователь вошёл в систему | userid=' . $user['id']));
                    header('Location: account.php');
                    exit;
                } else {
                    $error = $user['id'];
                    header('Location: login.php');
                    exit;
                }
            }
        }
        if (!isset($userexist)){
            writeLog(('code=404 | Пользователя с таким email не существует | email=' . $email));
            echo "<script>alert('Пользователя с таким email не существует'); window.location.href='login.php';</script>";
        }
        if (isset($error)){
            writeLog(('code=401 | Неверный пароль | userid=' . $error));
            echo "<script>alert('Неверный пароль'); window.location.href='login.php';</script>";
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
            </div>
            <hr>
            
            <div class="login">
                <form class="smallform" method="POST">
                    <div>
                        <label class = "smalllabel" for="email">Почта:</label>
                        <input class = "smallform" type="text" name="email" required>
                    </div>
                    <div>
                        <label class = "smalllabel" for="password">Пароль:</label>
                        <input class = "smallform" type="password" name="password" required>
                    </div>
                    <div class="formbuttons">
                        <button type="submit">Войти</button>
                        <a href="register.php">Зарегистрироваться</a>
                    </div>
                </form>
            </div>
        
            <hr>
            <div class = "copyright">
                Все права защищены © 2026
            </div>
        </div>
    </body>
</html>