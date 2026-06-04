<?php
//проверить функции чтобы работали под массивы
    require_once 'User.php';

    function sendJsonResponse($status, $message, $data = null){
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null){
            if (is_array($data)) {
                unset($data['password_hash']);
            }
            $response['data'] = $data;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
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
        $user = findUser($id);
        if ($user === null) {
            sendJsonResponse('error', 'Пользователь не найден');
        }
        sendJsonResponse('success', 'Пользователь найден', $user);
    }

    function createUser($input){
        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            sendJsonResponse('error', 'Поля не были заполнены');
        }
        $result = createNewUser($input['username'], $input['email'], $input['password']);
        
        if (isset($result['error'])) {
            sendJsonResponse('error', 'Указанный email занят');
        } else {
            sendJsonResponse('success', 'Регистрация произошла успешно', $result['user']);
        }
    }

    function loginUser($input){
        if (empty($input['login']) || empty($input['email']) || empty($input['password'])) {
            sendJsonResponse('error', 'Поля не были заполнены');
        }
        
        $result = checkUser($input['login'], $input['email'], $input['password']);
        
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