<?php

    require_once 'Controller.php';
    function handleRequest($method, $request){
        
        $request = str_replace('/Course_project/api/index.php', '', $request);
        $request = trim($request, '/');
        $parts = explode("/", $request);
        
        if (count($parts) == 2){
            $action = $parts[0];
            $id = $parts[1];
        } else if (count($parts) == 1){
            $action = $parts[0];
            $id = 0;
        } else {
            sendJsonResponse('error', 'Действия не существует', $request);
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
                    sendJsonResponse('error', 'Действия не существует', $request);
                }
                break;
            case 'POST':
                if ($action === 'users') {
                    createUser();
                } else {
                    sendJsonResponse('error', 'Действия не существует', $request);
                }
                break;
            case 'PUT':
            case 'PATCH':
                if ($action === 'users'){
                    updateUser($id);
                } else {
                    sendJsonResponse('error', 'Действия не существует', $request);
                }
                break;
            case 'DELETE':
                if ($action === 'users'){
                    deleteUser($id);
                } else {
                    sendJsonResponse('error', 'Действия не существует', $request);
                }
                break;
            default:
                sendJsonResponse('error', 'Действия не существует', $request);
        }
    }

?>