<?php

    require_once 'UserController.php';
    function handleRequest($method, $uri){
        
        $uri = str_replace('/Course_project/api/index.php', '', $uri);
        $uri = trim($uri, '/');
        $parts = explode("/", $uri);
        
        $action = "";
        $id = "";
        if (count($parts) > 1){
            $action = $parts[0];
            $id = $parts[1];
        } else if (count($parts) > 0){
            $action = $parts[0];
            $id = 0;
        } else {
            sendJsonResponse('error', 'Действия не существует', $uri);
            exit;
        }
        
        switch ($method) {
            case 'GET':
                if ($action === 'users') {
                    if ($id === 0) {
                        getAllUsers();
                    } else {
                        getOneUser($id);
                    }
                } else {
                    sendJsonResponse('error', 'Действия не существует', $uri);
                }
                break;
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if ($action === 'register') {
                    createUser($input);
                } else if ($action === 'login') {
                    loginUser($input);
                } else {
                    sendJsonResponse('error', 'Действия не существует', $uri);
                }
                break;
            case 'PUT':
            case 'PATCH':
                if ($action === 'users'){
                    updateUser($id);
                } else {
                    sendJsonResponse('error', 'Действия не существует', $uri);
                }
                break;
            case 'DELETE':
                if ($action === 'users'){
                    deleteUser($id);
                } else {
                    sendJsonResponse('error', 'Действия не существует', $uri);
                }
                break;
            default:
                sendJsonResponse('error', 'Действия не существует', $uri);
        }
    }

?>