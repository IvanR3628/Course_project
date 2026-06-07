<?php

    session_start();
    require_once 'api/Controller.php';

    if (filesize('data/poetry.json') == 0) {
        createPoetryFile();
    }

    $selectedPoemId = isset($_GET['poem_id']) ? (int)$_GET['poem_id'] : null;
    $filterAuthor = isset($_GET['author']) ? $_GET['author'] : null;
    $filterPublisher = isset($_GET['publisher']) ? $_GET['publisher'] : null;

    $poems = getPoems();

    $users = getUsers();
    $usersById = [];
    foreach ($users as $user) {
        $usersById[$user['id']] = $user['username'];
    }

    $userAge = null;
    $canWrite = false;
    $isAdmin = false;

    if (isset($_SESSION['user_id'])) {
        $user = findUserById($_SESSION['user_id']);
        if ($user) {
            if ((time() - strtotime($user['registrationdate'])) / 3600 >= 23) {
                $canWrite = true;
            }
            $userAge = (int)$user['age'];
            $isAdmin = $user['admin'];
        } else {
            writeLog(('code=401 | Пользователь не обнаружен. Аварийный выход из системы | userid=' . $_SESSION['user_id']));
            $_SESSION = array();
            session_destroy();
            header('Location: read.php');
            exit;
        }
    }

    if ($userAge == null || $userAge < 18) {
        $poems = array_filter($poems, function($poem) {
            return $poem['unsafeage'] != 'y';
        });
        $poems = array_values($poems);
    }

    usort($poems, function($a, $b) {
        return strtotime($b['changedate']) - strtotime($a['changedate']);
    });

    $authors = [];
    $publishers = [];
    foreach ($poems as &$poem) {
        if (!empty($poem['author'])) {
            $authors[] = $poem['author'];
        } else if ($poem['anonymity'] == 'y'){
            $authors[] = 'Аноним';
        } else {
            $user = findUserById($poem['authorid']);
            if ($user) {
                $authors[] = $user['username'];
            } else {
                $authors[] = 'Удалённый автор';
            }
        }
        if ($poem['anonymity'] == 'n') {
            $user = findUserById($poem['authorid']);
            if ($user) {
                $publishers[] = $user['username'];
            } else {
                $publishers[] = 'Удалённый публикатор';
            }
        } else {
            $publishers[] = 'Аноним';
        }
    }
    $authors = array_unique($authors);
    $publishers = array_unique($publishers);

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        
        $poemId = (int)$_POST['poem_id'];
        $poem = findPoemById($poemId);
        
        if ($poem) {
            if ($isAdmin === 'y' || $poem['authorid'] == $_SESSION['user_id']){
                writeLog(('code=200 | Стихотворение удалено | userid=' . $_SESSION['user_id'] . ' | poemid=' . $poemId));
                header('Location: read.php');
            } else {
                writeLog(('code=403 | У пользователя нет прав удалить это стихотворение | userid=' . $_SESSION['user_id'] . ' | poemid=' . $poemId));
                header('Location: read.php');
            }
        } else {
            writeLog(('code=404 | Стихотворение не найдено | userid=' . $_SESSION['user_id'] . ' | poemid=' . $poemId));
            header('Location: read.php');
        }
    }

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Читать</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/readstyle.css">
    </head>
    <body>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($canWrite): ?>
                    <button onclick="location.href='write.php'" class="writebutton">
                        Начать творить
                    </button>
                <?php else: ?>
                    <button onclick="alert('Новые пользователи могут начать творить только через сутки после регистрации.')" class="writebutton">
                        Начать творить
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <button onclick="alert('Пожалуйста, авторизуйтесь, чтобы начать творить.')" class="writebutton">
                    Начать творить
                </button>
        <?php endif; ?>
        <div class="page">
            
            <div class="headline">
                <a href="index.php">Главная</a> <a href="read.php">Читать</a> <a href="login.php">Аккаунт</a>
                <hr>
            </div>

            <div class="poetrycontainer">

                <div id="filtersContent">
                    <h2>Фильтры</h2>

                    <div>
                        <label for="searchTitle">Поиск по названию:</label>
                        <input type="text" id="searchTitle" placeholder="Введите название...">
                    </div>

                    <div>
                        <label for="searchAuthor">Автор:</label>
                        <input type="text" id="searchAuthor" placeholder="Введите автора...">
                        <select id="authorSelect">
                            <option value="">Выберите автора</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?php echo htmlspecialchars($author); ?>"><?php echo htmlspecialchars($author); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="searchPublisher">Кто опубликовал:</label>
                        <input type="text" id="searchPublisher" placeholder="Введите имя...">
                        <select id="publisherSelect">
                            <option value="">Выберите публикатора</option>
                            <?php foreach ($publishers as $publisher): ?>
                                <option value="<?php echo htmlspecialchars($publisher); ?>"><?php echo htmlspecialchars($publisher); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="searchDescription">Поиск по описанию:</label>
                        <input type="text" id="searchDescription" placeholder="Введите ключевые слова...">
                    </div>

                    <div>
                        <label>Сортировка:</label>
                        <select id="sortBy">
                            <option value="newest">Сначала новые</option>
                            <option value="oldest">Сначала старые</option>
                        </select>
                    </div>

                    <button id="resetFiltersButton">Сбросить фильтры</button>
                </div>

                <div class="poemslist">
                    <h2>Все стихотворения</h2>
                    <div id="poemsListContainer" class="poemscontainer">
                        Excuse me sir
                    </div>
                </div>

                <div class="poemview">
                    <div id="poemViewContent">
                        <p>Выберите стихотворение из списка слева</p>
                    </div>
                </div>
                
            </div>

            <div class = "copyright">
                <hr>
                Все права защищены © 2026
            </div>
            
        </div>
        
        <script>
            const allPoems = <?php echo json_encode($poems); ?>;
            const allUsers = <?php echo json_encode($usersById); ?>;
            const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
            const isAdmin = <?php echo json_encode($isAdmin); ?>;
        </script>
        <script src="js/readscript.js"></script>
    </body>
</html>