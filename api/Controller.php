<?php

    require_once 'User.php';
    require_once 'Poetry.php';

    function writeLog($message){
        $log = dirname(__DIR__) . '\data\auth.log';
        $time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $line = $time . " | " . $message . "\n";
        file_put_contents($log, $line, FILE_APPEND);
    }

    function sendJsonResponse($code, $status, $message, $data = null){
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null){
            if (is_array($data)) {
                unset($data['password_hash']);
            }
            $response['data'] = $data;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        $message = "API request | code=" . $code . ' | ' . $message;
        writeLog($message);
    }

    function validDate($date){
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }

    function getAllUsers(){
        $users = getUsers();
        if (count($users) === 0) {
            sendJsonResponse(404, 'error', 'Нет пользователей');
            exit;
        }
        sendJsonResponse(200, 'success', 'Список пользователей', $users);
    }

    function getOneUser($id){
        $user = findUserById($id);
        if ($user === null) {
            sendJsonResponse(404, 'error', 'Пользователь не найден');
        }
        sendJsonResponse(200, 'success', 'Пользователь найден', $user);
    }

    function createUser(){
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $username = trim($input['username']);
        $email = trim($input['email']);
        
        if (empty($username) || empty($email) || empty($input['password'])) {
            sendJsonResponse(400, 'error', 'Обязательные поля не были заполнены');
            exit;
        }
        if (!validUsername($username)) {
            sendJsonResponse(400, 'error', 'Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)');
            exit;
        }
        if (!validEmail($email)) {
            sendJsonResponse(400, 'error', 'Невалидный email');
            exit;
        }
        if (findUserByEmail($email)) {
            sendJsonResponse(400, 'error', 'Email уже используется');
            exit;
        }
        if (strlen($input['password']) < 6) {
            sendJsonResponse(400, 'error', 'Пароль должен содержать минимум 6 символов');
            exit;
        }
        
        $age = null;
        if (isset($input['age'])) {
            $age = $input['age'];
            if ($age < 1 || $age > 150){
                sendJsonResponse(400, 'error', 'Возраст должен лежать в диапазоне от 1 до 150 лет');
                exit;
            }
        }
        
        $admin = isset($input['admin']) ? $input['admin'] : "n";
        if ($admin !== "n" && $admin !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании прав администрирования (y/n)');
            exit;
        }
        
        $result = createNewUser($username, $email, $input['password'], $age, $admin);
        sendJsonResponse(200, 'success', 'Регистрация произошла успешно', $result['user']);
    }

    function updateUser($id){
        
        if ($id === 0) {
            sendJsonResponse(400, 'error', 'Id не указан');
        } 
        $input = json_decode(file_get_contents('php://input'), true);
        
        $newusername = trim($input['username']);
        $newemail = trim($input['email']);
        $age = isset($input['age']) ? $input['age'] : null;
        $admin = isset($input['admin']) ? $input['admin'] : "n";
        
        if (empty($newusername) && empty($newemail) && empty($input['password']) && empty($input['registrationdate']) && empty($input['age']) && empty($input['admin'])) {
            sendJsonResponse(400, 'error', 'Ни одно нужное поле не было заполнено');
            exit;
        }
        
        if (!empty($newusername) && !validUsername($newusername)) {
            sendJsonResponse(400, 'error', 'Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)');
            exit;
        }
        if (!empty($newemail) && !validEmail($newemail)) {
            sendJsonResponse(400, 'error', 'Невалидный email');
            exit;
        }
        if (!empty($newemail) && findUserByEmail($newemail)) {
            sendJsonResponse(400, 'error', 'Email уже используется');
            exit;
        }
        if (!empty($input['password']) && strlen($input['password']) < 6) {
            sendJsonResponse(400, 'error', 'Пароль должен содержать минимум 6 символов');
            exit;
        }
        if (!empty($input['registrationdate']) && !validDate($input['registrationdate'])){
            sendJsonResponse(400, 'error', 'Невалидная дата');
            exit;
        }
        if ($age !== null && (!filter_var($age, FILTER_VALIDATE_INT) || $age < 1 || $age > 150)){
            sendJsonResponse(400, 'error', 'Возраст должен лежать в диапазоне от 1 до 150 лет');
            exit;
        }
        if ($admin !== "n" && $admin !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании прав администрирования (y/n)');
            exit;
        }
        
        if (findUserById($id)){
            $result = updateUserById($id, $input);
            sendJsonResponse(200, 'success', 'Информация о пользователе обновлена', $result['user']);
        } else {
            sendJsonResponse(404, 'error', 'Пользователя с таким id не существует');
        }
    }

    function deleteUser($id){
        if ($id === 0) {
            sendJsonResponse(400, 'error', 'ID не указан');
        }
        
        if (findUserById($id)){
            $result = deleteUserById($id);
            sendJsonResponse(200, 'success', 'Пользователь удалён', $result['user']);
        } else {
            sendJsonResponse(404, 'error', 'Пользователя не существует');
        }
    }

    function getAllPoems(){
        $poems = getPoems();
        if (count($poems) === 0) {
            sendJsonResponse(404, 'error', 'Нет стихотворений');
            exit;
        }
        sendJsonResponse(200, 'success', 'Список стихотворений', $poems);
    }

    function getOnePoem($id){
        $poem = findPoemById($id);
        if ($poem === null) {
            sendJsonResponse(404, 'error', 'Стихотворение не найдено');
        }
        sendJsonResponse(200, 'success', 'Стихотворение найдено', $poem);
    }

    function createPoem(){
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $title = trim($input['title']);
        $content = trim($input['content']);
        
        if (empty($title) || empty($content) || empty($input['authorid'])) {
            sendJsonResponse(400, 'error', 'Обязательные поля не были заполнены');
            exit;
        }
        if (!findUserById($input['authorid'])){
            sendJsonResponse(404, 'error', 'Автора не существует');
            exit;
        }
        
        $description = isset($input['description']) ? $input['description'] : "";
            
        $anonymity = isset($input['anonymity']) ? $input['anonymity'] : "n";
        if ($anonymity !== "n" && $anonymity !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании анонимности (y/n)');
            exit;
        }
        
        $author = isset($input['author']) ? $input['author'] : "";
        
        $unsafeage = isset($input['unsafeage']) ? $input['unsafeage'] : "n";
        if ($unsafeage !== "n" && $unsafeage !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании ограничения по возрасту (y/n)');
            exit;
        }
        
        $result = createNewPoem($input['title'], $input['content'], $input['authorid'], $description, $anonymity, $author, $unsafeage);
        sendJsonResponse(200, 'success', 'Создано новое стихотворение', $result['poem']);
    }

    function updatePoem($id){
        if ($id === 0) {
            sendJsonResponse(400, 'error', 'Id не указан');
        } 
        $input = json_decode(file_get_contents('php://input'), true);
        
        $newtitle = trim($input['title']);
        $newcontent = trim($input['content']);
        
        if (empty($newtitle) && empty($newcontent) && empty($input['description']) && empty($input['authorid']) && empty($input['anonymity']) && empty($input['author']) && empty($input['unsafeage']) && empty($input['changedate'])) {
            sendJsonResponse(400, 'error', 'Ни одно нужное поле не было заполнено');
            exit;
        }
        if (!findUserById($input['authorid'])){
            sendJsonResponse(404, 'error', 'Автора не существует');
            exit;
        }
        
        $anonymity = isset($input['anonymity']) ? $input['anonymity'] : "n";
        if ($anonymity !== "n" && $anonymity !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании анонимности (y/n)');
            exit;
        }
        
        $unsafeage = isset($input['unsafeage']) ? $input['unsafeage'] : "n";
        if ($unsafeage !== "n" && $unsafeage !== "y"){
            sendJsonResponse(400, 'error', 'Ошибка в указании ограничения по возрасту (y/n)');
            exit;
        }
        
        if (findPoemById($id)){
            $result = updatePoemById($id, $input);
            sendJsonResponse(200, 'success', 'Информация о стихотворении обновлена', $result['poem']);
        } else {
            sendJsonResponse(404, 'error', 'Стихотворения с таким id не существует');
        }
        
    }

    function deletePoem($id){
        if ($id === 0) {
            sendJsonResponse(400, 'error', 'ID не указан');
        }
        
        if (findPoemById($id)){
            $result = deletePoemById($id);
            sendJsonResponse(200, 'success', 'Стихотворение удалено', $result['poem']);
        } else {
            sendJsonResponse(404, 'error', 'Стихотворения не существует');
        }
    }

?>