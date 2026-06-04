<?php

    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

    require_once 'api/UserController.php';

    $reg = "";
    $password = "";
    $u = json_decode(file_get_contents('data/users.json'), true);
    $users = $u['users'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $reg = trim($_POST['reg']);
        $password = $_POST['password'];
        foreach ($users as $user){
            if ($user['username'] == $reg || $user['email'] == $reg){
                $userexist = "true";
                if (password_verify($password, $user['password_hash'])){
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_username'] = $user['username'];
                    writeLog("Пользователь успешно авторизовался", $user['username'], $user['id']);
                    header('Location: account.php');
                    exit;
                } else {
                    $error = "Неверный пароль";
                }
            }
        }
        if (!isset($userexist)){
            writelog("Пользователя не существует", $reg);
        }
        if (isset($error)){
            writelog($error, $reg);
        }
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Авторизация</title>
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
                        <label for="reg">Имя/почта:</label>
                        <input type="text" id="reg" name="reg" required>
                    </div>
                    <div>
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <button type="submit">Войти</button>
                        <a href="register.php" class="register-btn">Зарегистрироваться</a>
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