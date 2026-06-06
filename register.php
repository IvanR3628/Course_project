<?php

    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

    require_once 'api/UserController.php';

    $u = json_decode(file_get_contents('data/users.json'), true);
    if (!$u){
        createFile();
        $u = json_decode(file_get_contents('data/users.json'), true);
    }
    $users = $u['users'];
    $age = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password2 = $_POST['password2'];
        if (isset($_POST['age'])){
            $age = $_POST['age'];
        }
        $error = "false";
        if ($password !== $password2){
            $error = "Пароли не совпадают";
        }
        if ($error === "false" && (strlen($username) < 2 || strlen($username) > 100)) {
            $error = "Псевдоним должен быть от 2 до 100 символов";
        }
        if ($error === "false" && !validEmail($email)) {
            $error = "Невалидный email";
        }
        if ($error === "false" && strlen($password) < 6) {
            $error = "Пароль должен быть от 6 символов";
        }
        
        if ($error === "false"){
            $result = createNewUser($username, $email, $password, $age);
            if (isset($result['error'])) {
                writeLog('Указанный email (' . $email . ') занят');
            } else {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_username'] = $username;
                writeLog('Регистрация произошла успешно', $username, $result['user']['id']);
                header('Location: account.php');
            }
        } else {
            writeLog($error, $email);
        }

    }
?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Регистрация</title>
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
                        <label for="username">Псевдоним:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div>
                        <label for="age">Возраст:</label>
                        <input type="number" id="age" name="age" min="1" max="200">
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
                        <label for="password">Повторите пароль:</label>
                        <input type="password" id="password2" name="password2" required>
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