<?php

    session_start();
    require_once 'api/Controller.php';

    $userAge = null;
    $canWrite = false;
    $canRedact = false;

    if (filesize('data/poetry.json') == 0) {
        createPoetryFile();
    }

    $poemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (isset($_SESSION['user_id'])) {
        $user = findUserById($_SESSION['user_id']);
        if ($user) {
            if ($user['admin'] == "y" || findPoemById($poemId)['authorid'] == $_SESSION['user_id']){
                $canRedact = true;
            }
            if ((time() - strtotime($user['registrationdate'])) / 3600 >= 23) {
                $canWrite = true;
            }
            if (isset($user['age'])) {
                $userAge = (int)$user['age'];
            }
        } else {
            writeLog(('code=401 | Пользователь не обнаружен. Аварийный выход из системы | userid=' . $_SESSION['user_id']));
            $_SESSION = array();
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }


    $poem = findPoemById($poemId);

    if (!$poem) {
        writeLog(('code=404 | Стихотворение не найдено | userid=' . $user['id'] . ' | poemid=' . $poemId));
        header('Location: read.php');
        exit;
    }

    if (!$canWrite){
        writeLog(('code=403 | У пользователя нет прав писать стихотворения | userid=' . $_SESSION['user_id']));
        header('Location: index.php');
        exit;
    }

    if (!$canRedact){
        writeLog(('code=403 | У пользователя нет прав редактировать это стихотворение | userid=' . $_SESSION['user_id']));
        header('Location: read.php');
        exit;
    }

    $isAdult = ($userAge !== null && $userAge >= 18);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "update"){
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        if ($title == "" || $content == ""){
            writeLog(('code=400 | Обязательные поля не были заполнены | userid=' . $_SESSION['user_id']));
            echo "<script>alert('Обязательные поля не были заполнены'); window.location.href='edit.php?id=$poemId';</script>";
            exit;
        }
        $description = trim($_POST['description']);
        
        if (isset($_POST['author']) && trim($_POST['author']) != ""){
            $author = trim($_POST['author']);
        } else {
            $author = "";
        }
        
        $input = [
            'title' => $title,
            'content' => $content,
            'description' => $description,
            'author' => $author,
            'anonymity' => $_POST['publish_as'] === "user" ? "n" : "y",
            'unsafeage' => $_POST['unsafeage'] === "1" ? "y" : "n",
            'changedate' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ];
            
        $result = updatePoemById($poemId, $input);
        
        writeLog(('code=200 | Информация о стихотворении обновлена | userid=' . $user['id'] . ' | poemid=' . $result['poem']['id']));
        
        echo    "<script>
                    alert('Стихотворение успешно изменено!');
                    window.location.href = 'read.php?poem_id=$poemId';
                </script>";
        
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Изменить</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/writestyle.css">
    </head>
    <body>
        <div class="page">
            
            <div class="headline">
                <a href="index.php">Главная</a> <a href="read.php">Читать</a> <a href="login.php">Аккаунт</a>
            </div>
            <hr>

            <div class="writecenter">
                <h1>Редактировать стихотворение</h1>
                    
                    <div class="container">
                    
                        <div class="drafts">
                            <h3 class="hcenter">Черновики</h3>
                            <div class="draftslist" id="draftsList">
                                <p class="nodrafts">Нет сохранённых черновиков</p>
                            </div>
                            <div class="draftsbuttons">
                                <button id="loadDraftButton">Загрузить черновик</button>
                                <button id="deleteDraftButton">Удалить черновик</button>
                                <button id="clearAllDraftsButton">Очистить всё</button>
                            </div>
                        </div>
                        
                
                        <form method="POST" class="write">
                            <div>
                                <label for="title">Название стихотворения:</label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($poem['title']);?>" required placeholder="Введите название...">
                            </div>

                            <div>
                                <label for="content">Текст стихотворения:</label>
                                <textarea id="content" name="content" rows="20" required placeholder="Введите текст..."><?php echo htmlspecialchars($poem['content']);?></textarea>
                            </div>

                            <div>
                                <label for="description">Описание (необязательно):</label>
                                <textarea id="description" name="description" rows="5" placeholder="Введите описание..."><?php echo htmlspecialchars($poem['description']);?></textarea>
                            </div>

                            <div>
                                <label for="author">Автор стихотворения:</label>
                                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($poem['author']);?>" placeholder="Оставьте пустым, если Вы - автор">
                            </div>

                            <div>
                                <label>Как опубликовать:</label>
                                <div>
                                    <label>
                                        <input type="radio" name="publish_as" value="user" <?php echo $poem['anonymity'] == 'n' ? 'checked' : ''; ?> >
                                        От моего имени (<?php echo $_SESSION['user_username'] ?>)
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="publish_as" value="anonymous" <?php echo $poem['anonymity'] == 'y' ? 'checked' : ''; ?> >
                                        Анонимно
                                    </label>
                                </div>
                            </div>
                            
                            
                            <?php if ($isAdult): ?>
                            <div>
                                <label>Возрастное ограничение:</label>
                                <div>
                                    <label>
                                        <input type="radio" name="unsafeage" value="0" <?php echo $poem['unsafeage'] != 'y' ? 'checked' : ''; ?> >
                                        Для всех возрастов
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="unsafeage" value="1" <?php echo $poem['unsafeage'] == 'y' ? 'checked' : ''; ?> >
                                        18+ (содержит взрослый контент)
                                    </label>
                                </div>
                            </div>
                            <?php else: ?>
                                <input type="hidden" name="unsafeage" value="0">
                            <?php endif; ?>
                            

                            <div class="formbuttons">
                                <button type="submit" name="action" value="update">Сохранить изменения</button>
                                <button type="submit" value="draft" id="saveDraft">Сохранить черновик</button>
                            </div>
                        </form>
                        
                </div>
                    
            </div>

            <hr>
            <div class = "copyright">
                Все права защищены © 2026
            </div>
            
        </div>
        
        <script>
            const USER_ID = '<?php echo $_SESSION['user_id']; ?>';
            const STORAGE_KEY = `poem_drafts_list_${USER_ID}`;
        </script>
        <script src="js/writescript.js"></script>
    </body>
</html>