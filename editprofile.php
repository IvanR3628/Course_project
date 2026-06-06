<?php

    session_start();

    require_once 'api/UserController.php';
    require_once 'api/Poetry.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user = findUserId($_SESSION['user_id']);

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
        }
        if ($error === "false" && $newpassword !== $newpassword2){
            $error = "Новые пароли не совпадают";
        }
        if ($error === "false" && (strlen($newusername) < 2 || strlen($newusername) > 100)) {
            $error = "Псевдоним должен быть от 2 до 100 символов";
        }
        if ($error === "false" && !validEmail($newemail)) {
            $error = "Невалидный email";
        }
        if ($error === "false" && findUserEmail($newemail) != null && $newemail != $user['email']){
            $error = "Email уже используется";
        }
        if ($error === "false" && $newpassword !== null && strlen($newpassword) < 6) {
            $error = "Пароль должен быть от 6 символов";
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
            
            updateUserId($_SESSION['user_id'], $input);
            $_SESSION['user_username'] = $newusername;
            writeLog('Информация успешно изменена', $newusername, $_SESSION['user_id']);
            header('Location: account.php');
            exit;
            
        } else {
            writeLog($error, $newemail);
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
                writePLog('Удалено стихотворение', findPoemId($poemId)['title'], $poemId);
                deletePoetryID($poemId);
            }
            
            deleteUserID($_SESSION['user_id']);
            writeLog('Удалён пользователь',  $user['email'], $user['id']);
            
            $_SESSION = array();
            session_destroy();
            header('Location: login.php');
            exit;
            
            
        } else {
            writeLog('Неверный текущий пароль', $user['email']);
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
                        <input type="number" id="age" name="age" min="1" max="200" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>">
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