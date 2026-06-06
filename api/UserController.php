<?php
    require_once 'User.php';

    function validEmail($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    function writeLog($message, $email = "", $id = 0){
        $log = dirname(__DIR__) . '\data\auth.log';
        $time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $line = $time . " | ";
        if ($email != ""){
            $line = $line . "email: " . $email . " | ";
        }
        if ($id != 0){
            $line = $line . "id: " . $id . " | ";
        }
        $line = $line . $message . "\n";
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
        $email = "";
        $id = 0;
        if (isset($data['email'])){
            $email = $data['email'];
        }
        if (isset($data['id'])){
            $id = $data['id'];
        }
        $message = "API request: " . $message;
        writeLog($message, $email, $id);
        
        exit;
    }

    function getAllUsers(){
        $users = getUsers();
        if (count($users) === 0) {
            sendJsonResponse('error', 'Нет пользователей');
            exit;
        }
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        sendJsonResponse('success', 'Список пользователей', $users);
    }

    function getOneUser($id){
        $user = findUserId($id);
        if ($user === null) {
            sendJsonResponse('error', 'Пользователь не найден');
        }
        sendJsonResponse('success', 'Пользователь найден', $user);
    }

    function createUser($input){
        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            sendJsonResponse('error', 'Поля не были заполнены');
        }
        
        
        if ((strlen($input['username']) < 2 || strlen($input['username']) > 100)) {
            sendJsonResponse('error', 'Псевдоним должен быть от 2 до 100 символов');
            return 0;
        }
        if (!validEmail($input['email'])) {
            sendJsonResponse('error', 'Невалидный email');
            return 0;
        }
        if (strlen($input['password']) < 6) {
            sendJsonResponse('error', 'Пароль должен быть от 6 символов');
            return 0;
        }
        
        $age = null;
        if (isset($input['age'])) {
            $age = $input['age'];
            if ($age < 1 || $age > 200){
                sendJsonResponse('error', 'Возраст должен быть от 1 до 200');
                return 0;
            }
        }
        
        if (isset($input['admin'])) {
            $admin = $input['admin'];
        } else {
            $admin = "n";
        }
        
        $result = createNewUser($input['username'], $input['email'], $input['password'], $age, $admin);
        if (isset($result['error'])) {
            sendJsonResponse('error', 'Указанный email (' . $input['email'] . ') занят');
        } else {
            sendJsonResponse('success', 'Регистрация произошла успешно', $result['user']);
        }
    }

    function loginUser($input){
        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            sendJsonResponse('error', 'Поля не были заполнены');
        }
        
        $result = checkUser($input['username'], $input['email'], $input['password']);
        
        if (isset($result['error'])) {
            sendJsonResponse('error', 'Данные указаны неправильно');
        } else {
            sendJsonResponse('success', 'Вход произошёл успешно', $result['user']);
        }
    }

    function updateUser($id){
        if ($id === 0) {
            sendJsonResponse('error', 'ID не указан');
        } 
        $input = json_decode(file_get_contents('php://input'), true);
        $result = updateUserId($id, $input);
        if (isset($result['error'])) {
            sendJsonResponse('error', 'Пользователя не существует');
        } else {
            sendJsonResponse('success', 'Пользователь обновлён', $result['user']);
        }
    }

    function deleteUser($id){
        if ($id === 0) {
            sendJsonResponse('error', 'ID не указан');
        }
        $result = deleteUserId($id);
        
        if (isset($result['error'])) {
            sendJsonResponse('error', 'Пользователя не существует');
        } else {
            sendJsonResponse('success', 'Пользователь удалён', $result['user']);
        }
    }

?>