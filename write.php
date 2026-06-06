<?php

    session_start();

    require_once 'api/Poetry.php';

    if (filesize('data/poetry.json') == 0) {
        createPFile();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $title = trim($_POST['title']); 
        $content = trim($_POST['content']);
        $description = trim($_POST['description']);
        $authorid = $_SESSION['user_id'];
        if (isset($_POST['author']) && $_POST['author'] != ""){
            $author = trim($_POST['author']);
        } else {
            $author = "";
        }
        if ($_POST['publish_as'] === "user") {
            $anonymity = "n";
        } else {
            $anonymity = "y";
        }
        if ($_POST['adult'] === "1"){
            $age = "y";
        } else {
            $age = "n";
        }
        if ($_POST['action'] === "publish"){
            $result = createNewPoetry($title, $content, $description, $authorid, $anonymity, $author, $age);
            writeLog('Создано новое стихотворение', $result['poetry']['title'], $result['poetry']['id']);
            header('Location: write.php');
            exit;
        }
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Творить</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/writestyle.css">
    </head>
    <body>
        <div class="page">
            
            <div class="headline">
                <a href="index.php">Главная</a> <a href="read.php">Читать</a> <a href="login.php">Аккаунт</a>
                <hr>
            </div>

            <div class="writecenter">
                <h1>Создать новое стихотворение</h1>
                    
                    <div class="container">
                    
                        <div class="drafts">
                            <h3 class="hcenter">Черновики</h3>
                            <div class="draftslist" id="draftsList">
                                <p class="nodrafts">Нет сохранённых черновиков</p>
                            </div>
                            <div class="draftsbuttons">
                                <button id="loadDraftButton" class="draftbuttonload">Загрузить черновик</button>
                                <button id="deleteDraftButton" class="draftbuttondelete">Удалить</button>
                                <button id="clearAllDraftsButton" class="draftbuttonclear">Очистить всё</button>
                            </div>
                        </div>
                        
                
                        <form method="POST" class="write">
                            <div>
                                <label for="title">Название стихотворения:</label>
                                <input type="text" id="title" name="title" required placeholder="Введите название...">
                            </div>

                            <div>
                                <label for="content">Текст стихотворения:</label>
                                <textarea id="content" name="content" rows="20" required placeholder="Введите текст..."></textarea>
                            </div>

                            <div>
                                <label for="description">Описание (необязательно):</label>
                                <textarea id="description" name="description" rows="5" placeholder="Введите описание..."></textarea>
                            </div>

                            <div>
                                <label for="author">Автор стихотворения:</label>
                                <input type="text" id="author" name="author" placeholder="Оставьте пустым, если Вы - автор">
                            </div>

                            <div>
                                <label>Как опубликовать:</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="publish_as" value="user" checked>
                                        От моего имени (<?php echo $_SESSION['user_username'] ?>)
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="publish_as" value="anonymous">
                                        Анонимно
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label>Возрастное ограничение:</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="adult" value="0" checked>
                                        Для всех возрастов
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="adult" value="1">
                                        18+ (содержит взрослый контент)
                                    </label>
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button type="submit" name="action" value="publish">Опубликовать</button>
                                <button type="submit" name="action" value="draft" id="saveDraft">Сохранить черновик</button>
                                <button onclick="location.href='write.php'">Очистить</button>
                            </div>
                        </form>
                        
                </div>
                    
            </div>

            <div class="copyright">
                <hr>
                Все права защищены © 2026
            </div>
            
        </div>
        
        <script>
            const USER_ID = '<?php echo $_SESSION['user_id'] ?? 'guest'; ?>';
            const STORAGE_KEY = `poem_drafts_list_${USER_ID}`;
        </script>
        <script src="js/writescript.js"></script>
    </body>
</html>