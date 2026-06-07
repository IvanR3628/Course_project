<?php

    session_start();
    require_once 'api/Controller.php';

    $userAge = null;
    $canWrite = false;

    if (filesize('data/poetry.json') == 0) {
        createPoetryFile();
    }

    if (isset($_SESSION['user_id'])) {
        $user = findUserById($_SESSION['user_id']);
        if ($user) {
            if ((time() - strtotime($user['registrationdate'])) / 3600 >= 23) {
                $canWrite = true;
            }
            $userAge = (int)$user['age'];
        } else {
            $_SESSION = array();
            session_destroy();
            
            header('Location: index.php');
            exit;
        }
    }

    $allPoems = getPoems();

    if ($userAge == null || $userAge < 18) {
        $allPoems = array_filter($allPoems, function($poem) {
            return $poem['unsafeage'] != 'y';
        });
        $allPoems = array_values($allPoems);
    }

    usort($allPoems, function($a, $b) {
        return strtotime($b['changedate']) - strtotime($a['changedate']);
    });

    $latestPoems = array_slice($allPoems, 0, 5);

    $authorsList = [];

    foreach ($allPoems as $poem) {
        if (!empty($poem['author'])) {
            $authorsList[] = $poem['author'];
        } else if ($poem['anonymity'] != 'y') {
            $user = findUserById($poem['authorid']);
            if ($user) {
                $authorsList[] = $user['username'];
            }
        }
    }

    $authorsList = array_unique($authorsList);
    shuffle($authorsList);
    $randomAuthors = array_slice($authorsList, 0, 5);

?>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Стихотвория – Главная</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
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

            <div class = "center">
                <h1>Стихотвория</h1>
                <h2>Последние произведения</h2>
                <div class="itemscontainer">
                    <?php foreach ($latestPoems as $poem): ?>
                        <div class="itemcard" data-type="poem" data-id="<?php echo $poem['id']; ?>">
                            <div><?php echo htmlspecialchars($poem['title']) . ($poem['unsafeage'] === 'y' ? ' (18+)' : ''); ?></div>
                            <div>

                                <?php
                                    if (!empty($poem['author'])) {
                                        $authorName = $poem['author'];
                                    } else if ($poem['anonymity'] == 'y') {
                                        $authorName = 'Аноним';
                                    } else {
                                        $user = findUserById($poem['authorid']);
                                        if ($user) {
                                            $authorName = $user['username'];
                                        } else {
                                            $authorName = 'Удалённый автор';
                                        }
                                    }

                                    if ($poem['anonymity'] == 'n') {
                                        $user = findUserById($poem['authorid']);
                                        $publisherName = $user ? $user['username'] : 'Удалённый публикатор';
                                    } else {
                                        $publisherName = 'Аноним';
                                    }
                                ?>

                                <span><?php echo htmlspecialchars($authorName); ?></span>
                                <span>| <?php echo htmlspecialchars($publisherName); ?></span>
                                <span>| <?php echo date('d.m.Y', strtotime($poem['changedate'])); ?></span>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2>Случайные авторы</h2>
                <div class="itemscontainer">
                    <?php foreach ($randomAuthors as $author): ?>
                        <div class="itemcard" data-type="author" data-name="<?php echo htmlspecialchars($author); ?>">
                            <div><?php echo htmlspecialchars($author); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            </div>

            <div class = "copyright">
                <hr>
                Все права защищены © 2026
            </div>
            
        </div>
        
        
        <script>
            document.querySelectorAll('.itemcard').forEach(card => {
                card.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    
                    if (type === 'poem') {
                        window.location.href = `read.php?poem_id=${id}`;
                    } else if (type === 'author') {
                        window.location.href = `read.php?author=${encodeURIComponent(name)}`;
                    }
                });
            });
        </script>
    </body>
</html>