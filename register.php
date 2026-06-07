<?php

    session_start();
    require_once 'api/Controller.php';

    if (isset($_SESSION['user_id'])) {
        header('Location: account.php');
        exit;
    }

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
        } else if (!validUsername($username)) {
            $error = "Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)";
        } else if (!validEmail($email)) {
            $error = "Невалидный email";
        } else if (findUserByEmail($email)) {
            $error = "Email уже используется";
        } else if (strlen($password) < 6) {
            $error = "Пароль должен содержать минимум 6 символов";
        }
        
        if ($error === "false"){
            $result = createNewUser($username, $email, $password, $age);
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_username'] = $username;
            writeLog(('code=200 | Регистрация произошла успешно | userid=' . $result['user']['id']));
            header('Location: account.php');
        } else {
            writeLog(('code=400 | ' . $error));
            echo "<script>alert('" . addslashes($error) . "'); window.location.href='register.php';</script>";
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
            </div>
            <hr>
            
            <div class="login">
                <form class="bigform" method="POST">
                    <div>
                        <label class = "biglabel" for="username">Псевдоним:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div>
                        <label class = "biglabel" for="age">Возраст:</label>
                        <input type="number" name="age" min="1" max="150">
                    </div>
                    <div>
                        <label class = "biglabel" for="email">Почта:</label>
                        <input type="text" name="email" required>
                    </div>
                    <div>
                        <label class = "biglabel" for="password">Пароль:</label>
                        <input type="password" name="password" required>
                    </div>
                    <div>
                        <label class = "biglabel" for="password">Повторите пароль:</label>
                        <input type="password" name="password2" required>
                    </div>
                    <div class="formbuttons">
                        <a href="login.php">Войти</a>
                        <button type="submit">Зарегистрироваться</button>
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