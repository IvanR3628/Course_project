<?php
//переделать все функции работающие на словари под массив
    function getUsers() {
        $file = dirname(__DIR__) . '\data\users.json';
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['users'])) {
            return [];
        }
        return $data['users'];
    }

    function findUser($id) {
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    function findUserEmail($email){
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    function saveAllUsers($users) {
        $file = dirname(__DIR__) . '\data\users.json';
        file_put_contents($file, json_encode(['users' => $users]));
    }

    function createNewUser($username, $email, $password) {
        $users = getUsers();
        if (findUserEmail($email)) {
            return ['error' => true];
        }
        if (count($users) === 0){
            $newId = 1;
        } else {
            $newId = $users[count($users) - 1]['id'] + 1;
        }
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ];
        
        $users[] = $newUser;
        saveAllUsers($users);
        unset($newUser['password_hash']);
        return ['user' => $newUser];
    }

    function checkUser($name, $email, $password){
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['username'] === $username && $user['email'] === $email && password_verify($password, $user['password_hash'])) {
                return ['user' => $user];
                break;
            }
        }
        return ['error' => true];
    }

    function updateUserId($id, $data){
        $users = getUsers();
        $index = -1;

        foreach ($users as $key => $user) {
            if ($user['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        if ($index === -1) {
            return ['error' => true];
        }
        
        if (isset($data['name'])) {
            $users[$index]['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $users[$index]['email'] = $data['email'];
        }
        if (isset($data['password'])) {
            $users[$index]['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        saveAllUsers($users);
        return ['user' => $users[$index]];
        
    }

    function deleteUserID($id){
        $users = getUsers();
        $index = -1;

        foreach ($users as $key => $user) {
            if ($user['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        if ($index === -1) {
            return ['error' => true];
        }
        
        $deletedUser = $users[$index];
        array_splice($users, $index, 1);
        
        saveAllUsers($users);
        return ['user' => $deletedUser];
    }

?>