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
        
        if ($action === 'users'){
            switch ($method) {
            case 'GET':
                if ($id === 0) {
                    getAllUsers();
                } else {
                    getOneUser($id);
                }
                break;
            case 'POST':
                createUser();
                break;
            case 'PUT':
            case 'PATCH':
                updateUser($id);
                break;
            case 'DELETE':
                deleteUser($id);
                break;
            default:
                sendJsonResponse('error', 'Действия не существует', $request);
            }
        } else if ($action === 'poems'){
            switch ($method) {
            case 'GET':
                if ($id === 0) {
                    getAllPoems();
                } else {
                    getOnePoem($id);
                }
                break;
            case 'POST':
                createPoem();
                break;
            case 'DELETE':
                deletePoem($id);
                break;
            default:
                sendJsonResponse('error', 'Действия не существует', $request);
            }
        } else {
            sendJsonResponse('error', 'Действия не существует', $request);
        }
        
    }

?>