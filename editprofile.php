<?php

    session_start();
    require_once 'api/Controller.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    } else {
        $user = findUserById($_SESSION['user_id']);
        if (!$user){
            $_SESSION = array();
            session_destroy();
            header('Location: login.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        
        $newusername = trim($_POST['username']);
        $newemail = trim($_POST['email']);
        $newage = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $currentpassword = $_POST['password'];
        $newpassword = !empty($_POST['newpassword']) ? $_POST['newpassword'] : null;
        $newpassword2 = !empty($_POST['newpassword2']) ? $_POST['newpassword2'] : null;
        
        $error = "false";
        
        if (!password_verify($currentpassword, $user['password_hash'])) {
            $error = 'Неверный текущий пароль';
        } else if ($newpassword !== $newpassword2){
            $error = "Новые пароли не совпадают";
        } else if (!validUsername($newusername)){
            $error = "Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)";
        } else if (!validEmail($newemail)) {
            $error = "Невалидный email";
        } else if (findUserByEmail($newemail) && $newemail != $user['email']){
            $error = "Email уже используется";
        } else if ($newpassword !== null && strlen($newpassword) < 6) {
            $error = "Пароль должен содержать минимум 6 символов";
        }
        
        if ($error === "false"){
            
            $input = [
                'username' => $newusername,
                'email' => $newemail,
                'age' => $newage
            ];
            
            if ($newpassword !== null) {
                $input['password'] = $newpassword;
            }
            
            updateUserById($user['id'], $input);
            $_SESSION['user_username'] = $newusername;
            header('Location: account.php');
            exit;
            
        } else {
            
        }
        
    }
        
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        $currentpassword = $_POST['password'];
        if (password_verify($currentpassword, $user['password_hash'])){
            
            $allPoetry = getPoetry();
            $poemsToDelete = [];
            
            foreach ($allPoetry as $poetry) {
                if ($poetry['authorid'] === $_SESSION['user_id']) {
                    $poemsToDelete[] = $poetry['id'];
                }
            }
            
            foreach ($poemsToDelete as $poemId) {
                
                deletePoemById($poemId);
            }
            
            $userId = $_SESSION['user_id'];
            deleteUserById($userId);
            
            $_SESSION = array();
            session_destroy();
            
            echo    "<script>  localStorage.removeItem('poem_drafts_list_{$userId}');
                                window.location.href = 'login.php';
                    </script>";
            exit;
            
        } else {
            
        }
        
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Редактировать</title>
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
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div>
                        <label for="age">Возраст:</label>
                        <input type="number" id="age" name="age" min="1" max="150" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="email">Почта:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div>
                        <label for="password">Текущий пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <label for="password">Новый пароль:</label>
                        <input type="password" id="newpassword" name="newpassword">
                    </div>
                    <div>
                        <label for="password">Повторите пароль:</label>
                        <input type="password" id="newpassword2" name="newpassword2">
                    </div>
                    <div>
                        <button type="submit" name="update">Обновить</button>
                        <button type="button" onclick="location.href='account.php'">Отмена</button>
                        <button type="submit" name="delete"
                                onclick="return confirm('ВНИМАНИЕ! Вы уверены, что хотите удалить аккаунт? Это действие необратимо.')">Удалить аккаунт</button>
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