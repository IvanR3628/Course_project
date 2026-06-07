<?php

    function createUsersFile() {
        $file = dirname(__DIR__) . '\data\users.json';
        $data = ['users' => []];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    function getUsers() {
        $file = dirname(__DIR__) . '\data\users.json';
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['users'])) {
            createUsersFile();
            return [];
        }
        return $data['users'];
    }

    function saveAllUsers($users) {
        $file = dirname(__DIR__) . '\data\users.json';
        file_put_contents($file, json_encode(['users' => $users], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    function validUsername($username){
        return preg_match('/^[a-zA-Zа-яА-Я0-9_-]{2,100}$/', $username);
    }

    function validEmail($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    function findUserById($id) {
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    function findUserByEmail($email){
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    function createNewUser($username, $email, $password, $age = "", $admin = "n") {
        $users = getUsers();
        if (count($users) === 0){
            $newId = 1;
        } else {
            $newId = $users[count($users) - 1]['id'] + 1;
        }
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'email' => $email,
            'age' => $age == "" ? null : $age,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'registrationdate' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            "admin" => $admin
        ];
        
        $users[] = $newUser;
        saveAllUsers($users);
        unset($newUser['password_hash']);
        return ['user' => $newUser];
    }

    function updateUserById($id, $data){
        $users = getUsers();
        $index = -1;

        foreach ($users as $key => $user) {
            if ($user['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        if (isset($data['username'])) {
            $users[$index]['username'] = trim($data['username']);
        }
        if (isset($data['email'])) {
            $users[$index]['email'] = trim($data['email']);
        }
        if (array_key_exists('age', $data)) {
            $users[$index]['age'] = $data['age'];
        }
        if (isset($data['password'])) {
            $users[$index]['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['registrationdate'])){
            $users[$index]['registrationdate'] = $data['registrationdate'];
        }
        if (isset($data['admin'])){
            $users[$index]['admin'] = $data['admin'];
        }
        
        saveAllUsers($users);
        return ['user' => $users[$index]];
    }

    function deleteUserById($id){
        $users = getUsers();
        $index = -1;

        foreach ($users as $key => $user) {
            if ($user['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        $deletedUser = $users[$index];
        array_splice($users, $index, 1);
        
        saveAllUsers($users);
        return ['user' => $deletedUser];
    }

?>