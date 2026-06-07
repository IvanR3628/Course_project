<?php

    require_once 'User.php';
    require_once 'Poetry.php';

    function writeLog($message){
        $log = dirname(__DIR__) . '\data\auth.log';
        $time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $line = $time . " | " . $message . "\n";
        file_put_contents($log, $line, FILE_APPEND);
    }

    function sendJsonResponse($status, $message, $data = null){
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null){
            if (is_array($data)) {
                unset($data['password_hash']);
            }
            $response['data'] = $data;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        $message = "API request: " . $message;
        writeLog($message);
    }

    function getAllUsers(){
        $users = getUsers();
        if (count($users) === 0) {
            sendJsonResponse('error', 'Нет пользователей');
            exit;
        }
        sendJsonResponse('success', 'Список пользователей', $users);
    }

    function getOneUser($id){
        $user = findUserById($id);
        if ($user === null) {
            sendJsonResponse('error', 'Пользователь не найден');
        }
        sendJsonResponse('success', 'Пользователь найден', $user);
    }

    function createUser(){
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            sendJsonResponse('error', 'Обязательные поля не были заполнены');
            exit;
        }
        if (!validUsername($input['username'])) {
            sendJsonResponse('error', 'Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)');
            exit;
        }
        if (!validEmail($input['email'])) {
            sendJsonResponse('error', 'Невалидный email');
            exit;
        }
        if (findUserByEmail($input['email'])) {
            sendJsonResponse('error', 'Email уже используется');
            exit;
        }
        if (strlen($input['password']) < 6) {
            sendJsonResponse('error', 'Пароль должен содержать минимум 6 символов');
            exit;
        }
        
        $age = null;
        if (isset($input['age'])) {
            $age = $input['age'];
            if ($age < 1 || $age > 150){
                sendJsonResponse('error', 'Возраст должен лежать в диапазоне от 1 до 150 лет');
                exit;
            }
        }
        
        $admin = isset($input['admin']) ? $input['admin'] : "n";
        
        $result = createNewUser($input['username'], $input['email'], $input['password'], $age, $admin);
        sendJsonResponse('success', 'Регистрация произошла успешно', $result['user']);
    }

    function updateUser($id){
        
        if ($id === 0) {
            sendJsonResponse('error', 'Id не указан');
        } 
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['username']) && empty($input['email']) && empty($input['password']) && empty($input['registrationdate']) && empty($input['age']) && empty($input['admin'])) {
            sendJsonResponse('error', 'Ни одно нужное поле не было заполнено');
            exit;
        }
        
        if (!empty($input['username']) && !validUsername($newusername)) {
            sendJsonResponse('error', 'Невалидный псевдоним. Убедитесь, что он содержит от 2 до 100 незапрещённых символов (русские и английские буквы, цифры, дефис и нижнее подчёркивание)');
            exit;
        }
        if (!empty($input['email']) && !validEmail($input['email'])) {
            sendJsonResponse('error', 'Невалидный email');
            exit;
        }
        if (!empty($input['email']) && findUserByEmail($input['email'])){
            sendJsonResponse('error', 'Email уже используется');
            exit;
        }
        if (!empty($input['password']) && strlen($input['password']) < 6){
            sendJsonResponse('error', 'Пароль должен содержать минимум 6 символов');
            exit;
        }
        if (!empty($input['registrationdate']) && !validDate($input['registrationdate'])){
            sendJsonResponse('error', 'Невалидная дата');
            exit;
        }
        if (!empty($input['age']) && ($age < 1 || $age > 150)){
            sendJsonResponse('error', 'Возраст должен лежать в диапазоне от 1 до 150 лет');
            exit;
        }
        
        if (findUserById($id)){
            $result = updateUserById($id, $input);
            sendJsonResponse('success', 'Пользователь обновлён', $result['user']);
        } else {
            sendJsonResponse('error', 'Пользователя с таким id не существует');
        }
    }

    function deleteUser($id){
        if ($id === 0) {
            sendJsonResponse('error', 'ID не указан');
        }
        
        if (findUserById($id)){
            $result = deleteUserById($id);
            sendJsonResponse('success', 'Пользователь удалён', $result['user']);
        } else {
            sendJsonResponse('error', 'Пользователя не существует');
        }
    }

?>